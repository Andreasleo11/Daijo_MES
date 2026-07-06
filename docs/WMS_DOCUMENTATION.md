# DOKUMENTASI TEKNIS LENGKAP: SISTEM WMS PALLETIZATION
## Daijo MES - Warehouse Management System
**Versi:** 1.0  
**Tanggal:** 30 April 2026  
**Platform:** Laravel 11 + Livewire 3

---

## DAFTAR ISI
1. Ringkasan Sistem
2. Arsitektur Database (6 Tabel)
3. Penjelasan Kode Per File (Backend)
4. Penjelasan Kode Per File (Frontend/Views)
5. Routing (URL Endpoints)
6. Fitur-Fitur Khusus
7. Alur Kerja Operasional

---

## 1. RINGKASAN SISTEM

Sistem WMS Palletization adalah modul gudang yang berfungsi untuk:
- Mencatat barang masuk ke dalam palet menggunakan barcode scanner
- Menentukan posisi rak secara otomatis berdasarkan customer dan prioritas barang
- Mencetak formulir palet untuk ditempelkan di fisik palet
- Mencatat keluar-masuk palet dari gudang (Audit Trail)
- Memvisualisasikan peta rak gudang secara real-time

---

## 2. ARSITEKTUR DATABASE (6 TABEL)

### Tabel 1: `wms_warehouses`
**Tujuan:** Master data gudang.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BigInt (PK, Auto Increment) | Primary Key |
| `whse_code` | String (Unique) | Kode gudang, contoh: "J06" |
| `whse_name` | String | Nama gudang, contoh: "Gudang Utama J06" |
| `created_at` | Timestamp | Waktu pembuatan record |
| `updated_at` | Timestamp | Waktu terakhir diubah |
| `deleted_at` | Timestamp (Nullable) | Soft Delete marker |

**Relasi:** Satu warehouse memiliki banyak rak (`wms_racks`).

---

### Tabel 2: `wms_racks`
**Tujuan:** Master data rak fisik di gudang.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BigInt (PK, Auto Increment) | Primary Key |
| `whse_id` | BigInt (FK → `wms_warehouses.id`) | Gudang tempat rak berada |
| `rack_code` | String | Kode rak, contoh: "A1", "B2" |
| `x_pos` | Integer (Default: 0) | Koordinat X di peta gudang |
| `y_pos` | Integer (Default: 0) | Koordinat Y di peta gudang |
| `width` | Integer (Default: 200) | Lebar rak di peta (pixel) |
| `height` | Integer (Default: 100) | Tinggi rak di peta (pixel) |
| `orientation` | String (Default: "HORIZONTAL") | Orientasi rak: HORIZONTAL / VERTICAL |
| `created_at` | Timestamp | Waktu pembuatan |
| `updated_at` | Timestamp | Waktu terakhir diubah |
| `deleted_at` | Timestamp (Nullable) | Soft Delete marker |

**Relasi:** Satu rak memiliki banyak posisi/slot (`wms_positions`).

---

### Tabel 3: `wms_positions`
**Tujuan:** Master data slot/posisi di dalam rak. Setiap slot bisa menampung palet.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BigInt (PK, Auto Increment) | Primary Key |
| `rack_id` | BigInt (FK → `wms_racks.id`) | Rak induk |
| `level_no` | Integer | Nomor level/lantai rak (1 = bawah) |
| `slot_no` | Integer | Nomor slot dalam level |
| `customer_code` | String (Nullable) | Kode customer yang dialokasikan untuk slot ini |
| `position_code` | String (Unique) | Kode posisi unik, contoh: "A1-L01-S03" |
| `status` | String (Default: "EMPTY") | Status slot: EMPTY / PARTIAL / FULL |
| `last_item_code` | String (Nullable) | Kode barang terakhir yang ditaruh di slot ini |
| `max_capacity` | Integer (Default: 1) | Kapasitas maksimum palet di slot ini |
| `created_at` | Timestamp | Waktu pembuatan |
| `updated_at` | Timestamp | Waktu terakhir diubah |
| `deleted_at` | Timestamp (Nullable) | Soft Delete marker |

**Relasi:**
- Belongs To: `wms_racks` (rak induk)
- Has Many: `wms_pallet_forms` (palet yang ada di slot ini)

---

