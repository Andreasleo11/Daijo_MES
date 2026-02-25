<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\MasterListItem;
use App\Models\Delivery\SapReject;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliveryAnalysis extends Component
{
    use WithPagination;

    public $filterItemCode = '';
    public $filterDateFrom = '';
    public $filterDateTo   = '';
    public $filterStatus   = '';
    public $perPage        = 10;

    public function mount()
    {
        $minDate = DB::table('sap_delsched')->min('delivery_date');
        $maxDate = DB::table('sap_delsched')->max('delivery_date');

        $this->filterDateFrom = $minDate ?? now()->format('Y-m-d');
        $this->filterDateTo   = $maxDate ?? now()->format('Y-m-d');

        // dd($this->getCustomerList());
    }

    public function updatingFilterItemCode() { $this->resetPage(); }
    public function updatingFilterDateFrom() { $this->resetPage(); }
    public function updatingFilterDateTo()   { $this->resetPage(); }
    public function updatingFilterStatus()   { $this->resetPage(); }

    // Tambah property
    public $filterCustomer = '';

    public function updatingFilterCustomer() { $this->resetPage(); }

    // Tambah method untuk load customer list (untuk dropdown)
    public function getCustomerList(): array
    {
        return MasterListItem::with('customer')
            ->whereNotNull('customer_code')
            ->select('customer_code')
            ->distinct()
            ->get()
            ->map(fn($item) => [
                'code' => $item->customer_code,
                'name' => $item->customer?->customer_name ?? $item->customer_code,
            ])
            ->sortBy('name')
            ->values()
            ->toArray();
    }


    private function getAdjustedStock($itemCode, $baseStock): int
    {
        $rejectRecord = SapReject::where('item_no', $itemCode)->first();
        
        if ($rejectRecord && $rejectRecord->in_stock) {
            return max(0, $baseStock - $rejectRecord->in_stock);
        }
        
        return $baseStock;
    }

    private function generateDateRange(): array
    {
        $dates   = [];
        $current = Carbon::parse($this->filterDateFrom);
        $end     = Carbon::parse($this->filterDateTo);
        while ($current->lte($end)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }
        return $dates;
    }

    public function getAnalysisData(): array
    {
        $dateRange = $this->generateDateRange();

        // ── 1. Kumpulkan item codes (dengan filter) ────────────
        $customerItemCodes = $this->filterCustomer
            ? MasterListItem::where('customer_code', $this->filterCustomer)->pluck('item_code')
            : null;

        $allItemCodes = DB::table('sap_delsched')
            ->when($this->filterItemCode,   fn($q) => $q->where('item_code', 'like', "%{$this->filterItemCode}%"))
            ->when($customerItemCodes,      fn($q) => $q->whereIn('item_code', $customerItemCodes))
            ->whereBetween('delivery_date', [$this->filterDateFrom, $this->filterDateTo])
            ->distinct()->pluck('item_code')
            ->merge(
                DB::table('sap_delactual')
                    ->when($this->filterItemCode, fn($q) => $q->where('item_no', 'like', "%{$this->filterItemCode}%"))
                    ->when($customerItemCodes,    fn($q) => $q->whereIn('item_no', $customerItemCodes))
                    ->whereBetween('delivery_date', [$this->filterDateFrom, $this->filterDateTo])
                    ->distinct()->pluck('item_no')
            )
            ->unique()->values();

        // ── 2. Pagination ──────────────────────────────────────
        $offset     = ($this->getPage() - 1) * $this->perPage;
        $pagedCodes = $allItemCodes->slice($offset, $this->perPage * 3)->values();

        // ── 3. Query data untuk halaman ini ───────────────────
        $schedules   = DB::table('sap_delsched')
            ->whereBetween('delivery_date', [$this->filterDateFrom, $this->filterDateTo])
            ->whereIn('item_code', $pagedCodes)
            ->get()->groupBy('item_code');

        $actuals     = DB::table('sap_delactual')
            ->whereBetween('delivery_date', [$this->filterDateFrom, $this->filterDateTo])
            ->whereIn('item_no', $pagedCodes)
            ->get()->groupBy('item_no');

        $masterItems = MasterListItem::with('customer')
            ->whereIn('item_code', $pagedCodes)
            ->get()->keyBy('item_code');

        // ── 4. BOM — ambil semua children untuk pagedCodes ────
        $bomRows = DB::table('sap_bom_wip')
        ->whereIn('fg_code', $pagedCodes)
        ->get()
        ->groupBy('fg_code')
        ->map(function ($rows) {
            // Per fg_code, group lagi by semi_first
            // Ambil 1 row per semi_first yang paling lengkap (punya semi paling banyak)
            return $rows
                ->groupBy('semi_first')
                ->map(function ($semiRows) {
                    // Hitung "kelengkapan" tiap row: berapa level yang terisi
                    return $semiRows->sortByDesc(function ($row) {
                        $score = 0;
                        if ($row->semi_first  && $row->qty_first)  $score++;
                        if ($row->semi_second && $row->qty_second) $score++;
                        if ($row->semi_third  && $row->qty_third)  $score++;
                        return $score;
                    })->first(); // ambil yang paling lengkap
                })
                ->values(); // jadi collection of rows, 1 per semi_first
        });

        // Kumpulkan semua semi codes untuk 1x query inventory
        $semiCodes = $bomRows->flatMap(fn($rows) =>
            $rows->flatMap(fn($b) => array_filter([
                $b->semi_first, $b->semi_second, $b->semi_third
            ]))
        )->unique()->values();

        // 1 query inventory untuk FG + semi sekaligus
        $inventory = DB::table('sap_inventory_fg')
            ->whereIn('item_code', $pagedCodes->merge($semiCodes)->unique())
            ->get()->keyBy('item_code');

        // ── 5. Helper: hitung daily rolling ───────────────────
        $calcDaily = function (array $seidPerDate, array $actualPerDate, int $inStock) use ($dateRange): array {
            $daily      = [];
            $prevBDel   = null;
            $prevBStock = null;

            foreach ($dateRange as $date) {
                $seid   = $seidPerDate[$date]   ?? 0;
                $actual = $actualPerDate[$date] ?? 0;

                $bDel   = $prevBDel   === null ? $actual - $seid            : $prevBDel   + $actual - $seid;
                $bStock = $prevBStock === null ? $inStock + $actual - $seid : $prevBStock + $actual - $seid;

                $daily[$date] = [
                    'seid_request'  => $seid,
                    'actual'        => $actual,
                    'balance_del'   => $bDel,
                    'balance_stock' => $bStock,
                ];

                $prevBDel   = $bDel;
                $prevBStock = $bStock;
            }

            return $daily;
        };

        // ── 6. Build result ────────────────────────────────────
        $result = [];

        foreach ($pagedCodes as $itemCode) {
            $masterItem   = $masterItems->get($itemCode);
            $inv          = $inventory->get($itemCode);
            $itemActuals  = $actuals->get($itemCode, collect());

            $inStock      = $inv?->stock      ?? 0;
            $itemName     = $inv?->item_name  ?? $itemActuals->first()?->item_name ?? $itemCode;
            $cycleTime    = $masterItem?->cycle_time    ?? null;
            $customerCode = $masterItem?->customer_code ?? null;
            $customerName = $masterItem?->customer?->customer_name ?? null;

            // Susun seid & actual per date
            $seidPerDate   = $schedules->get($itemCode, collect())
                ->groupBy('delivery_date')
                ->map(fn($rows) => $rows->sum('delivery_qty'))
                ->toArray();

            $actualPerDate = $itemActuals
                ->groupBy('delivery_date')
                ->map(fn($rows) => $rows->sum('quantity'))
                ->toArray();

            // Hitung daily FG
            $dailyData   = $calcDaily($seidPerDate, $actualPerDate, $inStock);
            $totalSched  = collect($dailyData)->sum('seid_request');
            $totalActual = collect($dailyData)->sum('actual');

            $diff           = $totalActual - $totalSched;
            $totalShortfall = $diff < 0 ? abs($diff) : 0;
            $totalOver      = $diff > 0 ? $diff : 0;
            $overallStatus  = $totalShortfall > 0 ? 'shortfall' : ($totalOver > 0 ? 'overdelivery' : 'on-time');

            if ($this->filterStatus && $overallStatus !== $this->filterStatus) {
                continue;
            }

            // ── BOM children ──────────────────────────────────
            $children = [];

            foreach ($bomRows->get($itemCode, collect()) as $bom) {
                // Flatten levels: [code, multiplier]
                // Level 2 multiplier = qty_first × qty_second (karena per 1 FG butuh qty_first unit semi_first,
                // dan per 1 semi_first butuh qty_second unit semi_second, dst)
                $levels = [];

                if ($bom->semi_first && $bom->qty_first) {
                    $levels[] = [
                        'code' => $bom->semi_first,
                        'multiplier' => $bom->qty_first,
                        'level' => 1,
                    ];
                }
                if ($bom->semi_second && $bom->qty_second) {
                    $levels[] = [
                        'code' => $bom->semi_second,
                        'multiplier' => ($bom->qty_first ?? 1) * $bom->qty_second,
                        'level' => 2,
                    ];
                }
                if ($bom->semi_third && $bom->qty_third) {
                    $levels[] = [
                        'code' => $bom->semi_third,
                        'multiplier' => ($bom->qty_first ?? 1) * ($bom->qty_second ?? 1) * $bom->qty_third,
                        'level' => 3,
                    ];
                }

                foreach ($levels as $level) {
                    $semiCode  = $level['code'];
                    $multi     = $level['multiplier'];
                    $semiInv   = $inventory->get($semiCode);
                    $semiBaseStock = $semiInv?->stock ?? 0;
                    $semiStock     = $this->getAdjustedStock($semiCode, $semiBaseStock);

                    $semiName  = $semiInv?->item_name ?? $semiCode;

                    // Requirement semi = FG qty × multiplier
                    $semiSeidPerDate   = collect($dailyData)
                        ->mapWithKeys(fn($d, $date) => [$date => $d['seid_request'] * $multi])
                        ->toArray();

                    $semiActualPerDate = collect($dailyData)
                        ->mapWithKeys(fn($d, $date) => [$date => $d['actual'] * $multi])
                        ->toArray();

                    $children[] = [
                        'semi_code'   => $semiCode,
                        'semi_name'   => $semiName,
                        'multiplier'  => $multi,
                        'level'       => $level['level'],
                        'in_stock'    => $semiStock,
                        'daily'       => $calcDaily($semiSeidPerDate, $semiActualPerDate, $semiStock),
                    ];
                }
            }

            $result[] = [
                'item_code'          => $itemCode,
                'item_name'          => $itemName,
                'in_stock'           => $inStock,
                'cycle_time'         => $cycleTime,
                'customer_code'      => $customerCode,
                'customer_name'      => $customerName,
                'total_scheduled'    => $totalSched,
                'total_actual'       => $totalActual,
                'total_shortfall'    => $totalShortfall,
                'total_overdelivery' => $totalOver,
                'status'             => $overallStatus,
                'daily'              => $dailyData,
                'children'           => $children,
            ];

            if (count($result) >= $this->perPage) break;
        }

        return $result;
    }

    public function getTotalItems(): int
    {
        return DB::table('sap_delsched')
            ->when($this->filterItemCode, fn($q) => $q->where('item_code', 'like', "%{$this->filterItemCode}%"))
            ->whereBetween('delivery_date', [$this->filterDateFrom, $this->filterDateTo])
            ->distinct()
            ->count('item_code');
    }

    public function exportExcel(): StreamedResponse
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $dateRange = $this->generateDateRange();
        $today     = Carbon::today()->format('Y-m-d');
        $filename  = 'delivery_analysis_' . now()->format('Ymd_His') . '.xlsx';

        $customerItemCodes = $this->filterCustomer
            ? MasterListItem::where('customer_code', $this->filterCustomer)->pluck('item_code')
            : null;

        $schedules = DB::table('sap_delsched')
            ->whereBetween('delivery_date', [$this->filterDateFrom, $this->filterDateTo])
            ->when($this->filterItemCode,  fn($q) => $q->where('item_code', 'like', "%{$this->filterItemCode}%"))
            ->when($customerItemCodes,     fn($q) => $q->whereIn('item_code', $customerItemCodes))
            ->get()->groupBy('item_code');

        $actuals = DB::table('sap_delactual')
            ->whereBetween('delivery_date', [$this->filterDateFrom, $this->filterDateTo])
            ->when($this->filterItemCode,  fn($q) => $q->where('item_no', 'like', "%{$this->filterItemCode}%"))
            ->when($customerItemCodes,     fn($q) => $q->whereIn('item_no', $customerItemCodes))
            ->get()->groupBy('item_no');

        $itemCodes   = $schedules->keys()->merge($actuals->keys())->unique()->values();
        $masterItems = MasterListItem::with('customer')->whereIn('item_code', $itemCodes)->get()->keyBy('item_code');

        // ── BOM: ambil & deduplicate ───────────────────────────
        $bomRows = DB::table('sap_bom_wip')
            ->whereIn('fg_code', $itemCodes)
            ->get()
            ->groupBy('fg_code')
            ->map(fn($rows) =>
                $rows->groupBy('semi_first')
                    ->map(fn($semiRows) =>
                        $semiRows->sortByDesc(fn($r) =>
                            (($r->semi_first  && $r->qty_first)  ? 1 : 0) +
                            (($r->semi_second && $r->qty_second) ? 1 : 0) +
                            (($r->semi_third  && $r->qty_third)  ? 1 : 0)
                        )->first()
                    )->values()
            );

        // Kumpulkan semua semi codes untuk 1x query inventory
        $semiCodes = $bomRows->flatMap(fn($rows) =>
            $rows->flatMap(fn($b) => array_filter([$b->semi_first, $b->semi_second, $b->semi_third]))
        )->unique()->values();

        $inventory = DB::table('sap_inventory_fg')
            ->whereIn('item_code', $itemCodes->merge($semiCodes)->unique())
            ->get()->keyBy('item_code');

        // ── Helper: hitung daily rolling ──────────────────────
        $calcDaily = function (array $seidPerDate, array $actualPerDate, int $inStock) use ($dateRange): array {
            $daily = []; $prevBDel = null; $prevBStock = null;
            foreach ($dateRange as $date) {
                $seid   = $seidPerDate[$date]   ?? 0;
                $actual = $actualPerDate[$date] ?? 0;
                $bDel   = $prevBDel   === null ? $actual - $seid            : $prevBDel   + $actual - $seid;
                $bStock = $prevBStock === null ? $inStock + $actual - $seid : $prevBStock + $actual - $seid;
                $daily[$date] = compact('seid', 'actual', 'bDel', 'bStock');
                $prevBDel = $bDel; $prevBStock = $bStock;
            }
            return $daily;
        };

        // ── Build $allItems ────────────────────────────────────
        $allItems = [];

        foreach ($itemCodes as $itemCode) {
            $masterItem  = $masterItems->get($itemCode);
            $inv         = $inventory->get($itemCode);
            $itemActuals = $actuals->get($itemCode, collect());

            $baseStock      = $inv?->stock ?? 0;
            $inStock        = $this->getAdjustedStock($itemCode, $baseStock);

            $itemName     = $inv?->item_name  ?? $itemActuals->first()?->item_name ?? $itemCode;
            $cycleTime    = $masterItem?->cycle_time    ?? null;
            $customerName = $masterItem?->customer?->customer_name ?? null;

            $seidPerDate   = $schedules->get($itemCode, collect())
                ->groupBy('delivery_date')->map(fn($r) => $r->sum('delivery_qty'))->toArray();
            $actualPerDate = $itemActuals
                ->groupBy('delivery_date')->map(fn($r) => $r->sum('quantity'))->toArray();

            $dailyData   = $calcDaily($seidPerDate, $actualPerDate, $inStock);
            $totalSched  = collect($dailyData)->sum('seid');
            $totalActual = collect($dailyData)->sum('actual');
            $diff        = $totalActual - $totalSched;
            $status      = $diff < 0 ? 'SHORTFALL' : ($diff > 0 ? 'OVERDELIVERY' : 'ON TIME');

            if ($this->filterStatus) {
                $map = ['shortfall' => 'SHORTFALL', 'overdelivery' => 'OVERDELIVERY', 'on-time' => 'ON TIME'];
                if (($map[$this->filterStatus] ?? '') !== $status) continue;
            }

            // ── BOM children ──────────────────────────────────
            $children = [];
            foreach ($bomRows->get($itemCode, collect()) as $bom) {
                $levels = [];
                if ($bom->semi_first  && $bom->qty_first)
                    $levels[] = ['code' => $bom->semi_first,  'multiplier' => $bom->qty_first, 'level' => 1];
                if ($bom->semi_second && $bom->qty_second)
                    $levels[] = ['code' => $bom->semi_second, 'multiplier' => ($bom->qty_first ?? 1) * $bom->qty_second, 'level' => 2];
                if ($bom->semi_third  && $bom->qty_third)
                    $levels[] = ['code' => $bom->semi_third,  'multiplier' => ($bom->qty_first ?? 1) * ($bom->qty_second ?? 1) * $bom->qty_third, 'level' => 3];

                foreach ($levels as $level) {
                    $semiInv   = $inventory->get($level['code']);
                    $semiStock = $semiInv?->stock     ?? 0;
                    $semiName  = $semiInv?->item_name ?? $level['code'];
                    $multi     = $level['multiplier'];

                    $semiSeid   = collect($dailyData)->mapWithKeys(fn($d, $dt) => [$dt => $d['seid']   * $multi])->toArray();
                    $semiActual = collect($dailyData)->mapWithKeys(fn($d, $dt) => [$dt => $d['actual'] * $multi])->toArray();

                    $children[] = [
                        'semi_code'  => $level['code'],
                        'semi_name'  => $semiName,
                        'multiplier' => $multi,
                        'level'      => $level['level'],
                        'in_stock'   => $semiStock,
                        'daily'      => $calcDaily($semiSeid, $semiActual, $semiStock),
                    ];
                }
            }

            $allItems[] = compact(
                'itemCode', 'itemName', 'inStock', 'cycleTime',
                'customerName', 'totalSched', 'totalActual',
                'dailyData', 'status', 'diff', 'children'
            );
        }

        // ── Spreadsheet ────────────────────────────────────────
        $ss = new Spreadsheet();

        $hdrStyle = fn($fg = 'FFFFFFFF', $bg = 'FF1A1816') => [
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => $fg], 'name' => 'Arial'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => $bg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD8D4CC']]],
        ];

        // ══════════════════════════════════════════════════════
        // SHEET 1: SUMMARY (tidak berubah)
        // ══════════════════════════════════════════════════════
        $ws = $ss->getActiveSheet()->setTitle('SUMMARY');
        $ws->setShowGridLines(false);

        $sumCols    = 11;
        $lastSumCol = Coordinate::stringFromColumnIndex($sumCols);

        $filterInfo = implode('  |  ', array_filter([
            "Period: {$this->filterDateFrom} – {$this->filterDateTo}",
            $this->filterCustomer ? "Customer: {$this->filterCustomer}" : null,
            $this->filterItemCode ? "Item: {$this->filterItemCode}" : null,
            $this->filterStatus   ? "Status: " . strtoupper($this->filterStatus) : null,
            "Export: " . now()->format('d M Y H:i'),
        ]));

        $ws->mergeCells("A1:{$lastSumCol}1");
        $ws->setCellValue('A1', 'DELIVERY SCHEDULE ANALYSIS');
        $ws->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Arial'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF1A1816']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 2, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws->getRowDimension(1)->setRowHeight(32);

        $ws->mergeCells("A2:{$lastSumCol}2");
        $ws->setCellValue('A2', $filterInfo);
        $ws->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'color' => ['argb' => 'FF7A756E'], 'name' => 'Arial'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFF7F5F2']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 2],
        ]);
        $ws->getRowDimension(2)->setRowHeight(18);
        $ws->getRowDimension(3)->setRowHeight(6);

        $sumHeaders = ['#', 'Customer', 'Item Code', 'Item Name', 'Cycle Time', 'In Stock', 'Scheduled', 'Actual', 'Shortfall', 'Overdelivery', 'Status'];
        $sumWidths  = [5, 22, 18, 38, 10, 12, 12, 12, 12, 13, 14];
        foreach ($sumHeaders as $i => $h) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $ws->setCellValue("{$col}4", $h);
            $ws->getStyle("{$col}4")->applyFromArray($hdrStyle());
            $ws->getColumnDimension($col)->setWidth($sumWidths[$i]);
        }
        $ws->getRowDimension(4)->setRowHeight(20);

        $shortfallCnt = 0; $overCnt = 0; $ontimeCnt = 0;
        foreach ($allItems as $idx => $item) {
            $row      = 5 + $idx;
            $statusBg = match($item['status']) {
                'SHORTFALL'    => 'FFFEE2E2',
                'OVERDELIVERY' => 'FFDBEAFE',
                default        => 'FFD1FAE5',
            };
            if ($item['status'] === 'SHORTFALL')    $shortfallCnt++;
            if ($item['status'] === 'OVERDELIVERY') $overCnt++;
            if ($item['status'] === 'ON TIME')      $ontimeCnt++;

            $rowData = [
                $idx + 1, $item['customerName'], $item['itemCode'], $item['itemName'],
                $item['cycleTime'], $item['inStock'], $item['totalSched'], $item['totalActual'],
                $item['diff'] < 0 ? abs($item['diff']) : null,
                $item['diff'] > 0 ? $item['diff'] : null,
                $item['status'],
            ];
            $aligns = [
                Alignment::HORIZONTAL_CENTER, Alignment::HORIZONTAL_LEFT, Alignment::HORIZONTAL_LEFT,
                Alignment::HORIZONTAL_LEFT, Alignment::HORIZONTAL_RIGHT, Alignment::HORIZONTAL_RIGHT,
                Alignment::HORIZONTAL_RIGHT, Alignment::HORIZONTAL_RIGHT, Alignment::HORIZONTAL_RIGHT,
                Alignment::HORIZONTAL_RIGHT, Alignment::HORIZONTAL_CENTER,
            ];
            foreach ($rowData as $ci => $val) {
                $col = Coordinate::stringFromColumnIndex($ci + 1);
                $ws->setCellValue("{$col}{$row}", $val);
                $fg = match(true) {
                    $ci === 8 && $val !== null => 'FFD62828',
                    $ci === 9 && $val !== null => 'FF1D3557',
                    default => 'FF1A1816',
                };
                $ws->getStyle("{$col}{$row}")->applyFromArray([
                    'font'      => ['size' => 9, 'name' => 'Arial', 'color' => ['argb' => $fg]],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => $statusBg]],
                    'alignment' => ['horizontal' => $aligns[$ci], 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD8D4CC']]],
                ]);
                if (in_array($ci, [4, 5, 6, 7, 8, 9]) && $val !== null)
                    $ws->getStyle("{$col}{$row}")->getNumberFormat()->setFormatCode('#,##0');
            }
            $ws->getRowDimension($row)->setRowHeight(18);
        }

        $totRow = 5 + count($allItems);
        $ws->mergeCells("A{$totRow}:{$lastSumCol}{$totRow}");
        $ws->setCellValue("A{$totRow}", "Total: " . count($allItems) . " items  |  Shortfall: {$shortfallCnt}  |  Overdelivery: {$overCnt}  |  On Time: {$ontimeCnt}");
        $ws->getStyle("A{$totRow}:{$lastSumCol}{$totRow}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Arial'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF1A1816']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 2, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws->getRowDimension($totRow)->setRowHeight(20);
        $ws->freezePane('A5');

        // ══════════════════════════════════════════════════════
        // SHEET 2: ANALYSIS (FG + BOM children dalam 1 sheet)
        // ══════════════════════════════════════════════════════
        $wsAn = $ss->createSheet()->setTitle('ANALYSIS');
        $wsAn->setShowGridLines(false);

        $numDates    = count($dateRange);
        $lastDateCol = Coordinate::stringFromColumnIndex(2 + $numDates);

        $wsAn->mergeCells("A1:{$lastDateCol}1");
        $wsAn->setCellValue('A1', 'DELIVERY SCHEDULE ANALYSIS  ·  ' . $filterInfo);
        $wsAn->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Arial'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF1A1816']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 2, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $wsAn->getRowDimension(1)->setRowHeight(24);

        $wsAn->setCellValue('A2', 'ITEM / REMARKS');
        $wsAn->setCellValue('B2', 'STK AWAL');
        foreach ($dateRange as $di => $date) {
            $col = Coordinate::stringFromColumnIndex(3 + $di);
            $wsAn->setCellValue("{$col}2", Carbon::parse($date)->format('d-M'));
        }
        $wsAn->getStyle("A2:{$lastDateCol}2")->applyFromArray($hdrStyle());
        $wsAn->getColumnDimension('A')->setWidth(32);
        $wsAn->getColumnDimension('B')->setWidth(11);
        foreach ($dateRange as $di => $date)
            $wsAn->getColumnDimension(Coordinate::stringFromColumnIndex(3 + $di))->setWidth(8);
        $wsAn->getRowDimension(2)->setRowHeight(18);
        $wsAn->freezePane('C3');

        $currentRow = 3;
        $fgRowDefs  = [
            ['SEID Request', 'seid',   'FFFAFAF8'],
            ['Actual Del',   'actual', 'FFF0FAF4'],
            ['Balance Del',  'bDel',   'FFFFFBF0'],
            ['Balance Stock','bStock', 'FFF0F5FF'],
        ];
        $wipRowDefs = [
            ['SEID Req',  'seid',   'FFFAFAF8'],
            ['Actual',    'actual', 'FFF0FAF4'],
            ['Bal Del',   'bDel',   'FFFFFBF0'],
            ['Bal Stock', 'bStock', 'FFF0F5FF'],
        ];

        foreach ($allItems as $item) {
            // ── FG group header ────────────────────────────────
            $statusBg = match($item['status']) {
                'SHORTFALL'    => 'FFFEE2E2',
                'OVERDELIVERY' => 'FFDBEAFE',
                default        => 'FFD1FAE5',
            };
            $statusFg = match($item['status']) {
                'SHORTFALL'    => 'FFD62828',
                'OVERDELIVERY' => 'FF1D3557',
                default        => 'FF166534',
            };
            $headerText = '[FG] ' . $item['itemCode'] . '  ·  ' . $item['itemName']
                . ($item['customerName'] ? '  [' . $item['customerName'] . ']' : '')
                . '    ' . $item['status']
                . '  Sched: '  . number_format($item['totalSched'])
                . '  Actual: ' . number_format($item['totalActual'])
                . ($item['diff'] < 0 ? '  Shortfall: ' . number_format(abs($item['diff'])) : '')
                . ($item['diff'] > 0 ? '  Over: +'     . number_format($item['diff']) : '');

            $wsAn->mergeCells("A{$currentRow}:{$lastDateCol}{$currentRow}");
            $wsAn->setCellValue("A{$currentRow}", $headerText);
            $wsAn->getStyle("A{$currentRow}:{$lastDateCol}{$currentRow}")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => $statusFg], 'name' => 'Arial'],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => $statusBg]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 2, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD8D4CC']]],
            ]);
            $wsAn->getRowDimension($currentRow)->setRowHeight(16);
            $currentRow++;

            // ── FG 4 baris ────────────────────────────────────
            foreach ($fgRowDefs as [$label, $key, $rowBg]) {
                $wsAn->setCellValue("A{$currentRow}", $label);
                $wsAn->setCellValue("B{$currentRow}", $key === 'bStock' ? $item['inStock'] : null);

                $rowValues = [];
                foreach ($dateRange as $date) {
                    $val = $item['dailyData'][$date][$key];
                    $rowValues[] = (in_array($key, ['seid', 'actual']) && $val == 0) ? null : $val;
                }
                $wsAn->fromArray($rowValues, null, "C{$currentRow}");
                $wsAn->getStyle("A{$currentRow}:{$lastDateCol}{$currentRow}")->applyFromArray([
                    'font'      => ['size' => 9, 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => $rowBg]],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD8D4CC']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $wsAn->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $wsAn->getStyle("B{$currentRow}:{$lastDateCol}{$currentRow}")
                    ->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0;-');
                $wsAn->getRowDimension($currentRow)->setRowHeight(15);
                $currentRow++;
            }

            // ── BOM Children ──────────────────────────────────
            if (!empty($item['children'])) {
                // BOM separator
                $wsAn->mergeCells("A{$currentRow}:{$lastDateCol}{$currentRow}");
                $wsAn->setCellValue("A{$currentRow}", '  WIP COMPONENTS (' . count($item['children']) . ')');
                $wsAn->getStyle("A{$currentRow}:{$lastDateCol}{$currentRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF7A756E'], 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFF0EEE9']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 2],
                    'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD8D4CC']]],
                ]);
                $wsAn->getRowDimension($currentRow)->setRowHeight(12);
                $currentRow++;

                foreach ($item['children'] as $child) {
                    $indent = str_repeat('  ', $child['level']);

                    // Child header
                    $childHeader = $indent . '[L' . $child['level'] . '] '
                        . $child['semi_code'] . '  ·  ' . $child['semi_name']
                        . '  ×' . $child['multiplier']
                        . '    Stk: ' . number_format($child['in_stock']);

                    $wsAn->mergeCells("A{$currentRow}:{$lastDateCol}{$currentRow}");
                    $wsAn->setCellValue("A{$currentRow}", $childHeader);
                    $wsAn->getStyle("A{$currentRow}:{$lastDateCol}{$currentRow}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF3D3935'], 'name' => 'Arial'],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFF7F5F2']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 2],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD8D4CC']]],
                    ]);
                    $wsAn->getRowDimension($currentRow)->setRowHeight(14);
                    $currentRow++;

                    // 4 baris child — font lebih kecil, bg sedikit muted
                    $childRowBgs = ['FFFCFCFB', 'FFF5FAF7', 'FFFFFDF5', 'FFF5F7FC'];
                    foreach ($wipRowDefs as $ri => [$label, $key, $rowBg]) {
                        $wsAn->setCellValue("A{$currentRow}", $indent . '  ' . $label);
                        $wsAn->setCellValue("B{$currentRow}", $key === 'bStock' ? $child['in_stock'] : null);

                        $rowValues = [];
                        foreach ($dateRange as $date) {
                            $val = $child['daily'][$date][$key] ?? 0;
                            $rowValues[] = (in_array($key, ['seid', 'actual']) && $val == 0) ? null : $val;
                        }
                        $wsAn->fromArray($rowValues, null, "C{$currentRow}");
                        $wsAn->getStyle("A{$currentRow}:{$lastDateCol}{$currentRow}")->applyFromArray([
                            'font'      => ['size' => 8, 'name' => 'Arial', 'color' => ['argb' => 'FF5A554E']],
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => $childRowBgs[$ri]]],
                            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE8E4DC']]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                        ]);
                        $wsAn->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        $wsAn->getStyle("B{$currentRow}:{$lastDateCol}{$currentRow}")
                            ->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0;-');
                        $wsAn->getRowDimension($currentRow)->setRowHeight(13);
                        $currentRow++;
                    }
                }
            }

            // Spacer antar item
            $wsAn->getRowDimension($currentRow)->setRowHeight(5);
            $currentRow++;
        }

        // ══════════════════════════════════════════════════════
        // SHEET 3: RAW DATA — FG + WIP rows
        // ══════════════════════════════════════════════════════
        $wsRaw = $ss->createSheet()->setTitle('RAW DATA');
        $wsRaw->setShowGridLines(false);

        $rawHeaders = ['Type','Item Code','Item Name','Semi Code','Semi Name','Level','Multiplier',
                    'Customer','In Stock','Date','Day','SEID Request','Actual Del','Balance Del','Balance Stock','Status'];
        $rawWidths  = [8, 18, 30, 18, 30, 6, 10, 22, 10, 14, 10, 12, 12, 12, 12, 12];

        $wsRaw->fromArray($rawHeaders, null, 'A1');
        $lastRawCol = Coordinate::stringFromColumnIndex(count($rawHeaders));
        $wsRaw->getStyle("A1:{$lastRawCol}1")->applyFromArray($hdrStyle());
        foreach ($rawWidths as $i => $w)
            $wsRaw->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))->setWidth($w);
        $wsRaw->getRowDimension(1)->setRowHeight(20);

        $rawBatch = [];
        foreach ($allItems as $item) {
            // FG rows
            foreach ($dateRange as $date) {
                $d = $item['dailyData'][$date];
                if ($d['seid'] == 0 && $d['actual'] == 0) continue;
                $dt = Carbon::parse($date);
                $rawBatch[] = [
                    'FG', $item['itemCode'], $item['itemName'],
                    '', '', '', '',
                    $item['customerName'] ?? '',
                    $item['inStock'],
                    $dt->format('d-M-Y'), $dt->format('l'),
                    $d['seid'], $d['actual'], $d['bDel'], $d['bStock'],
                    $item['status'],
                ];
            }

            // WIP/children rows
            foreach ($item['children'] as $child) {
                foreach ($dateRange as $date) {
                    $d = $child['daily'][$date];
                    if ($d['seid'] == 0 && $d['actual'] == 0) continue;
                    $dt = Carbon::parse($date);
                    $rawBatch[] = [
                        'WIP', $item['itemCode'], $item['itemName'],
                        $child['semi_code'], $child['semi_name'],
                        $child['level'], $child['multiplier'],
                        $item['customerName'] ?? '',
                        $child['in_stock'],
                        $dt->format('d-M-Y'), $dt->format('l'),
                        $d['seid'], $d['actual'], $d['bDel'], $d['bStock'],
                        $item['status'],
                    ];
                }
            }
        }

        if (!empty($rawBatch)) {
            $wsRaw->fromArray($rawBatch, null, 'A2');
            $lastRow = 1 + count($rawBatch);

            $wsRaw->getStyle("A2:{$lastRawCol}{$lastRow}")->applyFromArray([
                'font'      => ['size' => 9, 'name' => 'Arial'],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD8D4CC']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Number format kolom angka
            foreach ([9, 12, 13, 14, 15] as $numCol) {
                $col = Coordinate::stringFromColumnIndex($numCol);
                $wsRaw->getStyle("{$col}2:{$col}{$lastRow}")
                    ->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0;-');
            }

            // Warnai WIP rows sedikit beda
            foreach ($rawBatch as $ri => $row) {
                $excelRow = 2 + $ri;
                if ($row[0] === 'WIP') {
                    $wsRaw->getStyle("A{$excelRow}")->getFont()->getColor()->setARGB('FF7A756E');
                }
                if (is_numeric($row[13]) && $row[13] < 0)
                    $wsRaw->getStyle(Coordinate::stringFromColumnIndex(14) . $excelRow)->getFont()->getColor()->setARGB('FFD62828');
                if (is_numeric($row[14]) && $row[14] < 0)
                    $wsRaw->getStyle(Coordinate::stringFromColumnIndex(15) . $excelRow)->getFont()->getColor()->setARGB('FFD62828');
            }
        }

        $wsRaw->setAutoFilter("A1:{$lastRawCol}1");
        $wsRaw->freezePane('A2');
        $ss->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($ss) {
            (new Xlsx($ss))->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function render()
    {
        $analysisData = $this->getAnalysisData();
        $dateRange    = $this->generateDateRange();
        $totalItems   = $this->getTotalItems();
        $totalPages   = (int) ceil($totalItems / $this->perPage);
        $currentPage  = $this->getPage();

        return view('livewire.delivery-analysis', [
            'analysisData' => $analysisData,
            'dateRange'    => $dateRange,
            'totalItems'   => $totalItems,
            'totalPages'   => $totalPages,
            'currentPage'  => $currentPage,
            'customerList' => $this->getCustomerList(), // ← tambah
        ]);
    }
}