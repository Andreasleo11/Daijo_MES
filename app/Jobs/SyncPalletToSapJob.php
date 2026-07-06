<?php

namespace App\Jobs;

use App\Services\WmsSapSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncPalletToSapJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $palletId;
    
    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public $uniqueFor = 3600; 

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return (string) $this->palletId;
    }

    /**
     * Create a new job instance.
     */
    public function __construct($palletId)
    {
        $this->palletId = $palletId;
    }

    /**
     * Execute the job.
     */
    public function handle(WmsSapSyncService $service): void
    {
        Log::info("Job Started: Syncing Pallet {$this->palletId} to SAP");
        $service->syncPallet($this->palletId);
        Log::info("Job Finished: Syncing Pallet {$this->palletId} to SAP");
    }
}
