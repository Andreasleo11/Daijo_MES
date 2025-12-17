<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BomWipService;
use App\Services\DelActualService;
use App\Services\DelSchedService;
use App\Services\DelSoService;
use App\Services\InventoryFgService;
use App\Services\InventoryMtrService;
use App\Services\LineProductionService;
use App\Services\RejectService;
use Illuminate\Support\Facades\Log;

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

            try {
                $service = app($serviceClass);
                $service->SyncData();

                $this->info("✅ {$serviceName} selesai disinkronkan.");
                Log::info("{$serviceName} SyncData sukses.");
            } catch (\Throwable $e) {
                $this->error("❌ Gagal di {$serviceName}: {$e->getMessage()}");
                Log::error("Gagal di {$serviceName}: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->line(str_repeat('=', 60));
        $this->info('🎉 Semua service selesai disinkronkan.');
        Log::info('=== SyncDeliveryDataCommand selesai ===');
    }
}
