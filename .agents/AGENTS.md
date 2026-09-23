# Ponytail: Lazy Senior Developer Rule
Sebelum menulis kode baru, Anda wajib mengikuti tangga keputusan (decision ladder) berikut dan berhenti di anak tangga pertama yang menyelesaikan masalah:
1. **YAGNI**: Apakah fitur/tugas ini benar-benar harus ada? Jika tidak, lewati (jangan dibuat).
2. **Reuse**: Apakah sudah ada helper, utility, atau fungsi di codebase ini yang serupa? Jika ya, gunakan kembali.
3. **Standard Library**: Apakah library bawaan PHP/JS bisa menyelesaikannya? Jika ya, gunakan.
4. **Native Platform**: Apakah fitur bawaan browser/database/HTML bisa menangani ini? (misal input type="date", foreign key constraint).
5. **Existing Dependency**: Apakah dependency yang sudah terinstal bisa menyelesaikannya?
6. **One-Liner**: Apakah bisa diselesaikan dalam 1 baris kode saja?
7. **Minimal Code**: Jika semua di atas tidak bisa, tulis kode seminimal mungkin yang bekerja dengan baik.

*Catatan: Aspek keamanan (security), perlindungan data (data-loss protection), validasi input, penanganan error, dan aksesibilitas TIDAK BOLEH dikurangi atau diabaikan.*

---

# Daijo MES: App Context & Architecture

## 1. Overview & Tech Stack
- **Application**: PT Daijo Industrial MES (Manufacturing Execution System)
- **Backend**: Laravel 12 (PHP 8.4), MySQL, Redis
- **Frontend**: Blade templates, Tailwind CSS, Alpine.js, Livewire
- **Dev & Test Environment**: Docker via Laravel Sail (`./vendor/bin/sail test`)

## 2. Core Functional Modules & Domains
1. **Second Process (`SecondProcessReport`)**:
   - Manages daily secondary processing (Painting, Buffing, Amplas, Treatment, Packing, Rework, Repair, Assy).
   - Form Tabs:
     - **Tab 1 (Setup & Manpower)**: Shift logistics, part number, customer, manpowers.
     - **Tab 2 (Materials)**: Item Paint (viscosity, mixing ratio, qty — active for `Painting` & `Repair`) and Item Parts / WIP Lots (WIP + Repairan reconciliation).
     - **Tab 3 (Production Logs & NG)**: Target per hour, hourly production slots, NG breakdown by defects & remarks.
     - **Tab 4 (Handover & Signs)**: Next schedule, downtime/troubles (`loss_time_minutes`), operator, PQC, and Leader approval signoffs.
   - **Customer Enforcement & Auto-Conversion**:
     - The `customer` field is strictly enforced: must be an official customer name (`MasterCustomerDelivery::customer_name`) or `'N/A'`.
     - Empty, `'0'`, `'-'`, or case-insensitive `'n/a'` are automatically normalized to `'N/A'`.
     - Valid `customer_code` inputs (e.g. `CUST-001`) are automatically resolved and converted to `customer_name` on save.
     - Part number search (`searchItems`) returns `$item->customer?->customer_name ?: 'N/A'` (never leaks raw codes or `'0'`).
   - **Material Input vs Production Output Reconciliation**:
     - `Total Input = jml_input_wip + repairan` (accumulated in Tab 2).
     - `Total Output = jumlah_ok + jumlah_ng + jml_ng_lebur` (accumulated from OK, NG, and Scrap in Tab 3).
     - `sisa_input = Total Input - Total Output` (persisted in `sisa_input` column).
     - **Deficit (`Total Input < Total Output`)**: Submission blocked in client-side JS and backend controller (`ValidationException` on `sisa_input`). Draft saving allowed.
     - **Leftover (`Total Input > Total Output`)**: Allowed to submit, but creator must provide `sisa_input_remark`.
     - **Ideal (`Total Input == Total Output`)**: Submittable immediately without requiring remarks.
   - **Tab 3 UI Layout & Zone Separation (Responsive & Direct)**:
     - **Zone 1 (Read-Only Information Monitoring `#reconcile-summary-card`)**: Responsive 3-column / 1-column grid (`sm:grid-cols-3`), larger shop-floor typography (`text-2xl sm:text-3xl` figures), and direct labels without verbose paragraphs:
       - Card 1: Input Material (`#reconcile-total-input` + WIP & Repairan breakdown + Tab 2 shortcut).
       - Card 2: Total Output (`#reconcile-total-output` + NG % badge + OK, NG, Scrap statistic chips).
       - Card 3: Sisa Material (`#reconcile-sisa-qty` + dynamic status badge `⛔ Defisit` / `⚠️ Ada Sisa` / `✓ Ideal` + direct inline note).
       - Deficit Banner (`#reconcile-state-deficit`): Responsive flex alert showing exact deficit pcs and Tab 2 link.
     - **Zone 2 (User Input Parameters Strip)**: Integrated touch-friendly 2-column input strip (`grid-cols-1 sm:grid-cols-2`):
       - Form input for `target_per_hour` (Target Produksi / Jam with `pcs/jam` suffix, 42px touch height, `text-sm sm:text-base`).
       - Form input for `jml_ng_lebur` (Scrap/Lebur with `pcs` suffix and `+ ke Total Output` badge).
       - Conditional `sisa_input_remark` textarea when `sisa_input > 0`.
     - **Zone 3 (Hourly Production & Defect Grid `#unified-production-table`)**:
       - Distinct column indicators: `OK Qty (INPUT)` vs `Accum OK (AUTO)` and `Total NG (AUTO)` with clear typography.
     - Full backward compatibility: all form input names (`target_per_hour`, `jml_input_wip`, `repairan`, `jml_ng_lebur`, `jumlah_output`, `jumlah_ok`, `jumlah_ng`, `ng_prosentase`, `sisa_input`, `sisa_input_remark`) strictly preserved.
   - Analytics & Reports: `SecondProcessReportAnalyticsController`.

