<?php

namespace App\Livewire\MaterialWarehouse;

use App\Models\MasterListMaterial;
use App\Models\MwhPallet;
use App\Models\MwhOutgoing;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;

class MaterialStockCard extends Component
{
    use WithPagination;

    public string $selectedItemCode = '';
    public string $search = '';
    public string $filterType = 'ALL'; // ALL, INCOMING, OUTGOING
    public ?string $fromDate = null;
    public ?string $toDate = null;

    protected $queryString = [
        'selectedItemCode' => ['except' => ''],
        'search'           => ['except' => ''],
        'filterType'       => ['except' => 'ALL'],
        'fromDate'         => ['except' => null],
        'toDate'           => ['except' => null],
    ];

    public function getActiveMaterialsProperty()
    {
        $incomingCodes = MwhPallet::whereNotNull('item_code')->select('item_code')->distinct()->pluck('item_code');
        $outgoingCodes = MwhOutgoing::whereNotNull('item_code')->select('item_code')->distinct()->pluck('item_code');

        $activeCodes = $incomingCodes->merge($outgoingCodes)->unique()->filter()->values();

        if ($activeCodes->isEmpty()) {
            return collect();
        }

        $masterMaterials = MasterListMaterial::whereIn('item_code', $activeCodes)->get()->keyBy('item_code');

        return $activeCodes->map(function ($code) use ($masterMaterials) {
            $master = $masterMaterials->get($code);
            return (object) [
                'item_code'          => $code,
                'item_description'   => $master?->item_description ?: 'Material ' . $code,
                'purchasing_uom'     => $master?->purchasing_uom ?: 'KG',
                'preferred_supplier' => $master?->preferred_supplier ?: '-',
            ];
        })->sortBy('item_code')->values();
    }

    public function mount(): void
    {
        // Default to first available active material with transactions if non-selected
        if (empty($this->selectedItemCode)) {
            $firstMat = $this->activeMaterials->first();
            if ($firstMat) {
                $this->selectedItemCode = $firstMat->item_code;
            }
        }
    }

    public function updatingSelectedItemCode(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterType', 'fromDate', 'toDate']);
        $this->resetPage();
    }

