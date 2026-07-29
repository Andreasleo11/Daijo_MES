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

    public function mount(): void
    {
        // Default to first available material if non-selected
        if (empty($this->selectedItemCode)) {
            $firstMat = MasterListMaterial::orderBy('item_code', 'asc')->first();
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
        $materials = MasterListMaterial::orderBy('item_code', 'asc')->get();
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

            // 2. Fetch Incoming movements (MwhPallet records)
            $incomingQuery = MwhPallet::with(['incomingHeader', 'position'])
                ->where('item_code', $this->selectedItemCode);

            if ($this->fromDate) {
                $incomingQuery->whereDate('created_at', '>=', $this->fromDate);
            }
            if ($this->toDate) {
                $incomingQuery->whereDate('created_at', '<=', $this->toDate);
            }

            $incomings = $incomingQuery->get()->map(function ($pallet) {
                return [
                    'id'                 => 'IN-' . $pallet->id,
                    'timestamp'          => $pallet->created_at,
                    'date_formatted'     => $pallet->created_at ? $pallet->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') : '-',
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

            // 3. Fetch Outgoing movements (MwhOutgoing records)
            $outgoingQuery = MwhOutgoing::with(['pallet.position', 'position'])
                ->where('item_code', $this->selectedItemCode);

            if ($this->fromDate) {
                $outgoingQuery->whereDate('created_at', '>=', $this->fromDate);
            }
            if ($this->toDate) {
                $outgoingQuery->whereDate('created_at', '<=', $this->toDate);
            }

            $outgoings = $outgoingQuery->get()->map(function ($out) {
                return [
                    'id'                 => 'OUT-' . $out->id,
                    'timestamp'          => $out->created_at,
                    'date_formatted'     => $out->created_at ? $out->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') : '-',
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

            // 5. Calculate Running Balance
            $runningBalance = 0.0;
            $calculatedMovements = $allMovements->map(function ($item) use (&$runningBalance) {
                if ($item['type'] === 'INCOMING') {
                    $runningBalance += $item['qty_in'];
                } else {
                    $runningBalance -= $item['qty_out'];
                }
                $item['balance'] = max(0.0, $runningBalance);
                return $item;
            });

            // 6. Apply Search and Type Filters
            $movements = $calculatedMovements;

            if ($this->filterType === 'INCOMING') {
                $movements = $movements->where('type', 'INCOMING');
            } elseif ($this->filterType === 'OUTGOING') {
                $movements = $movements->where('type', 'OUTGOING');
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

            // Reverse for displaying newest movements on top while preserving balance calculation
            $movements = $movements->reverse()->values();
        }

        return view('livewire.material-warehouse.material-stock-card', [
            'materials'        => $materials,
            'selectedMaterial' => $selectedMaterial,
            'movements'        => $movements,
            'summary'          => $summary,
        ]);
    }
}
