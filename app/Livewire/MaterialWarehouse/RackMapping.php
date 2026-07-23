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
    public array $newMaterialSearchResults = [];

    // UI State
    public $showDetail = false;
    public string $searchTerm = '';

    protected $queryString = ['searchTerm' => ['except' => '']];

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
            $this->reset(['new_item_code', 'new_item_description', 'new_qty', 'new_lot_no', 'new_supplier_name', 'new_po_number', 'newMaterialSearchResults']);
        }
    }

    public function storeMaterialToSlot(MaterialWarehouseService $mwhService): void
    {
        $this->validate([
            'selectedPositionId' => 'required|exists:mwh_positions,id',
            'new_item_code'      => 'required|string|exists:master_list_materials,item_code',
            'new_qty'            => 'required|numeric|min:0.01',
            'new_lot_no'         => 'nullable|string|max:255',
            'new_supplier_name'  => 'nullable|string|max:255',
            'new_po_number'      => 'nullable|string|max:255',
        ], [
            'new_item_code.required' => 'Part Code material harus diisi.',
            'new_item_code.exists'   => 'Part Code material tidak ada di Master List.',
            'new_qty.required'       => 'Jumlah (KG) harus diisi.',
        ]);

        $pos = MwhPosition::find($this->selectedPositionId);
        if (!$pos) return;

        try {
            DB::beginTransaction();

            $header = MwhIncomingHeader::create([
                'document_no'   => $mwhService->generateDocumentNo(),
                'supplier_name' => trim($this->new_supplier_name) ?: null,
                'po_number'     => trim($this->new_po_number) ?: null,
                'arrival_date'  => now()->format('Y-m-d'),
                'remarks'       => 'Direct input via Material Warehouse Mapping',
            ]);

            $remainingToSplit = (float) $this->new_qty;

            while ($remainingToSplit > 0) {
                $palletQty = min(1000.0, $remainingToSplit);
                $remainingToSplit -= $palletQty;

                $palletId = $mwhService->generatePalletId();

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
                ]);
            }

            $mwhService->updatePositionStatus($pos->id);

            DB::commit();

            session()->flash('success', "Material {$this->new_item_code} ({$this->new_qty} KG) berhasil ditambahkan ke slot {$pos->position_code}.");

            $this->reset(['new_item_code', 'new_item_description', 'new_qty', 'new_lot_no', 'new_supplier_name', 'new_po_number', 'newMaterialSearchResults', 'showAddMaterialForm']);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menambahkan material ke slot: ' . $e->getMessage());
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

    public function render()
    {
        $racks = MwhRack::with(['positions' => function($query) {
            $query->orderBy('level_no', 'desc')
                  ->orderBy('slot_no', 'asc')
                  ->with(['pallets' => function($pq) {
                      $pq->where('current_qty', '>', 0)->with('material');
                  }]);
        }])->get();

        $matchingPositionIds = [];
        $queryStr = trim($this->searchTerm);

        if (strlen($queryStr) > 0) {
            $matchingPositionIds = MwhPosition::query()
                ->where(function($q) use ($queryStr) {
                    $q->where('position_code', 'like', '%' . $queryStr . '%')
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
                })
                ->pluck('id')
                ->toArray();
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

        return view('livewire.material-warehouse.rack-mapping', [
            'racks' => $racks,
            'selectedPosData' => $selectedPosData,
            'matchingPositionIds' => $matchingPositionIds,
        ]);
    }
}