    public function render()
    {
        $materials = $this->activeMaterials;
        $selectedMaterial = $materials->firstWhere('item_code', $this->selectedItemCode);

        $movements = collect();
        $summary = [
            'current_stock'  => 0.0,
            'total_incoming' => 0.0,
            'total_outgoing' => 0.0,
            'active_pallets' => 0,
            'uom'            => $selectedMaterial?->purchasing_uom ?? 'KG',
        ];

        if ($this->selectedItemCode) {
            // 1. Calculate live summary stats
            $summary['current_stock'] = (float) MwhPallet::where('item_code', $this->selectedItemCode)
                ->where('current_qty', '>', 0)
                ->sum('current_qty');

            $summary['active_pallets'] = MwhPallet::where('item_code', $this->selectedItemCode)
                ->where('current_qty', '>', 0)
                ->count();

            $summary['total_incoming'] = (float) MwhPallet::where('item_code', $this->selectedItemCode)
                ->sum('initial_qty');

            $summary['total_outgoing'] = (float) MwhOutgoing::where('item_code', $this->selectedItemCode)
                ->sum('qty_taken');

            // 2. Fetch ALL Incoming movements (MwhPallet records) without SQL date cutoff
            $incomings = MwhPallet::with(['incomingHeader', 'position'])
                ->where('item_code', $this->selectedItemCode)
                ->get()
                ->map(function ($pallet) {
                    $dt = $pallet->created_at ?: ($pallet->incomingHeader?->arrival_date ? Carbon::parse($pallet->incomingHeader->arrival_date) : null);
                    return [
                        'id'                 => 'IN-' . $pallet->id,
                        'timestamp'          => $dt,
                        'date_formatted'     => $dt ? $dt->timezone('Asia/Jakarta')->format('d M Y H:i') : '-',
                        'type'               => 'INCOMING',
                        'ref_code'           => $pallet->pallet_id,
                        'sub_ref'            => 'Lot: ' . ($pallet->lot_no ?: '-') . ' | PO: ' . ($pallet->incomingHeader?->po_number ?: '-'),
                        'source_destination' => $pallet->incomingHeader?->supplier_name ?: 'Internal Supplier',
                        'slot_code'          => $pallet->position?->position_code ?: 'UNASSIGNED',
                        'qty_in'             => (float) $pallet->initial_qty,
                        'qty_out'            => 0.0,
                        'uom'                => $pallet->uom ?? 'KG',
                        'remarks'            => 'Penerimaan Pallet Baru (' . number_format($pallet->current_qty, 2) . ' KG sisa)',
                    ];
                });

            // 3. Fetch ALL Outgoing movements (MwhOutgoing records) without SQL date cutoff
            $outgoings = MwhOutgoing::with(['pallet.position', 'position'])
                ->where('item_code', $this->selectedItemCode)
                ->get()
                ->map(function ($out) {
                    $dt = $out->created_at ?: ($out->outgoing_date ? Carbon::parse($out->outgoing_date) : null);
                    return [
                        'id'                 => 'OUT-' . $out->id,
                        'timestamp'          => $dt,
                        'date_formatted'     => $dt ? $dt->timezone('Asia/Jakarta')->format('d M Y H:i') : '-',
                        'type'               => 'OUTGOING',
                        'ref_code'           => $out->outgoing_code,
                        'sub_ref'            => 'Pallet: ' . $out->pallet_id,
                        'source_destination' => 'Tujuan: ' . ($out->issued_to ?: 'Produksi'),
                        'slot_code'          => $out->position?->position_code ?: ($out->pallet?->position?->position_code ?: '-'),
                        'qty_in'             => 0.0,
                        'qty_out'            => (float) $out->qty_taken,
                        'uom'                => $out->uom ?? 'KG',
                        'remarks'            => $out->remarks ?: 'Pengambilan Material',
                    ];
                });

            // 4. Merge and sort chronologically (oldest to newest for calculating running balance)
            $allMovements = $incomings->concat($outgoings)->sortBy('timestamp')->values();

            // 5. Calculate Running Balance & Opening Balance for Date Filter
            $runningBalance = 0.0;
            $fromCarbon = $this->fromDate ? Carbon::parse($this->fromDate)->startOfDay() : null;
            $toCarbon   = $this->toDate ? Carbon::parse($this->toDate)->endOfDay() : null;
            $openingBalance = 0.0;

            $calculatedMovements = collect();

            foreach ($allMovements as $item) {
                $itemDt = $item['timestamp'];
                
                // If transaction happened BEFORE fromDate, accumulate to openingBalance
                if ($fromCarbon && $itemDt && $itemDt->lt($fromCarbon)) {
                    if ($item['type'] === 'INCOMING') {
                        $openingBalance += $item['qty_in'];
                    } else {
                        $openingBalance -= $item['qty_out'];
                    }
                    $runningBalance = max(0.0, $openingBalance);
                    continue;
                }

                if ($item['type'] === 'INCOMING') {
                    $runningBalance += $item['qty_in'];
                } else {
                    $runningBalance -= $item['qty_out'];
                }
                $item['balance'] = max(0.0, $runningBalance);

                // Check toDate limit
                if ($toCarbon && $itemDt && $itemDt->gt($toCarbon)) {
                    continue;
                }

                $calculatedMovements->push($item);
            }

            // 6. Prepend Opening Balance row if fromDate filter is active and openingBalance > 0
            if ($fromCarbon && $openingBalance > 0) {
                $openingRow = [
                    'id'                 => 'OPENING-BAL',
                    'timestamp'          => $fromCarbon,
                    'date_formatted'     => $fromCarbon->timezone('Asia/Jakarta')->format('d M Y') . ' (00:00)',
                    'type'               => 'OPENING_BALANCE',
                    'ref_code'           => 'SALDO AWAL',
                    'sub_ref'            => 'Akumulasi Sebelum ' . $fromCarbon->format('d M Y'),
                    'source_destination' => 'Stok Bawaan Periode Sebelumnya',
                    'slot_code'          => '-',
                    'qty_in'             => $openingBalance,
                    'qty_out'            => 0.0,
                    'uom'                => $selectedMaterial?->purchasing_uom ?? 'KG',
                    'balance'            => $openingBalance,
                    'remarks'            => 'Saldo awal per tanggal ' . $fromCarbon->format('d M Y'),
                ];

                $calculatedMovements->prepend($openingRow);
            }

            // 7. Apply Search and Type Filters
            $movements = $calculatedMovements;

            if ($this->filterType === 'INCOMING') {
                $movements = $movements->filter(fn($i) => in_array($i['type'], ['INCOMING', 'OPENING_BALANCE']));
            } elseif ($this->filterType === 'OUTGOING') {
                $movements = $movements->filter(fn($i) => in_array($i['type'], ['OUTGOING', 'OPENING_BALANCE']));
            }

            if (!empty($this->search)) {
                $term = strtolower(trim($this->search));
                $movements = $movements->filter(function ($item) use ($term) {
                    return str_contains(strtolower($item['ref_code']), $term)
                        || str_contains(strtolower($item['sub_ref']), $term)
                        || str_contains(strtolower($item['source_destination']), $term)
                        || str_contains(strtolower($item['slot_code']), $term)
                        || str_contains(strtolower($item['remarks']), $term);
                });
            }

            // Keep chronological order (oldest to newest, top-to-bottom)
            $movements = $movements->values();
        }

        return view('livewire.material-warehouse.material-stock-card', [
            'materials'        => $materials,
            'selectedMaterial' => $selectedMaterial,
            'movements'        => $movements,
            'summary'          => $summary,
        ]);
    }
}
