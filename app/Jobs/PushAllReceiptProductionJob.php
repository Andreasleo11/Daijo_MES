<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ReceiptProductionService;
use Illuminate\Support\Facades\Log;
use Throwable;

class PushAllReceiptProductionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Handle the job.
     */
    public function handle(ReceiptProductionService $service)
    {
        Log::channel('single')->info("[JOB] PushAllReceiptProductionJob STARTED at " . now());

        try {
            $success = $service->pushAllUnprocessed();

            if ($success) {
                Log::channel('single')->info("[JOB] PushAllReceiptProductionJob DONE ✅ : all unprocessed records sent to SAP.");
            } else {
                Log::channel('single')->warning("[JOB] PushAllReceiptProductionJob FAILED ⚠️ : pushAllUnprocessed() returned false.");
            }
        } catch (Throwable $e) {
            Log::channel('single')->error("[JOB] PushAllReceiptProductionJob CRASHED ❌ : " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // optionally biar job dicatat failed
            throw $e;
        }

        Log::channel('single')->info("[JOB] PushAllReceiptProductionJob FINISHED at " . now());
    }
}
