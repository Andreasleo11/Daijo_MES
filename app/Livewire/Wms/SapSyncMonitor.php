<?php

namespace App\Livewire\Wms;

use App\Models\WmsPalletForm;
use App\Models\WmsPalletFormDetail;
use App\Services\WmsSapSyncService;
use App\Jobs\SyncPalletToSapJob;
use Livewire\Component;
use Livewire\WithPagination;

class SapSyncMonitor extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    
    // Modal properties
    public $showDetails = false;
    public $selectedPalletId = null;
    public $palletDetails = [];

    protected $queryString = ['search', 'statusFilter'];

    public bool $isDelivery = true;

    public function mount()
    {
        if (request()->routeIs('wms.sap-sync-monitor-delivery')) {
            $this->isDelivery = true;
        }
    }

    public function setDeliveryType(bool $isDelivery)
    {
        $this->isDelivery = $isDelivery;
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    /**
     * Show modal with pallet item details
     */
    public function viewDetails($palletId)
    {
        $this->selectedPalletId = $palletId;
        $this->palletDetails = WmsPalletFormDetail::where('pallet_form_id', $palletId)->get();
        $this->showDetails = true;
    }

    public function closeDetails()
    {
        $this->showDetails = false;
        $this->selectedPalletId = null;
        $this->palletDetails = [];
    }

    /**
     * Retry syncing a pallet
     */
    /**
     * Retry syncing a pallet
     */
    public function retrySync($palletId)
    {
        // Reset status to pending (0) to trigger UI polling
        WmsPalletForm::where('pallet_id', $palletId)->update([
            'sap_sync_status' => 0,
            'updated_at'      => now()
        ]);

        // Cuma riset detail yang BELUM sukses (status != 1) dan BELUM diabaikan (status != 4)
        WmsPalletFormDetail::where('pallet_form_id', $palletId)
            ->whereNotIn('sap_sync_status', [1, 4])
            ->update([
                'sap_sync_status' => 0,
                'sap_error_msg'   => null
            ]);

        SyncPalletToSapJob::dispatch($palletId);
        
        session()->flash('message', "Pallet {$palletId} re-queued for synchronization.");
    }

    /**
     * Retry syncing a specific SPK in a pallet
     */
    public function retrySpk($palletId, $spkNo)
    {
        // Reset status header ke pending (0)
        WmsPalletForm::where('pallet_id', $palletId)->update([
            'sap_sync_status' => 0,
            'updated_at'      => now()
        ]);

        // Reset detail yang bermasalah pada SPK ini ke pending (0)
        WmsPalletFormDetail::where('pallet_form_id', $palletId)
            ->where('spk_no', $spkNo)
            ->whereNotIn('sap_sync_status', [1, 4])
            ->update([
                'sap_sync_status' => 0,
                'sap_error_msg'   => null
            ]);

        SyncPalletToSapJob::dispatch($palletId);

        // Refresh detail list di modal
        $this->palletDetails = WmsPalletFormDetail::where('pallet_form_id', $palletId)->get();

        session()->flash('message', "SPK {$spkNo} in Pallet {$palletId} re-queued for synchronization.");
    }

    /**
     * Mark a pallet as ignored
     */
    public function ignorePallet($palletId)
    {
        WmsPalletForm::where('pallet_id', $palletId)->update([
            'sap_sync_status' => 4, // 4 = IGNORED
            'sap_error_msg'   => 'Manually ignored by user'
        ]);

        WmsPalletFormDetail::where('pallet_form_id', $palletId)
            ->where('sap_sync_status', '!=', 1)
            ->update([
                'sap_sync_status' => 4,
                'sap_error_msg'   => 'Manually ignored by user'
            ]);

        session()->flash('message', "Pallet {$palletId} marked as ignored.");
    }

    /**
     * Mark a specific detail as ignored
     */
    public function ignoreDetail($detailId)
    {
        WmsPalletFormDetail::where('id', $detailId)->update([
            'sap_sync_status' => 4,
            'sap_error_msg'   => 'Manually ignored by user'
        ]);

        // Refresh details
        if ($this->selectedPalletId) {
            $this->palletDetails = WmsPalletFormDetail::where('pallet_form_id', $this->selectedPalletId)->get();
        }

        session()->flash('message', "Item marked as ignored.");
    }

    /**
     * Retry all failed pallets
     */
    public function retryAllFailed()
    {
        $failedPalletIds = WmsPalletForm::where('sap_sync_status', 2)->pluck('pallet_id');
        
        if ($failedPalletIds->isEmpty()) return;

        // Bulk Reset Status to 0 cuma buat yang BELUM sukses
        WmsPalletForm::whereIn('pallet_id', $failedPalletIds)->update(['sap_sync_status' => 0]);
        WmsPalletFormDetail::whereIn('pallet_form_id', $failedPalletIds)
            ->whereNotIn('sap_sync_status', [1, 4])
            ->update([
                'sap_sync_status' => 0,
                'sap_error_msg'   => null
            ]);

        foreach ($failedPalletIds as $id) {
            SyncPalletToSapJob::dispatch($id);
        }

        session()->flash('message', count($failedPalletIds) . " failed pallets re-queued.");
    }

    public function render()
    {
        $query = WmsPalletForm::query()->withCount('details');

        if ($this->isDelivery) {
            $query->whereHas('details', function ($q) {
                $q->where('warehouse', 'FG');
            });
        } else {
            $query->whereDoesntHave('details', function ($q) {
                $q->where('warehouse', 'FG');
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('pallet_id', 'like', '%' . $this->search . '%')
                  ->orWhere('part_no', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('sap_sync_status', $this->statusFilter);
        }

        $pallets = $query->orderBy('created_at', 'desc')->paginate(10);

        // Stats
        if ($this->isDelivery) {
            $baseQuery = WmsPalletForm::whereHas('details', fn($q) => $q->where('warehouse', 'FG'));
        } else {
            $baseQuery = WmsPalletForm::whereDoesntHave('details', fn($q) => $q->where('warehouse', 'FG'));
        }

        $stats = [
            'total'   => (clone $baseQuery)->count(),
            'success' => (clone $baseQuery)->where('sap_sync_status', 1)->count(),
            'failed'  => (clone $baseQuery)->where('sap_sync_status', 2)->count(),
            'pending' => (clone $baseQuery)->where('sap_sync_status', 0)->count(),
        ];

        return view('livewire.wms.sap-sync-monitor', [
            'pallets' => $pallets,
            'stats'   => $stats
        ]);
    }
}
