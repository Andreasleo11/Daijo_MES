<?php

namespace App\Livewire\Wms;

use App\Models\WmsPalletForm;
use Livewire\Component;

class PalletFormLookup extends Component
{
    public $pallet_id = '';
    public $palletForm = null;

    protected $rules = [
        'pallet_id' => 'required',
    ];

    public function updatedPalletId($value)
    {
        $this->pallet_id = trim($value);
        if (empty($this->pallet_id)) {
            $this->palletForm = null;
            return;
        }

        $this->palletForm = WmsPalletForm::with(['details' => function($q) {
            $q->withTrashed();
        }, 'position'])
            ->where('pallet_id', $this->pallet_id)
            ->first();

        if (!$this->palletForm) {
            session()->flash('error', 'Pallet ID "' . $this->pallet_id . '" tidak ditemukan.');
            $this->palletForm = null;
            $this->dispatch('scan-error');
        } else {
            session()->forget('error');
            $this->dispatch('scan-success');
        }

        // Auto-select text for next scan
        $this->dispatch('select-pallet-id');
    }

    public function clear()
    {
        $this->pallet_id = '';
        $this->palletForm = null;
        $this->dispatch('focus-pallet-id');
    }

    public function render()
    {
        return view('livewire.wms.pallet-form-lookup');
    }
}