### Tabel 4: `wms_pallet_forms` (HEADER)
**Tujuan:** Menyimpan data identitas satu unit palet.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `pallet_id` | String (PK) | ID unik palet, contoh: "PLT-20260430-0001" |
| `position_id` | BigInt (FK → `wms_positions.id`, Nullable) | Lokasi rak saat ini (null jika sudah keluar) |
| `part_no` | String (Nullable) | Kode part utama. "MIXED" jika berisi banyak jenis |
| `model_name` | String (Nullable) | Nama model. "MULTI-ITEM" jika mixed |
| `prod_date` | Date (Nullable) | Tanggal produksi |
| `lot_no` | String (Nullable) | Nomor LOT/MO |
| `delivery_name` | String (Nullable) | Nama PIC/Operator |
| `delivery_shift` | String (Nullable) | Shift kerja (1, 2, 3) |
| `box_qty` | Integer (Default: 0) | Total jumlah box di dalam palet |
| `total_pallet_qty` | Decimal(15,2) (Default: 0) | Total pcs seluruh box |
| `status` | String (Default: "STORED") | Status palet: STORED / OUT |
| `remarks` | Text (Nullable) | Catatan tambahan operator |
| `created_at` | Timestamp | Waktu pembuatan |
| `updated_at` | Timestamp | Waktu terakhir diubah |
| `deleted_at` | Timestamp (Nullable) | Soft Delete marker |

**Relasi:**
- Belongs To: `wms_positions` (lokasi rak)
- Has Many: `wms_pallet_form_details` (daftar box di dalam palet)

---

### Tabel 5: `wms_pallet_form_details` (LINES/DETAIL)
**Tujuan:** Menyimpan rincian tiap box di dalam palet.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BigInt (PK, Auto Increment) | Primary Key |
| `pallet_form_id` | String (FK → `wms_pallet_forms.pallet_id`) | Palet induk (ON DELETE CASCADE) |
| `part_no` | String(100) (Nullable) | Kode part barang per box |
| `model_name` | String(255) (Nullable) | Nama model per box |
| `spk_no` | String (Nullable) | Nomor SPK referensi |
| `qty` | Decimal(15,2) (Default: 0) | Jumlah pcs per box |
| `warehouse` | String (Nullable) | Kode gudang asal, contoh: "YF" |
| `label` | String (Nullable) | ID barcode label box (unik per SPK) |
| `is_no_label` | Boolean (Default: false) | True jika box tidak punya label |
| `no_label_reason` | String(100) (Nullable) | Alasan tidak ada label |
| `created_at` | Timestamp | Waktu pembuatan |
| `updated_at` | Timestamp | Waktu terakhir diubah |
| `deleted_at` | Timestamp (Nullable) | Soft Delete marker |

**Relasi:** Belongs To `wms_pallet_forms` via `pallet_form_id`.

---

### Tabel 6: `wms_pallet_logs` (AUDIT TRAIL)
**Tujuan:** Mencatat setiap transaksi keluar-masuk palet.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BigInt (PK, Auto Increment) | Primary Key |
| `pallet_id` | String | ID palet yang bertransaksi |
| `transaction_type` | String | Jenis transaksi: "IN" atau "OUT" |
| `position_id` | BigInt (FK → `wms_positions.id`, Nullable) | Posisi rak saat transaksi |
| `user_id` | BigInt (FK → `users.id`, Nullable) | User yang melakukan transaksi |
| `notes` | Text (Nullable) | Catatan transaksi |
| `created_at` | Timestamp | Waktu transaksi |
| `updated_at` | Timestamp | Waktu terakhir diubah |

---

### Tabel Pendukung (Bukan WMS, tapi digunakan untuk validasi):
- **`spk_item_histories`**: Data SPK dari produksi. Digunakan untuk validasi apakah nomor SPK yang di-scan valid.
- **`master_list_items`**: Master data barang. Digunakan untuk mengambil `item_name` dan `customer_code` secara otomatis.

---

## 3. PENJELASAN KODE PER FILE (BACKEND)

### File 1: `app/Models/WmsPalletForm.php`
**Tujuan:** Eloquent Model untuk tabel `wms_pallet_forms`.

**Konfigurasi Khusus:**
- `$primaryKey = 'pallet_id'` → Primary key bukan `id` melainkan string `pallet_id`
- `$incrementing = false` → Karena PK bukan auto-increment
- `$keyType = 'string'` → Tipe PK adalah string
- Menggunakan `SoftDeletes` → Data tidak benar-benar dihapus dari database

**Relasi:**
- `details()`: HasMany ke `WmsPalletFormDetail` (FK: `pallet_form_id` → `pallet_id`)
- `position()`: BelongsTo ke `WmsPosition` (FK: `position_id`)

---

### File 2: `app/Models/WmsPalletFormDetail.php`
**Tujuan:** Eloquent Model untuk tabel `wms_pallet_form_details`.