2. **Master List Items & Customer Relations (`MasterListItem`, `MasterListItemView`)**:
   - Relates to `MasterCustomerDelivery` via `belongsTo(MasterCustomerDelivery::class, 'customer_code', 'customer_code')`.
   - `/master-list-item` Livewire management view includes connection filters (`Total`, `✓ Terhubung`, `⚠️ Belum Terhubung`), unassigned badges, and inline editing to resolve disconnected part-customer relationships.

3. **First Piece Inspection (`FirstPieceInspection`)**:
   - Quality gate conducted at the start of a production run.
   - Acts as a required approval gate before Second Process PQC signoff.

4. **IPQC Inspection (`IpqcInspection`)**:
   - In-Process Quality Control recording defect rates, sample inspection rounds, and judgements.

5. **Work Orders & Assembly (`SpWorkOrder`, `AssemblyDailyProcess`)**:
   - Work order dispatching, batch tracking, barcode generation, and packaging master/detail records.

6. **SAP ERP Gateway (`BaseSapService`)**:
   - Central integration gateway with SAP Business One API (`http://192.168.6.149:9001`).
   - Implements lazy token loading, cache stampede atomic lock (`Cache::lock('sap_token_lock', 40)`), 50-minute TTL, and auto-refresh on 401 Unauthorized with 120s timeout and retries.
   - Audits all traffic via `saveApiLog()` into `api_logs` table.
   - **Key Subservices**:
     - *Outbound Pushes*: `ReceiptProductionService` (`/api/receipt_production/create` every 10m via `sap:dispatch-receipt`), `QcTransferService` (`/api/inventory_transfer/create` for QC Pass/Fail splits), `WmsSapSyncService` (`/api/inventory_transfer/create` for Pallet moves).
     - *Inbound Master*: `SpkMasterService` (`/api/sap_production_order/list` synced to `spk_masters` & `spk_change_logs` 6x daily).
   - *Configuration*: All SAP connection parameters (`base_url`, `auth_url`, `company_db`, `username`, `password`) are centralized under `config('services.sap.*')` with default URL fallbacks (`http://192.168.6.149:9001`) in [config/services.php](file:///home/andreas/projects/Daijo_MES/config/services.php) and [BaseSapService.php](file:///home/andreas/projects/Daijo_MES/app/Services/BaseSapService.php) to guarantee safe execution even if `.env` does not specify `SAP_BASE_URL` or `SAP_AUTH_URL`.

5. **Production Dashboard (`ProductionDashboard`, `ProductionDashboardService`)**:
   - High-performance production tracking, NG rates, adjust/mould setup times, and adjuster NG trend.
   - **Shift Schedules & Half-Day Logic**:
     - *Normal Schedule*: Shift 1 (07:30 - 15:30), Shift 2 (15:30 - 23:30), Shift 3 (23:30 - 07:30 next day).
     - *Half-Day Schedule*: Shift 1 (07:30 - 12:30), Shift 2 (12:30 - 17:30), Shift 3 (17:30 - 22:30, rollover to 07:30 next day).
     - *Daily View*: Manual toggle "Setengah Hari" (defaults to unchecked/OFF) so Saturday/Sunday can run full-day or half-day as needed.
     - *Weekly View*: Dedicated manual checkboxes "Sabtu" and "Minggu" under "Setengah Hari" (default unchecked/OFF). Weekdays (Mon–Fri) always use normal schedule, while Saturday and/or Sunday use half-day only when checked by user.
     - *NG & Output Balance*: Multi-adjuster shift distribution uses integer division (`intdiv`) plus remainder allocation (`$baseNg + ($idx < $rem ? 1 : 0)`) so the sum of individual adjuster NGs strictly matches the shift total.
     - *Machine Efficiency & Prod Time*: Calculated based on total accumulated production seconds from cycle times capped against available operating time (`maxDetik`), avoiding premature individual hourly clamp loss.

6. **SAP Receipt Monitor (`ReceiptProductionLogs`, `production_summary`)**:
   - Monitors SPK receipts pushed to SAP from production scanning.
   - Bulk push & ignore actions feature real-time selection calculation (`selectedSummary`), displaying selected SPK count and total quantity (`pcs`) across top action bars, floating bottom action pills, and confirmation dialogs.

7. **Store Out & Delivery Scanning (`SOController`, `soresults`)**:
   - High-throughput scanning for sales orders / delivery orders (`/so/process/{docNum}`).
   - Fast sub-second scan processing via composite database indexes on `scanned_data` (`[doc_num, item_code]`, `[doc_num, spk_code, label]`), `so_datas` (`[doc_num, item_code]`), and `wms_pallet_form_details` (`[part_no, label]`).
   - Frontend scanning features immediate Enter key handling, composite tab-delimited QR barcode parsing, and rapid debouncing (150ms).

## 3. Essential Development Guidelines
- Always verify changes with automated tests via Sail: `./vendor/bin/sail test --filter=<TestClass>`.
- Maintain field validation integrity, error feedback banners, and tab switching handlers across legacy Blade views.
- Keep calculations consistent between client-side Alpine/JS real-time updates and server-side controller persistence.

## 4. Continuous Knowledge Maintenance (Mandatory for Agent)
- **Automatic Updates**: Upon finishing an implementation plan, completing a major change, or before the conversation session wraps up / gets compacted, the agent **MUST automatically update `.agents/AGENTS.md`** with any new context, newly added features, schema changes, or discovered business rules.
- **Autonomous & Zero-Prompt**: Do NOT wait for the user to ask or remind you to update this file. Proactively review and update it as part of finishing tasks.
- **Maintain High-Signal Content**: Keep entries concise, structured, and focused on business rules, architecture, and gotchas so the file remains punchy and within optimal context size.
