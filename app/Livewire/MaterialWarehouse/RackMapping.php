<?php

namespace App\Livewire\MaterialWarehouse;

use App\Models\MasterListMaterial;
use App\Models\MwhIncomingHeader;
use App\Models\MwhWarehouse;
use App\Models\MwhRack;
use App\Models\MwhPosition;
use App\Models\MwhPallet;
use App\Services\MaterialWarehouseService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RackMapping extends Component
{
    public $selectedPositionId;
    public $editPositionCode;
    public $editSlotLabel;
    public $editMaxCapacity;
    public $editStatus;
    public $editLastItemCode;
    
    // Add Material to Slot Form State
    public bool $showAddMaterialForm = false;
    public string $new_item_code = '';
    public string $new_item_description = '';
    public string $new_qty = '';
    public string $new_lot_no = '';
    public string $new_supplier_name = '';
    public string $new_po_number = '';
    public string $new_created_at = '';
    public array $newMaterialSearchResults = [];

    // Public / View-Only State
    public bool $isViewOnly = false;

    // UI & Filter State
    public $showDetail = false;
    public string $searchTerm = '';
    public string $selectedItemFilter = '';
    public bool $showFifoSummaryModal = false;
    public ?string $expandedFifoItemCode = null;

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'selectedItemFilter' => ['except' => ''],
    ];

    public function toggleFifoItemExpand(string $itemCode): void
    {
        if ($this->expandedFifoItemCode === $itemCode) {
            $this->expandedFifoItemCode = null;
        } else {
            $this->expandedFifoItemCode = $itemCode;
        }
    }

    public function selectPosition($id)
    {
        $this->selectedPositionId = $id;
        $pos = MwhPosition::find($id);
        if ($pos) {
            $this->editPositionCode = $pos->position_code;
            $this->editSlotLabel = $pos->slot_label;
            $this->editMaxCapacity = $pos->max_capacity;
            $this->editStatus = $pos->status;
            $this->editLastItemCode = $pos->last_item_code;
            $this->showDetail = true;
            $this->showAddMaterialForm = false;
            $this->new_created_at = now()->format('Y-m-d');
            $this->reset(['new_item_code', 'new_item_description', 'new_qty', 'new_lot_no', 'new_supplier_name', 'new_po_number', 'newMaterialSearchResults']);
        }
    }

    public function updatedNewItemCode($value): void
    {
        $query = trim($value);
        if (strlen($query) >= 2) {
            $this->newMaterialSearchResults = MasterListMaterial::query()
                ->where('item_code', 'like', '%' . $query . '%')
                ->orWhere('item_description', 'like', '%' . $query . '%')
                ->limit(10)
                ->get(['item_code', 'item_description', 'purchasing_uom'])
                ->toArray();
        } else {
            $this->newMaterialSearchResults = [];
        }
    }

    public function selectNewMaterial(string $itemCode, string $itemDesc): void
    {
        $this->new_item_code            = $itemCode;
        $this->new_item_description     = $itemDesc;
        $this->newMaterialSearchResults = [];
    }

    public function toggleAddMaterialForm(): void
    {
        $this->showAddMaterialForm = !$this->showAddMaterialForm;
        if (!$this->showAddMaterialForm) {
            $this->reset(['new_item_code', 'new_item_description', 'new_qty', 'new_lot_no', 'new_supplier_name', 'new_po_number', 'new_created_at', 'newMaterialSearchResults']);
        } else {
            $this->new_created_at = now()->format('Y-m-d');
        }
    }

    public function storeMaterialToSlot(MaterialWarehouseService $mwhService): void
    {
        $this->validate([
            'selectedPositionId' => 'required|exists:mwh_positions,id',
            'new_item_code'      => 'required|string|exists:master_list_materials,item_code',
            'new_qty'            => 'required|numeric|min:0.01',
            'new_created_at'     => 'required|date',
            'new_lot_no'         => 'nullable|string|max:255',
            'new_supplier_name'  => 'nullable|string|max:255',
            'new_po_number'      => 'nullable|string|max:255',
        ], [
            'new_item_code.required' => 'Part Code material harus diisi.',
            'new_item_code.exists'   => 'Part Code material tidak ada di Master List.',
            'new_qty.required'       => 'Jumlah (KG) harus diisi.',
            'new_created_at.required' => 'Tanggal masuk (FIFO) harus diisi.',
        ]);

        $pos = MwhPosition::find($this->selectedPositionId);
        if (!$pos) return;

        try {
            DB::beginTransaction();

            $arrivalDate = date('Y-m-d', strtotime($this->new_created_at));
            $createdAtTimestamp = date('Y-m-d H:i:s', strtotime($this->new_created_at . ' ' . now()->format('H:i:s')));

            $header = MwhIncomingHeader::create([
                'document_no'   => $mwhService->generateDocumentNo(),
                'supplier_name' => trim($this->new_supplier_name) ?: null,
                'po_number'     => trim($this->new_po_number) ?: null,
                'arrival_date'  => $arrivalDate,
                'remarks'       => 'Direct input via Material Warehouse Mapping',
                'created_at'    => $createdAtTimestamp,
            ]);

            $remainingToSplit = (float) $this->new_qty;

            while ($remainingToSplit > 0) {
                $palletQty = min(1000.0, $remainingToSplit);
                $remainingToSplit -= $palletQty;

                $inserted = false;
                $attempts = 0;

                while (!$inserted && $attempts < 5) {
                    $attempts++;
                    $palletId = $mwhService->generatePalletId();

                    try {
                        MwhPallet::create([
                            'pallet_id'          => $palletId,
                            'incoming_header_id' => $header->id,
                            'item_code'          => strtoupper(trim($this->new_item_code)),
                            'lot_no'             => trim($this->new_lot_no) ?: null,
                            'initial_qty'        => $palletQty,
                            'current_qty'        => $palletQty,
                            'uom'                => 'KG',
                            'position_id'        => $pos->id,
                            'status'             => 'STORED',
                            'created_at'         => $createdAtTimestamp,
                        ]);
                        $inserted = true;
                    } catch (\Illuminate\Database\QueryException $qe) {
                        if ($attempts >= 5 || !str_contains($qe->getMessage(), '1062 Duplicate entry')) {
                            throw $qe;
                        }
                        usleep(50000); // 50ms pause before retrying next ID
                    }
                }
            }

            $mwhService->updatePositionStatus($pos->id);

            DB::commit();

            session()->flash('success', "Material {$this->new_item_code} ({$this->new_qty} KG, Tgl FIFO: {$arrivalDate}) berhasil ditambahkan ke slot {$pos->position_code}.");

            $this->reset(['new_item_code', 'new_item_description', 'new_qty', 'new_lot_no', 'new_supplier_name', 'new_po_number', 'new_created_at', 'newMaterialSearchResults', 'showAddMaterialForm']);
        } catch (\Illuminate\Database\QueryException $qe) {
            DB::rollBack();
            if (str_contains($qe->getMessage(), '1062 Duplicate entry')) {
                session()->flash('error', 'Terjadi duplikasi ID Pallet saat menyimpan. Silakan coba klik tombol Simpan sekali lagi.');
            } else {
                session()->flash('error', 'Terjadi kesalahan basis data saat menyimpan material ke slot.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menambahkan material ke slot: ' . $e->getMessage());
        }
    }

    public function deletePalletFromSlot($palletId, MaterialWarehouseService $mwhService): void
    {
        try {
            DB::beginTransaction();

            $pallet = MwhPallet::find($palletId);
            if ($pallet) {
                $posId = $pallet->position_id;
                $palletCode = $pallet->pallet_id;

                // Also delete related outgoing transaction history for this test pallet
                \App\Models\MwhOutgoing::where('pallet_id', $palletCode)->delete();
                $pallet->delete();

                if ($posId) {
                    $mwhService->updatePositionStatus($posId);
                }

                DB::commit();
                session()->flash('success', "Pallet {$palletCode} berhasil dihapus dari slot.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menghapus pallet dari slot: ' . $e->getMessage());
        }
    }

    public function saveSettings()
    {
        $pos = MwhPosition::find($this->selectedPositionId);
        if ($pos) {
            $this->validate([
                'editPositionCode' => [
                    'required',
                    'string',
                    Rule::unique('mwh_positions', 'position_code')->ignore($pos->id)->whereNull('deleted_at')
                ],
                'editSlotLabel' => 'nullable|string|max:255',
                'editMaxCapacity' => 'required|numeric|min:0',
                'editStatus' => 'required|in:EMPTY,PARTIAL,FULL',
                'editLastItemCode' => 'nullable|string|max:255',
            ]);

            $pos->update([
                'position_code' => strtoupper($this->editPositionCode),
                'slot_label' => $this->editSlotLabel,
                'max_capacity' => $this->editMaxCapacity,
                'status' => $this->editStatus,
                'last_item_code' => $this->editLastItemCode,
            ]);

            session()->flash('success', 'Pengaturan slot ' . $pos->position_code . ' berhasil disimpan.');
        }
    }

    public function resetSlot()
    {
        $pos = MwhPosition::find($this->selectedPositionId);
        if ($pos) {
            $pos->update([
                'status' => 'EMPTY',
                'last_item_code' => null
            ]);

            MwhPallet::where('position_id', $pos->id)
                ->where('current_qty', '>', 0)
                ->update(['status' => 'EMPTY', 'current_qty' => 0]);
            
            $this->editStatus = 'EMPTY';
            $this->editLastItemCode = null;
            session()->flash('success', 'Status slot ' . $pos->position_code . ' telah di-reset menjadi EMPTY.');
        }
    }

    public $newRackCode;
    public $newLevels = 2;
    public $newSlotsPerLevel = 4;
    public $newMaxCapacity = 1000;
    public $showAddRackModal = false;

    public function createNewRack()
    {
        $this->validate([
            'newRackCode' => ['required', Rule::unique('mwh_racks', 'rack_code')->whereNull('deleted_at')],
            'newLevels' => 'required|integer|min:1',
            'newSlotsPerLevel' => 'required|integer|min:1',
            'newMaxCapacity' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $whse = MwhWarehouse::firstOrCreate(
                ['whse_code' => 'MTR-01'],
                ['whse_name' => 'Gudang Material Utama']
            );

            $rack = MwhRack::create([
                'whse_id' => $whse->id,
                'rack_code' => strtoupper($this->newRackCode),
            ]);

            for ($l = 1; $l <= $this->newLevels; $l++) {
                for ($s = 1; $s <= $this->newSlotsPerLevel; $s++) {
                    $levelStr = str_pad($l, 2, '0', STR_PAD_LEFT);
                    $slotStr = str_pad($s, 2, '0', STR_PAD_LEFT);
                    
                    MwhPosition::create([
                        'rack_id' => $rack->id,
                        'level_no' => $l,
                        'slot_no' => $s,
                        'position_code' => strtoupper($rack->rack_code) . "-L{$levelStr}-S{$slotStr}",
                        'slot_label' => 'Slot ' . $l . '-' . $s,
                        'max_capacity' => $this->newMaxCapacity,
                    ]);
                }
            }

            DB::commit();
            session()->flash('success', 'Rak Material ' . $rack->rack_code . ' berhasil dibuat.');
            
            $this->reset(['newRackCode', 'showAddRackModal']);
            $this->newMaxCapacity = 1000;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal membuat rak material: ' . $e->getMessage());
        }
    }

    public function deleteRack($rackId)
    {
        try {
            DB::beginTransaction();

            $rack = MwhRack::find($rackId);
            if ($rack) {
                MwhPosition::where('rack_id', $rack->id)->delete();
                $rackCode = $rack->rack_code;
                $rack->delete();

                DB::commit();
                session()->flash('success', 'Rak Material ' . $rackCode . ' beserta seluruh slotnya berhasil dihapus.');
                
                $this->selectedPositionId = null;
                $this->showDetail = false;
            } else {
                DB::rollBack();
                session()->flash('error', 'Rak material tidak ditemukan.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menghapus rak material: ' . $e->getMessage());
        }
    }

    public function selectSearchSuggestion(string $itemCode): void
    {
        $this->selectedItemFilter = $itemCode;
        $this->searchTerm = $itemCode;
    }

    public function render()
    {
        $racks = MwhRack::with(['positions' => function($query) {
            $query->orderBy('level_no', 'desc')
                  ->orderBy('slot_no', 'asc')
                  ->with(['pallets' => function($pq) {
                      $pq->where('current_qty', '>', 0)->with('material');
                  }]);
        }])->get();

        $availableItemCodes = MwhPallet::query()
            ->where('current_qty', '>', 0)
            ->distinct()
            ->orderBy('item_code', 'asc')
            ->pluck('item_code')
            ->toArray();

        $matchingPositionIds = [];
        $queryStr = trim($this->searchTerm);
        $itemFilter = trim($this->selectedItemFilter);

        if (strlen($queryStr) > 0 || strlen($itemFilter) > 0) {
            $matchingPositionIds = MwhPosition::query()
                ->where(function($q) use ($queryStr, $itemFilter) {
                    if (strlen($itemFilter) > 0) {
                        $q->where('last_item_code', $itemFilter)
                          ->orWhereHas('pallets', function($pq) use ($itemFilter) {
                              $pq->where('current_qty', '>', 0)->where('item_code', $itemFilter);
                          });
                    }

                    if (strlen($queryStr) > 0) {
                        $q->where(function($sub) use ($queryStr) {
                            $sub->where('position_code', 'like', '%' . $queryStr . '%')
                              ->orWhere('slot_label', 'like', '%' . $queryStr . '%')
                              ->orWhere('last_item_code', 'like', '%' . $queryStr . '%')
                              ->orWhereHas('rack', function($rq) use ($queryStr) {
                                  $rq->where('rack_code', 'like', '%' . $queryStr . '%');
                              })
                              ->orWhereHas('pallets', function($pq) use ($queryStr) {
                                  $pq->where('current_qty', '>', 0)
                                     ->where(function($subQ) use ($queryStr) {
                                         $subQ->where('pallet_id', 'like', '%' . $queryStr . '%')
                                              ->orWhere('item_code', 'like', '%' . $queryStr . '%')
                                              ->orWhere('lot_no', 'like', '%' . $queryStr . '%')
                                              ->orWhereHas('material', function($mq) use ($queryStr) {
                                                  $mq->where('item_description', 'like', '%' . $queryStr . '%');
                                              });
                                     });
                              });
                        });
                    }
                })
                ->pluck('id')
                ->toArray();
        }

        // Live Search Suggestions for Autocomplete Dropdown
        $searchSuggestions = [];
        if (strlen($queryStr) >= 2) {
            $st = '%' . $queryStr . '%';
            $searchSuggestions = MwhPallet::with('material')
                ->where('current_qty', '>', 0)
                ->where(function($q) use ($st) {
                    $q->where('item_code', 'like', $st)
                      ->orWhere('pallet_id', 'like', $st)
                      ->orWhere('lot_no', 'like', $st)
                      ->orWhereHas('material', function($mq) use ($st) {
                          $mq->where('item_description', 'like', $st);
                      });
                })
                ->limit(10)
                ->get()
                ->map(function($p) {
                    return [
                        'item_code'        => $p->item_code,
                        'item_description' => $p->material ? $p->material->item_description : '-',
                        'pallet_id'        => $p->pallet_id,
                    ];
                })
                ->unique('item_code')
                ->values()
                ->toArray();
        }

        // Special Active Item FIFO Summary Banner (Appears right above slot status legend bar when item is selected/searched)
        $targetItemCode = $itemFilter ?: $queryStr;
        $activeItemSummary = null;

        if (strlen($targetItemCode) > 0) {
            $itemPallets = MwhPallet::with(['position.rack', 'material', 'incomingHeader'])
                ->where('current_qty', '>', 0)
                ->where(function($q) use ($targetItemCode) {
                    $q->where('item_code', $targetItemCode)
                      ->orWhere('item_code', 'like', '%' . $targetItemCode . '%');
                })
                ->orderBy('created_at', 'asc')
                ->get();

            if ($itemPallets->isNotEmpty()) {
                $matchedItemCode = $itemPallets->first()->item_code;
                $exactPallets = $itemPallets->where('item_code', $matchedItemCode)->values();
                $firstPallet = $exactPallets->first() ?? $itemPallets->first();

                $activeItemSummary = [
                    'item_code'        => $matchedItemCode,
                    'item_description' => $firstPallet->material ? $firstPallet->material->item_description : '-',
                    'total_qty'        => $exactPallets->sum('current_qty'),
                    'pallet_count'     => $exactPallets->count(),
                    'oldest_date'      => $firstPallet->created_at,
                    'pallets'          => $exactPallets,
                ];
            }
        }

        $selectedPosData = $this->selectedPositionId 
            ? MwhPosition::with([
                'rack', 
                'pallets' => function($q) {
                    $q->where('current_qty', '>', 0)
                      ->with(['material', 'incomingHeader']);
                }
            ])->find($this->selectedPositionId) 
            : null;

        $fifoSummaryData = [];
        if ($this->showFifoSummaryModal) {
            $groupedPallets = MwhPallet::with(['position.rack', 'material', 'incomingHeader'])
                ->where('current_qty', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get()
                ->groupBy('item_code');

            foreach ($groupedPallets as $itemCode => $pallets) {
                $firstPallet = $pallets->first();
                $palletIds = $pallets->pluck('pallet_id')->toArray();

                $outgoings = \App\Models\MwhOutgoing::with(['pallet', 'position'])
                    ->whereIn('pallet_id', $palletIds)
                    ->orderBy('created_at', 'desc')
                    ->get();

                $fifoSummaryData[] = [
                    'item_code'        => $itemCode,
                    'item_description' => $firstPallet->material ? $firstPallet->material->item_description : '-',
                    'total_qty'        => $pallets->sum('current_qty'),
                    'pallet_count'     => $pallets->count(),
                    'oldest_date'      => $firstPallet->created_at,
                    'pallets'          => $pallets,
                    'outgoings'        => $outgoings,
                ];
            }

            usort($fifoSummaryData, function($a, $b) {
                return strcmp($a['item_code'], $b['item_code']);
            });
        }

        return view('livewire.material-warehouse.rack-mapping', [
            'racks'               => $racks,
            'selectedPosData'     => $selectedPosData,
            'matchingPositionIds' => $matchingPositionIds,
            'availableItemCodes'  => $availableItemCodes,
            'searchSuggestions'   => $searchSuggestions,
            'activeItemSummary'   => $activeItemSummary,
            'fifoSummaryData'     => $fifoSummaryData,
        ]);
    }
}