**Konfigurasi Khusus:**
- Cast `is_no_label` ke `boolean`
- Cast `qty` ke `float`
- Menggunakan `SoftDeletes`

**Relasi:**
- `header()`: BelongsTo ke `WmsPalletForm` (FK: `pallet_form_id` → `pallet_id`)

---

### File 3: `app/Models/WmsPosition.php`
**Tujuan:** Eloquent Model untuk tabel `wms_positions`.

**Relasi:**
- `rack()`: BelongsTo ke `WmsRack`
- `palletForms()`: HasMany ke `WmsPalletForm`

---

### File 4: `app/Services/WmsService.php`
**Tujuan:** Service class yang mengandung seluruh business logic WMS.

**Method-method:**

| Method | Parameter | Return | Fungsi |
|--------|-----------|--------|--------|
| `generatePalletId()` | - | String | Generate ID unik format PLT-YYYYMMDD-XXXX. Mengambil nomor terakhir hari ini lalu increment +1. |
| `getPriorityItems()` | - | Array | Mengembalikan daftar item code yang WAJIB ditempatkan di Level 1 (lantai bawah) karena berat/besar. |
| `recommendPosition()` | `$customerCodes` (array), `$primaryPartNo` (string) | WmsPosition atau null | Algoritma rekomendasi posisi rak. Prioritas: (1) Slot PARTIAL dengan item sama, (2) Slot EMPTY untuk customer sama, (3) Slot EMPTY manapun. Item prioritas dipaksa ke Level 1. |
| `updatePositionStatus()` | `$positionId` (int) | void | Menghitung ulang status slot (EMPTY/PARTIAL/FULL) berdasarkan jumlah palet yang ada di dalamnya vs max_capacity. |
| `logTransaction()` | `$palletId`, `$type`, `$positionId`, `$notes` | void | Mencatat transaksi IN/OUT ke tabel `wms_pallet_logs`. |

---

### File 5: `app/Livewire/Wms/PalletFormCreator.php` (348 baris)
**Tujuan:** Komponen Livewire utama untuk pembuatan palet baru via scanning.

**Properties (State):**

| Property | Tipe | Default | Fungsi |
|----------|------|---------|--------|
| `$prod_date` | String | Today | Tanggal produksi |
| `$lot_no` | String | '' | Nomor LOT |
| `$delivery_name` | String | '' | Nama operator |
| `$delivery_shift` | String | '' | Shift kerja |
| `$remarks` | String | '' | Catatan |
| `$total_box` | Int | 0 | Jumlah box yang sudah di-scan |
| `$total_pallet_qty` | Float | 0 | Total pcs |
| `$showSuccessModal` | Bool | false | Tampilkan modal sukses |
| `$lastGeneratedPalletId` | String | null | ID palet terakhir yang dibuat |
| `$scan_spk` | String | '' | Input SPK dari scanner |
| `$scan_qty` | String | '' | Input quantity |
| `$scan_whse` | String | '' | Input warehouse |
| `$scan_label` | String | '' | Input label barcode |
| `$scan_part_no` | String | '' | Auto-filled dari SPK lookup |
| `$scan_model_name` | String | '' | Auto-filled dari SPK lookup |
| `$scan_customer_code` | String | '' | Auto-filled dari SPK lookup |
| `$label_mode` | String | 'SCAN' | Mode: 'SCAN' atau 'NO_LABEL' |
| `$scanned_items` | Array | [] | Daftar box yang sudah di-scan |
| `$recommendedSlot` | String | null | Rekomendasi posisi rak |

**Method-method:**

| Method | Fungsi Detail |
|--------|---------------|
| `mount()` | Inisialisasi `prod_date` ke hari ini. |
| `toggleNoLabel()` | Toggle antara mode scan biasa dan mode tanpa label. Saat aktif, field SPK dan Label dikosongkan. |
| `resetScanner()` | Mengosongkan semua field scan (SPK, Qty, Whse, Label, Part, Model) dan mengembalikan fokus ke SPK. |
| `resetWholeForm()` | Reset TOTAL: semua field header + scanner + daftar scan. Dipakai setelah user klik "Lanjut Scan Baru". |
| `addItem()` | **FUNGSI UTAMA SCANNING.** Alur: (1) Jika mode NO_LABEL → langsung tambahkan box kosong ke daftar. (2) Jika mode SCAN → Validasi SPK wajib diisi → Cek SPK ada di database `spk_item_histories` → Validasi Label wajib diisi → Cek duplikat label di session → Cek duplikat label di database global → Tambahkan ke `$scanned_items`. Setiap langkah yang gagal akan dispatch event `scan-error` (bunyi buzz). Jika berhasil dispatch `scan-success` (bunyi beep). |
| `removeItem($index)` | Hapus satu item dari daftar scan berdasarkan index. |
| `calculateTotals()` | Menghitung ulang `$total_box` dan `$total_pallet_qty` dari `$scanned_items`. Juga memanggil `updateRecommendation()`. |
| `updateRecommendation()` | Memanggil `WmsService::recommendPosition()` untuk mendapatkan rekomendasi rak secara real-time berdasarkan item yang sudah di-scan. |
| `generateForm($wmsService)` | **FUNGSI SIMPAN.** Alur: (1) Validate header fields → (2) Hitung summary (apakah MIXED atau single item) → (3) Dapatkan rekomendasi rak → (4) Generate Pallet ID → (5) Buat record `WmsPalletForm` → (6) Loop buat semua `WmsPalletFormDetail` → (7) Update status posisi rak → (8) Log transaksi IN. Semua dibungkus `DB::transaction()`. |

