<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sap\BomWipService;
use App\Services\Sap\DelActualService;
use App\Services\Sap\DelSchedService;
use App\Services\Sap\DelSoService;
use App\Services\Sap\InventoryFgService;
use App\Services\Sap\InventoryMtrService;
use App\Services\Sap\LineProductionService;
use App\Services\Sap\RejectService;
use Illuminate\Support\Facades\Log;
use App\Models\ApiLog;

class SyncDeliveryDataCommand extends Command
{
    /**
     * Nama dan signature command.
     *
     * Jalankan pakai:
     * php artisan sync:delivery-data
     */
    protected $signature = 'sync:delivery-data';

    /**
     * Deskripsi command.
     */
    protected $description = 'Menjalankan SyncData() untuk semua service SAP & delivery';

    /**
     * Jalankan command-nya.
     */
    public function handle()
    {
        $this->info('🚀 Memulai sinkronisasi data delivery & SAP...');
        Log::info('=== SyncDeliveryDataCommand dimulai ===');

        // Daftar semua service yang ingin dijalankan
        $services = [
            BomWipService::class,
            DelActualService::class,
            DelSchedService::class,
            DelSoService::class,
            InventoryFgService::class,
            InventoryMtrService::class,
            LineProductionService::class,
            RejectService::class,
        ];

        foreach ($services as $serviceClass) {
            $serviceName = class_basename($serviceClass);
            $this->line(str_repeat('=', 60));
            $this->info("➡️  Menjalankan {$serviceName}::SyncData()");

            $startTime = microtime(true);
            try {
                $service = app($serviceClass);
                $result = $service->SyncData();

                $duration = microtime(true) - $startTime;

                $this->info("✅ {$serviceName} selesai disinkronkan. ({$duration}s)");
                Log::info("{$serviceName} SyncData sukses.");

                // Log Success to DB
                ApiLog::create([
                    'api_name' => "SyncDelivery:{$serviceName}",
                    'method' => 'COMMAND',
                    'endpoint' => 'sync:delivery-data',
                    'request_payload' => ['service' => $serviceClass, 'start_time' => date('Y-m-d H:i:s')],
                    'response_payload' => ['message' => 'Success', 'duration' => $duration],
                    'status_code' => 200,
                    'status' => 'SUCCESS',
                    'message' => "{$serviceName} synced successfully",
                ]);

            } catch (\Throwable $e) {
                $this->error("❌ Gagal di {$serviceName}: {$e->getMessage()}");
                Log::error("Gagal di {$serviceName}: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);

                // Log Error to DB
                ApiLog::create([
                    'api_name' => "SyncDelivery:{$serviceName}",
                    'method' => 'COMMAND',
                    'endpoint' => 'sync:delivery-data',
                    'request_payload' => ['service' => $serviceClass, 'start_time' => date('Y-m-d H:i:s')],
                    'response_payload' => ['trace' => substr($e->getTraceAsString(), 0, 1000)], // Limit trace
                    'status_code' => 500,
                    'status' => 'ERROR',
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $this->line(str_repeat('=', 60));
        $this->info('🎉 Semua service selesai disinkronkan.');
        Log::info('=== SyncDeliveryDataCommand selesai ===');
    }
}
