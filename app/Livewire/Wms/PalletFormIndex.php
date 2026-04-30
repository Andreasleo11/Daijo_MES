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

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deletePallet($palletId, WmsService $wmsService)
    {
        try {
            $pallet = WmsPalletForm::findOrFail($palletId);
            $positionId = $pallet->position_id;

            // Delete the pallet (will also delete details if cascading is active, 
            // but since we use SoftDeletes, we should be careful)
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

    public function render()
    {
        $palletForms = WmsPalletForm::with('position')
            ->where(function($query) {
                $query->where('pallet_id', 'like', '%' . $this->search . '%')
                      ->orWhere('part_no', 'like', '%' . $this->search . '%')
                      ->orWhere('model_name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.wms.pallet-form-index', [
            'palletForms' => $palletForms
        ]);
    }
}