---

### File 6: `app/Livewire/Wms/PalletFormLookup.php` (52 baris)
**Tujuan:** Halaman pencarian detail palet via scan barcode palet.

**Method-method:**

| Method | Fungsi |
|--------|--------|
| `updatedPalletId($value)` | Dipanggil otomatis saat input berubah. Mencari palet di database dengan eager load `details` dan `position`. Jika tidak ditemukan, flash error. |
| `clear()` | Mengosongkan pencarian dan mengembalikan fokus ke input. |

---

### File 7: `app/Livewire/Wms/PalletFormIndex.php` (38 baris)
**Tujuan:** Halaman daftar riwayat palet (History).

**Method-method:**

| Method | Fungsi |
|--------|--------|
| `updatingSearch()` | Reset pagination saat search berubah. |
| `render()` | Query `WmsPalletForm` dengan relasi `position`, filter berdasarkan `$search` (pallet_id, part_no, model_name), urutkan terbaru, paginate 15. |

---

### File 8: `app/Livewire/Wms/PalletOutbound.php` (69 baris)
**Tujuan:** Halaman proses palet keluar gudang.

**Method-method:**

| Method | Fungsi |
|--------|--------|
| `processOutbound($wmsService)` | Scan barcode palet → Cek ada di DB → Cek belum OUT → Update status ke "OUT" & hapus `position_id` → Log transaksi OUT → Update status posisi rak. Dibungkus `DB::transaction()`. |

---

### File 9: `app/Livewire/Wms/RackMapping.php` (175 baris)
**Tujuan:** Halaman visualisasi dan manajemen rak gudang.

**Method-method:**

| Method | Fungsi |
|--------|--------|
| `selectPosition($id)` | Memilih slot untuk diedit. Mengambil data max_capacity dan customer_code. |
| `saveSettings($wmsService)` | Menyimpan perubahan max_capacity dan customer_code slot. Recalculate status. |
| `resetSlot($wmsService)` | Reset slot ke status EMPTY dan hapus `last_item_code`. |
| `createNewRack($wmsService)` | Membuat rak baru beserta semua posisi/slot-nya secara batch. Format position_code: "{RACK_CODE}-L{XX}-S{XX}". Dibungkus `DB::transaction()`. |
| `deleteRack($rackId)` | Menghapus rak beserta seluruh posisinya. Palet yang ada di dalamnya di-detach (position_id → null). Dibungkus `DB::transaction()`. |

---

### File 10: `app/Livewire/Wms/PalletLogIndex.php` (58 baris)
**Tujuan:** Halaman audit trail (log keluar-masuk palet).

**Method-method:**

| Method | Fungsi |
|--------|--------|
| `mount()` | Hitung statistik IN/OUT hari ini. |
| `calculateStats()` | Query `WmsPalletLog` untuk menghitung total transaksi IN dan OUT hari ini. |
| `render()` | Query log dengan relasi `position` dan `user`, filter berdasarkan `$search`, paginate 15. |

---

## 4. PENJELASAN KODE PER FILE (FRONTEND/VIEWS)

### File 1: `resources/views/livewire/wms/pallet-form-creator.blade.php`
**Tujuan:** Form scanning pembuatan palet.

**Bagian-bagian:**
- **Header Form**: Input tanggal, shift, operator, LOT, remarks.
- **Scanner Panel**: Input SPK, Qty, Warehouse, Label dengan warna biru (mode normal) atau oranye (mode no-label).
- **Scanned Items Table**: Tabel daftar box yang sudah di-scan dengan tombol hapus per baris.
- **Summary Bar**: Menampilkan total box, total qty, dan rekomendasi rak.
- **Success Modal**: Popup setelah generate berhasil dengan opsi "Lihat & Print" atau "Lanjut Scan Baru".

