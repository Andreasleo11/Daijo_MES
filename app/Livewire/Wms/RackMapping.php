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
    public $selectedPositionId;
    public $editMaxCapacity;
    public $editCustomerCode;
    
    // Filtering State
    public $filterCustomer = '';
    protected $queryString = ['filterCustomer'];
    
    // UI State
    public $showDetail = false;

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

    public function saveSettings(WmsService $wmsService)
    {
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

    public function resetSlot(WmsService $wmsService)
    {
        $pos = WmsPosition::find($this->selectedPositionId);
        if ($pos) {
            $pos->update([
                'status' => 'EMPTY',
                'last_item_code' => null
            ]);
            
            $this->showDetail = false;
            session()->flash('success', 'Status slot ' . $pos->position_code . ' telah di-reset menjadi EMPTY.');
        }
    }

    public $newRackCode;
    public $newLevels = 2;
    public $newSlotsPerLevel = 4;
    public $newRackCustomer;
    public $newMaxCapacity = 1;
    public $showAddRackModal = false;

    public function createNewRack(WmsService $wmsService)
    {
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
                'whse_id' => 1, // Defaulting to J06
                'rack_code' => strtoupper($this->newRackCode),
            ]);

            // Simple Batch generation
            for ($l = 1; $l <= $this->newLevels; $l++) {
                for ($s = 1; $s <= $this->newSlotsPerLevel; $s++) {
                    $levelStr = str_pad($l, 2, '0', STR_PAD_LEFT);
                    $slotStr = str_pad($s, 2, '0', STR_PAD_LEFT);
                    
                    WmsPosition::create([
                        'rack_id' => $rack->id,
                        'level_no' => $l,
                        'slot_no' => $s,
                        'position_code' => strtoupper($rack->rack_code) . "-L{$levelStr}-S{$slotStr}",
                        'max_capacity' => $this->newMaxCapacity,
                        'customer_code' => strtoupper($this->newRackCustomer),
                    ]);
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
                $positionIds = WmsPosition::where('rack_id', $rack->id)->pluck('id');

                // Detach from Pallet Forms and reset status so they are not lost
                \App\Models\WmsPalletForm::whereIn('position_id', $positionIds)->update([
                    'position_id' => null,
                    'status' => 'GENERATED'
                ]);

                // Detach from Logs to preserve history without breaking foreign key
                \App\Models\WmsPalletLog::whereIn('position_id', $positionIds)->update([
                    'position_id' => null
                ]);

                // Delete all positions under this rack
                WmsPosition::whereIn('id', $positionIds)->delete();
                
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

    public function render()
    {
        $racks = WmsRack::with(['positions' => function($query) {
            $query->with('customer')
                  ->withCount('palletForms')
                  ->orderBy('level_no', 'desc')
                  ->orderBy('slot_no', 'asc');
        }])->get();

        $selectedPosData = $this->selectedPositionId 
            ? WmsPosition::with(['palletForms', 'customer'])->withCount('palletForms')->find($this->selectedPositionId) 
            : null;

        $customers = \App\Models\MasterCustomerDelivery::orderBy('customer_code')->get();

        $unassignedPallets = \App\Models\WmsPalletForm::whereNull('position_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.wms.rack-mapping', [
            'racks'             => $racks,
            'selectedPosData'   => $selectedPosData,
            'customers'         => $customers,
            'unassignedPallets' => $unassignedPallets,
        ]);
    }

    public function assignPalletToSelectedSlot($palletId, WmsService $wmsService)
    {
        if (! $this->selectedPositionId) {
            session()->flash('error', 'Pilih slot rak terlebih dahulu.');
            return;
        }

        try {
            $pos = WmsPosition::find($this->selectedPositionId);
            $pallet = \App\Models\WmsPalletForm::where('pallet_id', $palletId)->firstOrFail();

            $oldPosId = $pallet->position_id;
            $pallet->update(['position_id' => $pos->id]);

            if ($oldPosId) {
                $wmsService->updatePositionStatus($oldPosId);
            }
            $wmsService->updatePositionStatus($pos->id);

            $wmsService->logTransaction($pallet->pallet_id, 'ASSIGN_SLOT', $pos->id, auth()->id(), "Assigned by Store from Rack Mapping");

            session()->flash('success', "Pallet {$pallet->pallet_id} berhasil di-assign ke slot rak {$pos->position_code}.");
        } catch (\Exception $e) {
            session()->flash('error', "Gagal assign pallet: " . $e->getMessage());
        }
    }
}
