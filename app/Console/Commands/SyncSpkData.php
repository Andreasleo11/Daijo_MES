<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SpkMasterService;

class SyncSpkData extends Command
{
    protected $signature = 'spk:sync';
    protected $description = 'Sync SPK data from SAP';

    protected $spkService;

    public function __construct(SpkMasterService $spkService)
    {
        parent::__construct();
        $this->spkService = $spkService;
    }

    public function handle()
    {
        $this->info('Starting SPK sync...');
        $this->spkService->SyncData();
        $this->info('SPK sync completed.');
    }
}