**JavaScript:**
- **Keyboard Navigation**: Enter berpindah fokus SPK → Qty → Whse → Label → addItem.
- **Auto-Submit**: Setelah label di-scan, tunggu 400ms lalu otomatis `addItem()`.
- **Audio Feedback**: Web Audio API menghasilkan suara success (1200Hz sawtooth) dan error (100Hz square 2x).

### File 2: `resources/views/wms/pallet_form_print.blade.php`
**Tujuan:** Layout cetak formulir palet (A4, 4 form per halaman).

**Sistem Layout:**
- CSS Grid: 210mm x 297mm dibagi 2x2 = 4 slot (masing-masing 105mm x 148.5mm).
- Setiap form terdiri dari: Header, Summary Table, Data Table (2 kolom), Barcode.
- Pagination: Maksimal 15 baris per kolom (30 box per form). Lebih dari itu otomatis lanjut ke halaman berikutnya.
- Copy System: Setiap form dicetak 2x (Original + Copy) berdampingan.

### File 3: `resources/views/livewire/wms/pallet-form-lookup.blade.php`
**Tujuan:** Halaman pengecekan detail palet via scan.

### File 4: `resources/views/livewire/wms/pallet-form-index.blade.php`
**Tujuan:** Halaman daftar riwayat palet dengan search dan reprint.

### File 5: `resources/views/livewire/layout/sidebar.blade.php`
**Tujuan:** Navigasi sidebar dengan link ke semua modul WMS.

---

## 5. ROUTING (URL ENDPOINTS)

| URL | Komponen | Fungsi |
|-----|----------|--------|
| `/wms/pallet-form/create` | PalletFormCreator | Halaman scan & buat palet baru |
| `/wms/pallet-form/history` | PalletFormIndex | Riwayat palet |
| `/wms/pallet-form/lookup` | PalletFormLookup | Cek detail palet |
| `/wms/pallet-form/print/{id}` | Blade View | Cetak formulir palet |
| `/wms/outbound` | PalletOutbound | Proses palet keluar |
| `/wms/mapping` | RackMapping | Visualisasi & manajemen rak |
| `/wms/pallet-logs` | PalletLogIndex | Audit trail |

Semua route berada di dalam group `Route::middleware('auth')` dan prefix `wms`.

---

## 6. FITUR-FITUR KHUSUS

### A. Audio Feedback (Web Audio API)
- **Success**: Frekuensi 1200Hz + 1500Hz, gelombang sawtooth, volume 0.4. Terdengar tajam.
- **Error**: Frekuensi 100Hz, gelombang square, volume 0.6, dibunyikan 2x. Terdengar berat.
- Tujuan: Operator tahu status scan tanpa melihat layar di lingkungan pabrik yang bising.

### B. Anti-Duplicate Scan (2 Layer)
- **Layer 1 (Session)**: Cek apakah label+SPK sudah ada di daftar scan saat ini.
- **Layer 2 (Database)**: Cek apakah label+SPK sudah pernah tersimpan di palet manapun sebelumnya.

### C. Rack Recommendation Algorithm
Urutan prioritas penempatan:
1. Slot PARTIAL dengan item yang sama (konsolidasi).
2. Slot EMPTY untuk customer yang sama.
3. Slot EMPTY manapun (fallback).
- Item prioritas (barang berat) dipaksa ke Level 1 (lantai bawah).

### D. Soft Deletes
Semua tabel WMS menggunakan SoftDeletes. Data yang dihapus tidak hilang permanen, hanya ditandai dengan `deleted_at`.

### E. Timezone
Seluruh tampilan waktu menggunakan `Asia/Jakarta` (WIB).

---

## 7. ALUR KERJA OPERASIONAL

### Alur Barang Masuk (Inbound):
1. Operator buka halaman "Generate Pallet Form".
2. Isi header: Tanggal, Shift, Nama Operator.
3. Scan SPK → Sistem otomatis isi Part No & Model.
4. Isi Qty dan Warehouse.
5. Scan Label → Sistem validasi → Bunyi beep jika OK.
6. Ulangi langkah 3-5 untuk setiap box.
7. Klik "Generate" → Data tersimpan, rak terupdate.
8. Pilih "Lihat & Print" atau "Lanjut Scan Baru".

### Alur Barang Keluar (Outbound):
1. Operator buka halaman "Outbound".
2. Scan barcode palet.
3. Sistem update status palet ke "OUT" dan kosongkan slot rak.
4. Log transaksi tercatat otomatis.

---

*Dokumen ini di-generate secara otomatis dari source code aktif.*
