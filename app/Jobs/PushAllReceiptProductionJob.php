<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ReceiptProductionService;
use Illuminate\Support\Facades\Log;

class PushAllReceiptProductionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(ReceiptProductionService $service)
    {
        Log::info("Starting PushAllReceiptProductionJob...");

        $success = $service->pushAllUnprocessed();

        if ($success) {
            Log::info("PushAllReceiptProductionJob DONE: all unprocessed records sent to SAP.");
        } else {
            Log::warning("PushAllReceiptProductionJob FAILED: check logs for details.");
        }
    }
}
