<?php

namespace App\Livewire\MaterialWarehouse;

use App\Models\MwhWarehouse;
use App\Models\MwhRack;
use App\Models\MwhPosition;
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
    
    // UI State
    public $showDetail = false;

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
            $this->showDetail = false;
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
            
            $this->editStatus = 'EMPTY';
            $this->editLastItemCode = null;
            $this->showDetail = false;
            session()->flash('success', 'Status slot ' . $pos->position_code . ' telah di-reset menjadi EMPTY.');
        }
    }

    public $newRackCode;
    public $newLevels = 2;
    public $newSlotsPerLevel = 4;
    public $newMaxCapacity = 1;
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

            // Default warehouse for Material, create one if none exists
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
            
            $this->reset(['newRackCode', 'newMaxCapacity', 'showAddRackModal']);
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
                  ->orderBy('slot_no', 'asc');
        }])->get();

        $selectedPosData = $this->selectedPositionId 
            ? MwhPosition::find($this->selectedPositionId) 
            : null;

        return view('livewire.material-warehouse.rack-mapping', [
            'racks' => $racks,
            'selectedPosData' => $selectedPosData,
        ]);
    }
}
