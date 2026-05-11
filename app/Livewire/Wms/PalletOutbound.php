<?php

namespace App\Livewire\Wms;

use App\Models\WmsPalletForm;
use App\Services\WmsService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PalletOutbound extends Component
{
    public $pallet_id_input;
    public $isProcessing = false;

    public function processOutbound(WmsService $wmsService)
    {
        if ($this->isProcessing) return;
        $this->isProcessing = true;

        $this->validate([
            'pallet_id_input' => 'required'
        ]);

        $pallet = WmsPalletForm::where('pallet_id', $this->pallet_id_input)->first();

        if (!$pallet) {
            session()->flash('error', 'Pallet ID ' . $this->pallet_id_input . ' tidak ditemukan.');
            $this->pallet_id_input = '';
            $this->isProcessing = false;
            $this->dispatch('scan-error');
            return;
        }

        if ($pallet->status === 'OUT') {
            session()->flash('error', 'Pallet ID ' . $this->pallet_id_input . ' sudah tercatat keluar sebelumnya.');
            $this->pallet_id_input = '';
            $this->isProcessing = false;
            $this->dispatch('scan-error');
            return;
        }

        try {
            DB::beginTransaction();

            $oldPositionId = $pallet->position_id;

            // 1. Update Pallet Status
            $pallet->update([
                'status' => 'OUT',
                'position_id' => null // Remove from rack
            ]);

            // 2. Log Transaction
            $wmsService->logTransaction($pallet->pallet_id, 'OUT', $oldPositionId);

            // 3. Update Position Status
            if ($oldPositionId) {
                $wmsService->updatePositionStatus($oldPositionId);
            }

            DB::commit();

            session()->flash('success', 'Pallet ' . $pallet->pallet_id . ' berhasil di-scan KELUAR. Rak kini kosong.');
            $this->pallet_id_input = '';
            $this->dispatch('scan-success');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
            $this->dispatch('scan-error');
        } finally {
            $this->isProcessing = false;
        }
    }

    public function render()
    {
        return view('livewire.wms.pallet-outbound');
    }
}
