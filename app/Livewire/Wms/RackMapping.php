<?php

namespace App\Livewire\Wms;

use App\Models\WmsRack;
use App\Models\WmsPosition;
use App\Services\WmsService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RackMapping extends Component
{
    // Slot Detail & Edit State
    public $selectedPositionId;
    public $editMaxCapacity;
    public $editCustomerCode;
    
    // Filtering & Search State
    public $filterCustomer = '';
    public $searchItem = '';
    protected $queryString = ['filterCustomer', 'searchItem'];
    
    // UI State
    public $showDetail = false;

    // Add Rack Modal State
    public $newRackCode;
    public $newLevels = 2;
    public $newSlotsPerLevel = 4;
    public $newRackCustomer;
    public $newMaxCapacity = 1;
    public $showAddRackModal = false;

    // Warehouse Edit State
    public $whseId = 1;
    public $whseName = '';
    public $whseCode = '';
    public $showEditWarehouseModal = false;

    // Edit Rack & Dimension State
    public $editingRackId = null;
    public $editRackCode = '';
    public $editRackLevels = 1;
    public $editRackSlotsPerLevel = 1;
    public $editRackCustomer = '';
    public $editRackMaxCapacity = 1;
    public $showEditRackModal = false;

    public function selectPosition($id)
    {
        $this->selectedPositionId = $id;
        $pos = WmsPosition::find($id);
        if ($pos) {
            $this->editMaxCapacity = $pos->max_capacity;
            $this->editCustomerCode = $pos->customer_code;
            $this->showDetail = true;
        }
    }

    public function saveSettings(?WmsService $wmsService = null)
    {
        $wmsService = $wmsService ?? app(WmsService::class);
        $pos = WmsPosition::find($this->selectedPositionId);
        if ($pos) {
            $pos->update([
                'max_capacity' => $this->editMaxCapacity,
                'customer_code' => $this->editCustomerCode,
            ]);

            // Recalculate status (EMPTY/PARTIAL/FULL)
            $wmsService->updatePositionStatus($pos->id);

            session()->flash('success', 'Pengaturan slot ' . $pos->position_code . ' berhasil disimpan.');
            $this->showDetail = false;
        }
    }

    public function resetSlot(?WmsService $wmsService = null)
    {
        $wmsService = $wmsService ?? app(WmsService::class);
        $pos = WmsPosition::find($this->selectedPositionId);
        if ($pos) {
            try {
                DB::beginTransaction();

                // Detach/unassign any pallet forms currently in this slot
                $pallets = \App\Models\WmsPalletForm::where('position_id', $pos->id)->get();
                foreach ($pallets as $pallet) {
                    $pallet->update([
                        'position_id' => null,
                        'assigned_at' => null,
                    ]);
                    $wmsService->logTransaction($pallet->pallet_id, 'UNASSIGN_SLOT', null, "Unassigned by Store via Slot Reset ({$pos->position_code})");
                }

                $pos->update([
                    'status' => 'EMPTY',
                    'last_item_code' => null,
                ]);

                $wmsService->updatePositionStatus($pos->id);

                DB::commit();

                $this->showDetail = false;
                session()->flash('success', 'Status slot ' . $pos->position_code . ' telah di-reset menjadi EMPTY dan semua pallet telah dilepas.');
            } catch (\Exception $e) {
                DB::rollBack();
                session()->flash('error', 'Gagal me-reset slot: ' . $e->getMessage());
            }
        }
    }

    public function deletePallet($palletId, ?WmsService $wmsService = null)
    {
        $wmsService = $wmsService ?? app(WmsService::class);
        try {
            DB::beginTransaction();

            $pallet = \App\Models\WmsPalletForm::where('pallet_id', $palletId)->firstOrFail();
            $positionId = $pallet->position_id;

            // Delete all details
            $pallet->details()->delete();

            // Delete pallet header
            $pallet->delete();

            // Log transaction
            $wmsService->logTransaction($palletId, 'DELETE_PALLET', $positionId, "Pallet deleted from Rack Mapping by Store");

            // Recalculate position status if it was assigned to a slot
            if ($positionId) {
                $wmsService->updatePositionStatus($positionId);
            }

            DB::commit();

            session()->flash('success', "Pallet {$palletId} berhasil dihapus.");

            // Refresh selected position if slot detail is open
            if ($this->selectedPositionId) {
                $this->selectPosition($this->selectedPositionId);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Gagal menghapus pallet: " . $e->getMessage());
        }
    }

    public function deletePalletFromSlot($palletId, ?WmsService $wmsService = null)
    {
        $this->deletePallet($palletId, $wmsService);
    }

    public function unassignPalletFromSlot($palletId, ?WmsService $wmsService = null)
    {
        $wmsService = $wmsService ?? app(WmsService::class);
        try {
            DB::beginTransaction();

            $pallet = \App\Models\WmsPalletForm::where('pallet_id', $palletId)->firstOrFail();
            $oldPosId = $pallet->position_id;

            $pallet->update([
                'position_id' => null,
                'assigned_at' => null,
            ]);

            if ($oldPosId) {
                $wmsService->updatePositionStatus($oldPosId);
            }

            $wmsService->logTransaction($pallet->pallet_id, 'UNASSIGN_SLOT', null, "Unassigned from Rack Mapping by Store");

            DB::commit();

            session()->flash('success', "Pallet {$pallet->pallet_id} berhasil dilepas dari slot.");

            // Refresh selected position if slot detail is open
            if ($this->selectedPositionId) {
                $this->selectPosition($this->selectedPositionId);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Gagal melepas pallet: " . $e->getMessage());
        }
    }

    protected function generatePositionCode(WmsRack $rack, int $levelNo, int $slotNo): string
    {
        $samplePos = $rack->positions->first();
        $whseCode = $rack->warehouse?->whse_code ?? $this->whseCode ?? 'J06';

        if ($samplePos && !empty($samplePos->position_code)) {
            // Pattern: WHSE-CUST-RACK-L1S1
            if (preg_match('/^[A-Za-z0-9]+-([A-Za-z0-9]+)-[A-Za-z0-9]+-L\d+S\d+$/i', $samplePos->position_code, $matches)) {
                $custCode = $matches[1];
                return "{$whseCode}-{$custCode}-{$rack->rack_code}-L{$levelNo}S{$slotNo}";
            }
            // Pattern: WHSE-CUST-RACK-L01-S01
            if (preg_match('/^[A-Za-z0-9]+-([A-Za-z0-9]+)-[A-Za-z0-9]+-L\d+-S\d+$/i', $samplePos->position_code, $matches)) {
                $custCode = $matches[1];
                $lStr = str_pad($levelNo, 2, '0', STR_PAD_LEFT);
                $sStr = str_pad($slotNo, 2, '0', STR_PAD_LEFT);
                return "{$whseCode}-{$custCode}-{$rack->rack_code}-L{$lStr}-S{$sStr}";
            }
        }

        $levelStr = str_pad($levelNo, 2, '0', STR_PAD_LEFT);
        $slotStr = str_pad($slotNo, 2, '0', STR_PAD_LEFT);
        return strtoupper($rack->rack_code) . "-L{$levelStr}-S{$slotStr}";
    }

    protected function upsertPosition(WmsRack $rack, int $levelNo, int $slotNo, string $posCode, int $maxCapacity = 1, ?string $customerCode = null): WmsPosition
    {
        $existing = WmsPosition::withTrashed()->where('position_code', $posCode)->first();
        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->update([
                'rack_id' => $rack->id,
                'level_no' => $levelNo,
                'slot_no' => $slotNo,
                'position_code' => $posCode,
                'max_capacity' => $maxCapacity,
                'customer_code' => $customerCode,
                'status' => $existing->palletForms()->where('total_pallet_qty', '>', 0)->exists() ? $existing->status : 'EMPTY',
            ]);
            return $existing;
        }

        return WmsPosition::create([
            'rack_id' => $rack->id,
            'level_no' => $levelNo,
            'slot_no' => $slotNo,
            'position_code' => $posCode,
            'max_capacity' => $maxCapacity,
            'customer_code' => $customerCode,
            'status' => 'EMPTY',
        ]);
    }

    public function createNewRack(?WmsService $wmsService = null)
    {
        $wmsService = $wmsService ?? app(WmsService::class);
        $this->validate([
            'newRackCode' => ['required', Rule::unique('wms_racks', 'rack_code')->whereNull('deleted_at')],
            'newLevels' => 'required|integer|min:1',
            'newSlotsPerLevel' => 'required|integer|min:1',
            'newRackCustomer' => 'nullable|string',
            'newMaxCapacity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $rack = WmsRack::create([
                'whse_id' => $this->whseId ?: 1,
                'rack_code' => strtoupper($this->newRackCode),
            ]);

            $cust = !empty($this->newRackCustomer) ? strtoupper($this->newRackCustomer) : null;

            // Simple Batch generation
            for ($l = 1; $l <= $this->newLevels; $l++) {
                for ($s = 1; $s <= $this->newSlotsPerLevel; $s++) {
                    $posCode = $this->generatePositionCode($rack, $l, $s);
                    $this->upsertPosition($rack, $l, $s, $posCode, $this->newMaxCapacity, $cust);
                }
            }

            DB::commit();
            session()->flash('success', 'Rak ' . $rack->rack_code . ' berhasil dibuat (Kapasitas: ' . $this->newMaxCapacity . ').');
            
            $this->reset(['newRackCode', 'newRackCustomer', 'newMaxCapacity', 'showAddRackModal']);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal membuat rak: ' . $e->getMessage());
        }
    }

    public function deleteRack($rackId)
    {
        try {
            DB::beginTransaction();

            $rack = WmsRack::find($rackId);
            if ($rack) {
                $positionIds = WmsPosition::withTrashed()->where('rack_id', $rack->id)->pluck('id');

                // Detach from Pallet Forms and reset status so they are not lost
                \App\Models\WmsPalletForm::whereIn('position_id', $positionIds)->update([
                    'position_id' => null,
                    'status' => 'GENERATED'
                ]);

                // Detach from Logs to preserve history without breaking foreign key
                \App\Models\WmsPalletLog::whereIn('position_id', $positionIds)->update([
                    'position_id' => null
                ]);

                // Force delete all positions under this rack to keep DB clean
                WmsPosition::withTrashed()->whereIn('id', $positionIds)->forceDelete();
                
                $rackCode = $rack->rack_code;
                $rack->delete();

                DB::commit();
                session()->flash('success', 'Rak ' . $rackCode . ' beserta seluruh slotnya berhasil dihapus.');
                
                // Reset selected slot if it belongs to the deleted rack
                $this->selectedPositionId = null;
                $this->showDetail = false;
            } else {
                DB::rollBack();
                session()->flash('error', 'Rak tidak ditemukan.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menghapus rak: ' . $e->getMessage());
        }
    }

    public function mount()
    {
        $warehouse = \App\Models\WmsWarehouse::first() ?? \App\Models\WmsWarehouse::create([
            'whse_code' => 'J06',
            'whse_name' => 'Monitoring Hunian Rak Gudang J06 (Highly Marelli)',
        ]);
        $this->whseId = $warehouse->id;
        $this->whseName = $warehouse->whse_name;
        $this->whseCode = $warehouse->whse_code;
    }

    public function openEditWarehouseModal()
    {
        $warehouse = \App\Models\WmsWarehouse::find($this->whseId) ?? \App\Models\WmsWarehouse::first();
        if ($warehouse) {
            $this->whseId = $warehouse->id;
            $this->whseName = $warehouse->whse_name;
            $this->whseCode = $warehouse->whse_code;
        }
        $this->showEditWarehouseModal = true;
    }

    public function saveWarehouse()
    {
        $this->validate([
            'whseName' => 'required|string|max:255',
            'whseCode' => 'required|string|max:50',
        ]);

        $warehouse = \App\Models\WmsWarehouse::find($this->whseId) ?? \App\Models\WmsWarehouse::first();
        if (!$warehouse) {
            $warehouse = \App\Models\WmsWarehouse::create([
                'whse_code' => strtoupper($this->whseCode),
                'whse_name' => $this->whseName,
            ]);
        } else {
            $warehouse->update([
                'whse_name' => $this->whseName,
                'whse_code' => strtoupper($this->whseCode),
            ]);
        }

        $this->whseId = $warehouse->id;
        $this->whseName = $warehouse->whse_name;
        $this->whseCode = $warehouse->whse_code;

        session()->flash('success', 'Informasi Gudang berhasil diperbarui.');
        $this->showEditWarehouseModal = false;
    }

    public function openEditRackModal($rackId)
    {
        $rack = WmsRack::with('positions')->find($rackId);
        if (!$rack) {
            session()->flash('error', 'Rak tidak ditemukan.');
            return;
        }

        $this->editingRackId = $rack->id;
        $this->editRackCode = $rack->rack_code;

        $maxLevel = $rack->positions->max('level_no') ?? 1;
        $maxSlots = $rack->positions->max('slot_no') ?? 1;
        $firstPos = $rack->positions->first();

        $this->editRackLevels = max(1, $maxLevel);
        $this->editRackSlotsPerLevel = max(1, $maxSlots);
        $this->editRackCustomer = $firstPos?->customer_code ?? '';
        $this->editRackMaxCapacity = $firstPos?->max_capacity ?? 1;

        $this->showEditRackModal = true;
    }

    public function saveRackChanges(?WmsService $wmsService = null)
    {
        $wmsService = $wmsService ?? app(WmsService::class);
        $this->validate([
            'editRackCode' => [
                'required',
                'string',
                Rule::unique('wms_racks', 'rack_code')->ignore($this->editingRackId)->whereNull('deleted_at')
            ],
            'editRackLevels' => 'required|integer|min:1|max:20',
            'editRackSlotsPerLevel' => 'required|integer|min:1|max:50',
            'editRackCustomer' => 'nullable|string',
            'editRackMaxCapacity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $rack = WmsRack::with('positions')->findOrFail($this->editingRackId);
            $newRackCode = strtoupper(trim($this->editRackCode));
            $rack->update(['rack_code' => $newRackCode]);

            $existingPositions = WmsPosition::where('rack_id', $rack->id)->get();
            $existingMap = $existingPositions->keyBy(fn($p) => "{$p->level_no}-{$p->slot_no}");

            // Check if any positions to be deleted contain active pallets
            $positionsToDelete = [];
            foreach ($existingPositions as $pos) {
                if ($pos->level_no > $this->editRackLevels || $pos->slot_no > $this->editRackSlotsPerLevel) {
                    $hasActivePallet = \App\Models\WmsPalletForm::where('position_id', $pos->id)
                        ->where('total_pallet_qty', '>', 0)
                        ->exists();

                    if ($hasActivePallet) {
                        DB::rollBack();
                        session()->flash('error', "Gagal memperkecil rak: Slot {$pos->position_code} masih berisi pallet aktif. Pindahkan pallet terlebih dahulu.");
                        return;
                    }

                    $positionsToDelete[] = $pos;
                }
            }

            // Force delete excess empty positions
            foreach ($positionsToDelete as $pos) {
                \App\Models\WmsPalletForm::where('position_id', $pos->id)->update(['position_id' => null]);
                \App\Models\WmsPalletLog::where('position_id', $pos->id)->update(['position_id' => null]);
                $pos->forceDelete();
            }

            $cust = !empty($this->editRackCustomer) ? strtoupper($this->editRackCustomer) : null;

            // Create new positions or update existing ones
            for ($l = 1; $l <= $this->editRackLevels; $l++) {
                for ($s = 1; $s <= $this->editRackSlotsPerLevel; $s++) {
                    $posCode = $this->generatePositionCode($rack, $l, $s);

                    $key = "{$l}-{$s}";
                    if ($existingMap->has($key)) {
                        $pos = $existingMap->get($key);
                        $pos->update([
                            'position_code' => $posCode,
                            'max_capacity' => $this->editRackMaxCapacity,
                            'customer_code' => $cust,
                        ]);
                    } else {
                        $this->upsertPosition($rack, $l, $s, $posCode, $this->editRackMaxCapacity, $cust);
                    }
                }
            }

            DB::commit();
            session()->flash('success', "Rak {$newRackCode} dan seluruh slotnya berhasil diperbarui.");
            $this->showEditRackModal = false;

            if ($this->selectedPositionId) {
                $this->selectPosition($this->selectedPositionId);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Gagal menyimpan perubahan rak: " . $e->getMessage());
        }
    }

    public function addLevelToRack($rackId)
    {
        try {
            DB::beginTransaction();
            $rack = WmsRack::with('positions')->findOrFail($rackId);
            $maxLevel = $rack->positions->max('level_no') ?? 0;
            $newLevel = $maxLevel + 1;
            $maxSlots = $rack->positions->max('slot_no') ?? 1;
            $samplePos = $rack->positions->first();

            for ($s = 1; $s <= $maxSlots; $s++) {
                $posCode = $this->generatePositionCode($rack, $newLevel, $s);
                $this->upsertPosition(
                    $rack,
                    $newLevel,
                    $s,
                    $posCode,
                    $samplePos?->max_capacity ?? 1,
                    $samplePos?->customer_code
                );
            }

            DB::commit();
            session()->flash('success', "Berhasil menambahkan Level {$newLevel} pada Rak {$rack->rack_code}.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Gagal menambah level: " . $e->getMessage());
        }
    }

    public function removeLevelFromRack($rackId)
    {
        try {
            DB::beginTransaction();
            $rack = WmsRack::with('positions')->findOrFail($rackId);
            $maxLevel = $rack->positions->max('level_no');
            if ($maxLevel <= 1) {
                session()->flash('error', "Rak {$rack->rack_code} harus memiliki minimal 1 level.");
                return;
            }

            $positionsOnMaxLevel = WmsPosition::where('rack_id', $rack->id)->where('level_no', $maxLevel)->get();
            foreach ($positionsOnMaxLevel as $pos) {
                $hasActivePallet = \App\Models\WmsPalletForm::where('position_id', $pos->id)->where('total_pallet_qty', '>', 0)->exists();
                if ($hasActivePallet) {
                    DB::rollBack();
                    session()->flash('error', "Tidak dapat menghapus Level {$maxLevel} karena slot {$pos->position_code} masih berisi pallet aktif.");
                    return;
                }
            }

            foreach ($positionsOnMaxLevel as $pos) {
                \App\Models\WmsPalletForm::where('position_id', $pos->id)->update(['position_id' => null]);
                \App\Models\WmsPalletLog::where('position_id', $pos->id)->update(['position_id' => null]);
                $pos->forceDelete();
            }

            DB::commit();
            session()->flash('success', "Level {$maxLevel} pada Rak {$rack->rack_code} berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Gagal menghapus level: " . $e->getMessage());
        }
    }

    public function addSlotToRack($rackId)
    {
        try {
            DB::beginTransaction();
            $rack = WmsRack::with('positions')->findOrFail($rackId);
            $levels = $rack->positions->pluck('level_no')->unique()->values();
            if ($levels->isEmpty()) {
                $levels = collect([1]);
            }
            $maxSlot = $rack->positions->max('slot_no') ?? 0;
            $newSlot = $maxSlot + 1;
            $samplePos = $rack->positions->first();

            foreach ($levels as $l) {
                $posCode = $this->generatePositionCode($rack, $l, $newSlot);
                $this->upsertPosition(
                    $rack,
                    $l,
                    $newSlot,
                    $posCode,
                    $samplePos?->max_capacity ?? 1,
                    $samplePos?->customer_code
                );
            }

            DB::commit();
            session()->flash('success', "Berhasil menambahkan Slot S{$newSlot} ke semua level pada Rak {$rack->rack_code}.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Gagal menambah slot: " . $e->getMessage());
        }
    }

    public function removeSlotFromRack($rackId)
    {
        try {
            DB::beginTransaction();
            $rack = WmsRack::with('positions')->findOrFail($rackId);
            $maxSlot = $rack->positions->max('slot_no');
            if ($maxSlot <= 1) {
                session()->flash('error', "Rak {$rack->rack_code} harus memiliki minimal 1 slot per level.");
                return;
            }

            $positionsOnMaxSlot = WmsPosition::where('rack_id', $rack->id)->where('slot_no', $maxSlot)->get();
            foreach ($positionsOnMaxSlot as $pos) {
                $hasActivePallet = \App\Models\WmsPalletForm::where('position_id', $pos->id)->where('total_pallet_qty', '>', 0)->exists();
                if ($hasActivePallet) {
                    DB::rollBack();
                    session()->flash('error', "Tidak dapat menghapus Slot S{$maxSlot} karena slot {$pos->position_code} masih berisi pallet aktif.");
                    return;
                }
            }

            foreach ($positionsOnMaxSlot as $pos) {
                \App\Models\WmsPalletForm::where('position_id', $pos->id)->update(['position_id' => null]);
                \App\Models\WmsPalletLog::where('position_id', $pos->id)->update(['position_id' => null]);
                $pos->forceDelete();
            }

            DB::commit();
            session()->flash('success', "Slot S{$maxSlot} pada Rak {$rack->rack_code} berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Gagal menghapus slot: " . $e->getMessage());
        }
    }

    public function deletePositionSlot($positionId)
    {
        try {
            DB::beginTransaction();
            $pos = WmsPosition::findOrFail($positionId);
            $hasActivePallet = \App\Models\WmsPalletForm::where('position_id', $pos->id)->where('total_pallet_qty', '>', 0)->exists();
            if ($hasActivePallet) {
                session()->flash('error', "Slot {$pos->position_code} masih berisi pallet aktif. Kosongkan atau pindahkan pallet terlebih dahulu.");
                return;
            }

            $posCode = $pos->position_code;
            \App\Models\WmsPalletForm::where('position_id', $pos->id)->update(['position_id' => null]);
            \App\Models\WmsPalletLog::where('position_id', $pos->id)->update(['position_id' => null]);
            $pos->forceDelete();

            DB::commit();
            $this->selectedPositionId = null;
            $this->showDetail = false;
            session()->flash('success', "Slot {$posCode} berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "Gagal menghapus slot: " . $e->getMessage());
        }
    }

    public function render()
    {
        $hasCustomerTable = \Illuminate\Support\Facades\Schema::hasTable('master_customer_delivery');

        $racks = WmsRack::with(['positions' => function($query) use ($hasCustomerTable) {
            if ($hasCustomerTable) {
                $query->with('customer');
            }
            $query->with(['palletForms' => function($q) {
                $q->with('details');
            }])
            ->withCount('palletForms')
            ->orderBy('level_no', 'desc')
            ->orderBy('slot_no', 'asc');
        }])->get();

        $matchingPositionIds = [];
        $searchTerm = trim($this->searchItem);
        if (!empty($searchTerm)) {
            $term = '%' . $searchTerm . '%';

            // 1. Position code or last_item_code
            $posIds1 = WmsPosition::where('position_code', 'like', $term)
                ->orWhere('last_item_code', 'like', $term)
                ->pluck('id');

            // 2. Pallet forms header fields (pallet_id, part_no, model_name, lot_no) with qty > 0
            $posIds2 = \App\Models\WmsPalletForm::whereNotNull('position_id')
                ->where('total_pallet_qty', '>', 0)
                ->where(function($q) use ($term) {
                    $q->where('pallet_id', 'like', $term)
                      ->orWhere('part_no', 'like', $term)
                      ->orWhere('model_name', 'like', $term)
                      ->orWhere('lot_no', 'like', $term);
                })
                ->pluck('position_id');

            // 3. Pallet form details (item box level inside mixed pallets) with qty > 0
            $posIds3 = \App\Models\WmsPalletForm::whereNotNull('position_id')
                ->whereHas('details', function($q) use ($term) {
                    $q->where('qty', '>', 0)
                      ->where(function($dq) use ($term) {
                          $dq->where('part_no', 'like', $term)
                             ->orWhere('model_name', 'like', $term)
                             ->orWhere('spk_no', 'like', $term)
                             ->orWhere('label', 'like', $term);
                      });
                })
                ->pluck('position_id');

            $matchingPositionIds = $posIds1->merge($posIds2)->merge($posIds3)->unique()->filter()->values()->toArray();
        }

        // Live autocomplete suggestions querying WmsPalletFormDetail directly for items with QTY > 0
        $searchSuggestions = [];
        if (strlen($searchTerm) >= 1) {
            $term = '%' . $searchTerm . '%';

            $detailsQuery = \App\Models\WmsPalletFormDetail::whereHas('header', function($q) {
                $q->whereNotNull('position_id');
            })
            ->where('qty', '>', 0)
            ->where(function($q) use ($term) {
                $q->where('part_no', 'like', $term)
                  ->orWhere('model_name', 'like', $term)
                  ->orWhere('spk_no', 'like', $term)
                  ->orWhere('label', 'like', $term);
            })
            ->with(['header.position'])
            ->get();

            $searchSuggestions = $detailsQuery->groupBy('part_no')
                ->map(function($group, $partNo) {
                    $first = $group->first();
                    $totalQty = $group->sum('qty');
                    $positions = $group->map(fn($d) => $d->header?->position?->position_code)->filter()->unique()->values()->all();

                    return [
                        'part_no'      => $partNo ?: $first->part_no,
                        'model_name'   => $first->model_name ?: 'No Model Name',
                        'total_qty'    => $totalQty,
                        'pallet_count' => count($positions),
                        'positions'    => implode(', ', array_slice($positions, 0, 3)) . (count($positions) > 3 ? '...' : ''),
                    ];
                })
                ->values()
                ->take(8)
                ->toArray();
        }

        $selectedPosRelations = ['palletForms.details'];
        if ($hasCustomerTable) {
            $selectedPosRelations[] = 'customer';
        }

        $selectedPosData = $this->selectedPositionId 
            ? WmsPosition::with($selectedPosRelations)->withCount('palletForms')->find($this->selectedPositionId) 
            : null;

        $customers = $hasCustomerTable
            ? \App\Models\MasterCustomerDelivery::orderBy('customer_code')->get()
            : collect();

        $unassignedPallets = \App\Models\WmsPalletForm::whereNull('position_id')
            ->where('total_pallet_qty', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        $warehouse = \App\Models\WmsWarehouse::find($this->whseId) ?? \App\Models\WmsWarehouse::first();

        return view('livewire.wms.rack-mapping', [
            'warehouse'           => $warehouse,
            'racks'               => $racks,
            'selectedPosData'     => $selectedPosData,
            'customers'           => $customers,
            'unassignedPallets'   => $unassignedPallets,
            'matchingPositionIds' => $matchingPositionIds,
            'searchSuggestions'   => $searchSuggestions,
        ]);
    }

    public function selectSearchSuggestion($itemCode)
    {
        $this->searchItem = $itemCode;
    }

    public function assignPalletToSelectedSlot($palletId, ?WmsService $wmsService = null)
    {
        $wmsService = $wmsService ?? app(WmsService::class);
        if (! $this->selectedPositionId) {
            session()->flash('error', 'Pilih slot rak terlebih dahulu.');
            return;
        }

        try {
            $pos = WmsPosition::find($this->selectedPositionId);
            $pallet = \App\Models\WmsPalletForm::where('pallet_id', $palletId)->firstOrFail();

            if ($pallet->total_pallet_qty <= 0) {
                session()->flash('error', "Pallet {$pallet->pallet_id} sudah habis (quantity 0) dan tidak perlu di-assign ke slot rak.");
                return;
            }

            $oldPosId = $pallet->position_id;
            $pallet->update([
                'position_id' => $pos->id,
                'assigned_at' => $pallet->assigned_at ?? now(),
            ]);

            if ($oldPosId) {
                $wmsService->updatePositionStatus($oldPosId);
            }
            $wmsService->updatePositionStatus($pos->id);

            $wmsService->logTransaction($pallet->pallet_id, 'ASSIGN_SLOT', $pos->id, "Assigned by Store from Rack Mapping");

            session()->flash('success', "Pallet {$pallet->pallet_id} berhasil di-assign ke slot rak {$pos->position_code}.");
        } catch (\Exception $e) {
            session()->flash('error', "Gagal assign pallet: " . $e->getMessage());
        }
    }
}
