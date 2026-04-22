<?php

namespace App\Livewire\Wms;

use App\Models\WmsPalletForm;
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
