<?php

namespace App\Services;

use App\Models\WmsPalletForm;
use App\Models\WmsPalletFormDetail;
use App\Models\MasterListItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WmsDeliveryRecapService
{
    /**
     * Dapatkan tanggal produksi saat ini berdasarkan aturan cutoff:
     * - Jam 07:30:00 s/d 23:59:59 -> tanggal hari ini
     * - Jam 00:00:00 s/d 07:29:59 -> tanggal kemarin (shift 3 masih hari produksi kemarin)
     */
    public static function getCurrentProductionDate(): string
    {
        $now = now();
        if ($now->format('H:i:s') < '07:30:00') {
            return $now->copy()->subDay()->format('Y-m-d');
        }
        return $now->format('Y-m-d');
    }

    /**
     * Hitung rentang waktu DateTime cutoff [start, end]:
     * Tanggal D dimulai pada: D 07:30:00
     * Berakhir pada: (D + 1 hari) 07:29:59
     */
    public static function getTimeWindow(string $selectedDate): array
    {
        $start = Carbon::parse($selectedDate)->setTime(7, 30, 0);
        $end   = Carbon::parse($selectedDate)->addDay()->setTime(7, 29, 59);

        return [$start, $end];
    }

    /**
     * Mengambil dan mengagregasikan seluruh data delivery untuk 1 hari produksi tertentu
     */
    public function getDailyRecap(
        string $selectedDate,
        ?string $shiftFilter = null,
        ?string $deliveryFilter = null,
        ?string $search = null
    ): array {
        [$startTime, $endTime] = self::getTimeWindow($selectedDate);

        // 1. Query seluruh Pallet Form dalam window cutoff 07:30 - 07:29
        $palletQuery = WmsPalletForm::query()
            ->with([
                'details.item.customer',
                'position.rack.warehouse',
            ])
            ->whereBetween('created_at', [$startTime, $endTime]);

        if (!empty($shiftFilter) && $shiftFilter !== 'ALL') {
            $palletQuery->where('delivery_shift', $shiftFilter);
        }

        if (!empty($deliveryFilter) && $deliveryFilter !== 'ALL') {
            $palletQuery->where('delivery_name', $deliveryFilter);
        }

        $pallets = $palletQuery->orderBy('created_at', 'asc')->get();

        // 2. Kumpulkan filter opsi unik untuk dropdown di UI
        $allDeliveriesInWindow = WmsPalletForm::whereBetween('created_at', [$startTime, $endTime])
            ->pluck('delivery_name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // 3. Agregasi data KPI & Grouping by Item Code
        $groupedItems = [];
        $totalPcs     = 0;
        $totalBoxes   = 0;
        $palletsCount = $pallets->count();
        $flatBoxLogs  = [];

        $shiftSummary = [
            1 => ['qty' => 0, 'boxes' => 0, 'pallets' => 0],
            2 => ['qty' => 0, 'boxes' => 0, 'pallets' => 0],
            3 => ['qty' => 0, 'boxes' => 0, 'pallets' => 0],
        ];

        // Track pallet id per shift untuk menghitung jumlah pallet unik per shift
        $palletsPerShift = [1 => [], 2 => [], 3 => []];

        foreach ($pallets as $pallet) {
            $shift = (int) ($pallet->delivery_shift ?: 1);
            if (!isset($shiftSummary[$shift])) {
                $shift = 1;
            }

            $palletsPerShift[$shift][$pallet->pallet_id] = true;
            $slotCode = $pallet->position ? $pallet->position->position_code : 'TEMPORARY';

            foreach ($pallet->details as $detail) {
                $partNo = trim((string) ($detail->part_no ?: 'UNKNOWN'));
                $qty    = (float) ($detail->qty ?: 0);

                // Filter search (jika diisi)
                if (!empty($search)) {
                    $searchLower = strtolower(trim($search));
                    $matchSearch = str_contains(strtolower($partNo), $searchLower)
                        || str_contains(strtolower((string) $detail->model_name), $searchLower)
                        || str_contains(strtolower((string) $detail->spk_no), $searchLower)
                        || str_contains(strtolower((string) $pallet->pallet_id), $searchLower)
                        || str_contains(strtolower((string) $pallet->delivery_name), $searchLower)
                        || str_contains(strtolower((string) ($detail->item?->customer?->customer_name ?? '')), $searchLower);

                    if (!$matchSearch) {
                        continue;
                    }
                }

                $totalPcs   += $qty;
                $totalBoxes += 1;

                $shiftSummary[$shift]['qty']   += $qty;
                $shiftSummary[$shift]['boxes'] += 1;

                // Resolve Nama Item & Customer
                $itemName     = $detail->model_name ?: ($detail->item?->item_name ?: $partNo);
                $customerName = $detail->item?->customer?->customer_name ?: ($detail->item?->customer_code ?: '-');

                // Inisialisasi Group Item Code
                if (!isset($groupedItems[$partNo])) {
                    $groupedItems[$partNo] = [
                        'part_no'         => $partNo,
                        'item_name'       => $itemName,
                        'customer_name'   => $customerName,
                        'total_qty'       => 0,
                        'total_boxes'     => 0,
                        'pallets'         => [], // pallet_id => summary
                        'spk_numbers'     => [],
                        'delivery_names'  => [],
                        'shift_breakdown' => [
                            1 => ['qty' => 0, 'boxes' => 0],
                            2 => ['qty' => 0, 'boxes' => 0],
                            3 => ['qty' => 0, 'boxes' => 0],
                        ],
                    ];
                }

                // Update total per item
                $groupedItems[$partNo]['total_qty']   += $qty;
                $groupedItems[$partNo]['total_boxes'] += 1;
                $groupedItems[$partNo]['shift_breakdown'][$shift]['qty']   += $qty;
                $groupedItems[$partNo]['shift_breakdown'][$shift]['boxes'] += 1;

                if (!empty($detail->spk_no)) {
                    $groupedItems[$partNo]['spk_numbers'][$detail->spk_no] = true;
                }
                if (!empty($pallet->delivery_name)) {
                    $groupedItems[$partNo]['delivery_names'][$pallet->delivery_name] = true;
                }

                // Agregasi detail per pallet di bawah item ini
                if (!isset($groupedItems[$partNo]['pallets'][$pallet->pallet_id])) {
                    $groupedItems[$partNo]['pallets'][$pallet->pallet_id] = [
                        'pallet_id'      => $pallet->pallet_id,
                        'delivery_name'  => $pallet->delivery_name ?: '-',
                        'delivery_shift' => $shift,
                        'lot_no'         => $pallet->lot_no ?: '-',
                        'slot'           => $slotCode,
                        'status'         => $pallet->status ?: 'STORED',
                        'created_at'     => $pallet->created_at ? $pallet->created_at->format('H:i') : '-',
                        'created_full'   => $pallet->created_at ? $pallet->created_at->format('d M Y H:i:s') : '-',
                        'item_qty'       => 0,
                        'box_count'      => 0,
                        'spk_list'       => [],
                        'boxes'          => [],
                    ];
                }

                $groupedItems[$partNo]['pallets'][$pallet->pallet_id]['item_qty']  += $qty;
                $groupedItems[$partNo]['pallets'][$pallet->pallet_id]['box_count'] += 1;
                if (!empty($detail->spk_no)) {
                    $groupedItems[$partNo]['pallets'][$pallet->pallet_id]['spk_list'][$detail->spk_no] = true;
                }

                $boxEntry = [
                    'label'           => $detail->label ?: '-',
                    'spk_no'          => $detail->spk_no ?: '-',
                    'qty'             => $qty,
                    'is_no_label'     => (bool) $detail->is_no_label,
                    'no_label_reason' => $detail->no_label_reason,
                    'scan_time'       => $detail->created_at ? $detail->created_at->format('H:i') : '-',
                ];

                $groupedItems[$partNo]['pallets'][$pallet->pallet_id]['boxes'][] = $boxEntry;

                // Flat Box Log untuk Tab Audit / Sheet 2 Excel
                $flatBoxLogs[] = [
                    'pallet_id'      => $pallet->pallet_id,
                    'delivery_name'  => $pallet->delivery_name ?: '-',
                    'shift'          => $shift,
                    'lot_no'         => $pallet->lot_no ?: '-',
                    'slot'           => $slotCode,
                    'part_no'        => $partNo,
                    'item_name'      => $itemName,
                    'customer_name'  => $customerName,
                    'spk_no'         => $detail->spk_no ?: '-',
                    'label'          => $detail->label ?: ($detail->is_no_label ? 'TANPA LABEL' : '-'),
                    'qty'            => $qty,
                    'scan_time'      => $detail->created_at ? $detail->created_at->format('d/m/Y H:i:s') : '-',
                ];
            }
        }

        // Hitung pallet count per shift
        foreach ($shiftSummary as $s => &$sData) {
            $sData['pallets'] = count($palletsPerShift[$s] ?? []);
        }
        unset($sData);

        // Rapikan format array dan sorting
        $formattedItems = [];
        foreach ($groupedItems as $item) {
            $palletsList = array_values($item['pallets']);
            foreach ($palletsList as &$p) {
                $p['spk_list'] = array_keys($p['spk_list']);
            }
            unset($p);

            $formattedItems[] = [
                'part_no'         => $item['part_no'],
                'item_name'       => $item['item_name'],
                'customer_name'   => $item['customer_name'],
                'total_qty'       => $item['total_qty'],
                'total_boxes'     => $item['total_boxes'],
                'pallets_count'   => count($item['pallets']),
                'spk_list'        => array_keys($item['spk_numbers']),
                'delivery_names'  => array_keys($item['delivery_names']),
                'shift_breakdown' => $item['shift_breakdown'],
                'pallets'         => $palletsList,
            ];
        }

        // Urutkan item code berdasarkan total_qty terbesar ke terkecil
        usort($formattedItems, fn($a, $b) => $b['total_qty'] <=> $a['total_qty']);

        // Data rekap pallet langsung (Tab 2)
        $palletsSummaryList = [];
        foreach ($pallets as $p) {
            $pItems = $p->details->groupBy('part_no')->map(function ($rows, $code) {
                return [
                    'part_no' => $code,
                    'model'   => $rows->first()->model_name ?: $code,
                    'qty'     => $rows->sum('qty'),
                    'boxes'   => $rows->count(),
                ];
            })->values()->toArray();

            $palletsSummaryList[] = [
                'pallet_id'        => $p->pallet_id,
                'delivery_name'    => $p->delivery_name ?: '-',
                'delivery_shift'   => $p->delivery_shift ?: '-',
                'lot_no'           => $p->lot_no ?: '-',
                'total_box'        => $p->box_qty ?: $p->details->count(),
                'total_qty'        => (float) $p->total_pallet_qty,
                'status'           => $p->status ?: 'STORED',
                'slot'             => $p->position ? $p->position->position_code : 'TEMPORARY',
                'created_at_time'  => $p->created_at ? $p->created_at->format('H:i') : '-',
                'created_at_full'  => $p->created_at ? $p->created_at->format('d M Y H:i:s') : '-',
                'items_count'      => count($pItems),
                'items'            => $pItems,
                'remarks'          => $p->remarks ?: '-',
            ];
        }

        return [
            'selected_date'           => $selectedDate,
            'time_window_start'       => $startTime->format('d M Y H:i:s'),
            'time_window_end'         => $endTime->format('d M Y H:i:s'),
            'time_window_label'       => $startTime->format('d M Y 07:30') . ' - ' . $endTime->format('d M Y 07:29') . ' WIB',
            'kpis'                    => [
                'total_qty'        => $totalPcs,
                'total_boxes'      => $totalBoxes,
                'total_pallets'    => $palletsCount,
                'total_models'     => count($formattedItems),
                'total_deliveries' => count($allDeliveriesInWindow),
                'shifts'           => $shiftSummary,
            ],
            'items'                   => $formattedItems,
            'pallets_list'            => $palletsSummaryList,
            'box_logs'                => $flatBoxLogs,
            'available_deliveries'    => $allDeliveriesInWindow,
        ];
    }
}
