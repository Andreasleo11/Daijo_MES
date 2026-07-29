<?php

namespace App\Livewire\Wms;

use App\Models\WmsPalletForm;
use App\Services\WmsService;
use Livewire\Component;
use Livewire\WithPagination;

class PalletFormIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $filterSlot = 'ALL'; // ALL, UNASSIGNED, ASSIGNED

    // Assign Slot Modal State (Store)
    public bool $showAssignModal = false;
    public ?string $assignPalletId = null;
    public ?int $assignPositionId = null;

    protected $queryString = ['search', 'filterSlot'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterSlot()
    {
        $this->resetPage();
    }

    public function openAssignModal($palletId)
    {
        $pallet = WmsPalletForm::where('pallet_id', $palletId)->firstOrFail();

        if ($pallet->total_pallet_qty <= 0) {
            session()->flash('error', "Pallet {$pallet->pallet_id} sudah habis (quantity 0) dan tidak perlu di-assign ke slot rak.");
            return;
        }

        $this->assignPalletId   = $pallet->pallet_id;
        $this->assignPositionId = $pallet->position_id;
        $this->showAssignModal  = true;
    }

    public function saveAssignSlot(WmsService $wmsService)
    {
        $this->validate([
            'assignPositionId' => 'required|exists:wms_positions,id',
        ], [
            'assignPositionId.required' => 'Pilih slot rak yang akan di-assign.',
        ]);

        try {
            $pallet = WmsPalletForm::where('pallet_id', $this->assignPalletId)->firstOrFail();

            if ($pallet->total_pallet_qty <= 0) {
                session()->flash('error', "Pallet {$pallet->pallet_id} sudah habis (quantity 0) dan tidak perlu di-assign ke slot rak.");
                $this->showAssignModal = false;
                return;
            }

            $oldPositionId = $pallet->position_id;
            $newPositionId = (int) $this->assignPositionId;

            $pallet->update([
                'position_id' => $newPositionId,
            ]);

            // Update old & new position tracking
            if ($oldPositionId && $oldPositionId !== $newPositionId) {
                $wmsService->updatePositionStatus($oldPositionId);
            }
            $wmsService->updatePositionStatus($newPositionId);

            // Log Store transaction
            $wmsService->logTransaction($pallet->pallet_id, 'ASSIGN_SLOT', $newPositionId, auth()->id(), "Assigned by Store to slot");

            $newPos = \App\Models\WmsPosition::find($newPositionId);
            session()->flash('success', "Pallet {$pallet->pallet_id} berhasil di-assign ke slot rak {$newPos->position_code} oleh Store.");

            $this->showAssignModal = false;
        } catch (\Exception $e) {
            session()->flash('error', "Gagal meng-assign slot rak: " . $e->getMessage());
        }
    }

    public function deletePallet($palletId, WmsService $wmsService)
    {
        try {
            $pallet = WmsPalletForm::findOrFail($palletId);
            $positionId = $pallet->position_id;

            // Delete the pallet
            $pallet->delete();

            // Update rack status if it was in a rack
            if ($positionId) {
                $wmsService->updatePositionStatus($positionId);
            }

            session()->flash('success', "Pallet $palletId berhasil dihapus.");
        } catch (\Exception $e) {
            session()->flash('error', "Gagal menghapus pallet: " . $e->getMessage());
        }
    }

    public function render(WmsService $wmsService)
    {
        // Self-healing: Ensure any pallet with total_pallet_qty <= 0 or status == OUT has position_id cleared
        $stalePallets = WmsPalletForm::whereNotNull('position_id')
            ->where(function($q) {
                $q->where('total_pallet_qty', '<=', 0)
                  ->orWhere('status', 'OUT');
            })
            ->get();

        if ($stalePallets->isNotEmpty()) {
            $affectedPosIds = $stalePallets->pluck('position_id')->filter()->unique();
            WmsPalletForm::whereIn('id', $stalePallets->pluck('id'))
                ->update(['position_id' => null]);

            foreach ($affectedPosIds as $posId) {
                $wmsService->updatePositionStatus($posId);
            }
        }

        $palletForms = WmsPalletForm::with(['position.rack', 'details.item.customer'])
            ->where(function($query) {
                $query->where('pallet_id', 'like', '%' . $this->search . '%')
                      ->orWhere('part_no', 'like', '%' . $this->search . '%')
                      ->orWhere('model_name', 'like', '%' . $this->search . '%')
                      ->orWhere('delivery_name', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterSlot !== 'ALL', function($query) {
                if ($this->filterSlot === 'UNASSIGNED') {
                    $query->whereNull('position_id')
                          ->where('total_pallet_qty', '>', 0)
                          ->where('status', '!=', 'OUT');
                } elseif ($this->filterSlot === 'ASSIGNED') {
                    $query->whereNotNull('position_id')
                          ->where('total_pallet_qty', '>', 0)
                          ->where('status', '!=', 'OUT');
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $availablePositions = \App\Models\WmsPosition::with('rack')
            ->orderBy('position_code', 'asc')
            ->get();

        return view('livewire.wms.pallet-form-index', [
            'palletForms'        => $palletForms,
            'availablePositions' => $availablePositions,
        ]);
    }
}
