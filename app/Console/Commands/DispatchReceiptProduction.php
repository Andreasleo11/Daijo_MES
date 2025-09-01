<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\PushAllReceiptProductionJob;

class DispatchReceiptProduction extends Command
{
    protected $signature = 'sap:dispatch-receipt';
    protected $description = 'Dispatch all unprocessed production scanned data to SAP';

    public function handle()
    {
        PushAllReceiptProductionJob::dispatch();

        $this->info("Dispatched PushAllReceiptProductionJob to queue.");
    }
}
