<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReceiptProductionService extends BaseSapService
{
    protected $endpoint = '/api/receipt_production/create';

    public function pushAllUnprocessed()
    {
        // Ambil dari production_summary yang belum dikirim ke SAP
        // Exclude sap_sent=2 (sedang diproses worker lain) dan sap_sent=1 (sudah sukses)
        $records = DB::table('production_summary')
            ->where('sap_sent', 0)
            ->whereIn('warehouse', ['FFI', 'KRFFI'])
            ->get();

        \Log::info('Scheduler jalan, records count: ' . $records->count());

        if ($records->isEmpty()) {
            try {
                $this->saveApiLog(
                    'receipt_production',
                    'POST',
                    $this->endpoint,
                    [],
                    [],
                    204,
                    'failed',
                    'No unprocessed records found'
                );
                \Log::info('SaveApiLog sukses dibuat dari scheduler.');
                return;
            } catch (\Throwable $e) {
                \Log::error('Gagal saveApiLog: ' . $e->getMessage());
            }
        }

        Log::info("SAP Push START", ['summary_count' => $records->count()]);

        foreach ($records as $summary) {
            // === ATOMIC LOCK: Tandai sebagai PROCESSING (2) sebelum tembak SAP ===
            // Hanya update jika status masih 0 (Pending). Jika return 0 rows,
            // berarti worker/scheduler lain sudah mengambilnya — skip.
            $locked = DB::table('production_summary')
                ->where('id', $summary->id)
                ->where('sap_sent', 0)
                ->update(['sap_sent' => 2, 'updated_at' => now()]);

            if (!$locked) {
                Log::warning("[SAP Push] SPK {$summary->spk_code} SKIPPED - sudah diambil worker lain atau sudah diproses.");
                continue;
            }

            // Cari item_code dari production_scanned_data menggunakan spk_code
            $scannedData = DB::table('production_scanned_data')
                ->where('spk_code', $summary->spk_code)
                ->first();

            if (!$scannedData) {
                Log::warning("Scanned data not found for SPK", ['spk_code' => $summary->spk_code]);

                // Revert lock ke 0 supaya bisa dicoba ulang
                DB::table('production_summary')
                    ->where('id', $summary->id)
                    ->where('sap_sent', 2)
                    ->update(['sap_sent' => 0, 'updated_at' => now()]);

                $this->saveApiLog(
                    'receipt_production',
                    'POST',
                    $this->endpoint,
                    [],
                    [],
                    404,
                    'failed',
                    'SPK ' . $summary->spk_code . ' - scanned data not found'
                );
                continue;
            }

            $payload = [
                [
                    'summary_id' => $summary->id, 
                    'spk_code'  => $summary->spk_code,
                    'item_code' => $scannedData->item_code,
                    'warehouse' => $summary->warehouse,
                    'quantity'  => $summary->total_quantity,
                    'label'     => $summary->label,
                ]
            ];


            try {
                $response = $this->post($this->endpoint, $payload);

                $json   = $response->json();
                $status = $response->successful() && isset($json['status']) && $json['status'] === true;
                
                if ($status) {
                    // Update dari status PROCESSING (2) ke SUCCESS (1)
                    DB::table('production_summary')
                        ->where('id', $summary->id)
                        ->where('sap_sent', 2) // Hanya update dari status processing
                        ->update([
                            'sap_sent'    => 1,
                            'sap_sent_at' => now(),
                            'updated_at'  => now(),
                        ]);

                    Log::info("SAP Push SUCCESS", [
                        'summary_id' => $summary->id,
                        'spk_code' => $summary->spk_code,
                        'payload'  => $payload,
                        'response' => $json,
                    ]);

                    $this->saveApiLog(
                        'receipt_production',
                        'POST',
                        $this->endpoint,
                        $payload,
                        $json,
                        $response->status(),
                        'success',
                        'SPK ' . $summary->spk_code . ' sent to SAP successfully'
                    );
                } else {
                    Log::error("SAP Push FAILED", [
                        'summary_id' => $summary->id,
                        'spk_code' => $summary->spk_code,
                        'status'   => $response->status(),
                        'body'     => $response->body(),
                        'json'     => $json,
                    ]);

                    // Revert lock ke 0 supaya bisa dicoba ulang di run berikutnya
                    DB::table('production_summary')
                        ->where('id', $summary->id)
                        ->where('sap_sent', 2)
                        ->update(['sap_sent' => 0, 'updated_at' => now()]);

                    $this->saveApiLog(
                        'receipt_production',
                        'POST',
                        $this->endpoint,
                        $payload,
                        $json,
                        $response->status(),
                        'failed',
                        'SPK ' . $summary->spk_code . ' failed: ' . $response->body()
                    );
                }
            } catch (\Throwable $e) {
                Log::error("SAP Push EXCEPTION", [
                    'summary_id' => $summary->id,
                    'spk_code' => $summary->spk_code,
                    'error'    => $e->getMessage(),
                ]);

                // Revert lock ke 0 supaya bisa dicoba ulang
                DB::table('production_summary')
                    ->where('id', $summary->id)
                    ->where('sap_sent', 2)
                    ->update(['sap_sent' => 0, 'updated_at' => now()]);

                $this->saveApiLog(
                    'receipt_production',
                    'POST',
                    $this->endpoint,
                    $payload,
                    [],
                    500,
                    'failed',
                    'SPK ' . $summary->spk_code . ' exception: ' . $e->getMessage()
                );
            }
        }
    }

    public function pushSingleRecord($summary, $scannedData)
    {
        // === ATOMIC LOCK untuk manual push ===
        // Hanya boleh push jika status masih 0 (Pending)
        $locked = DB::table('production_summary')
            ->where('id', $summary->id)
            ->where('sap_sent', 0)
            ->update(['sap_sent' => 2, 'updated_at' => now()]);

        if (!$locked) {
            $current = DB::table('production_summary')->where('id', $summary->id)->value('sap_sent');
            $statusMap = [1 => 'sudah terkirim ke SAP', 2 => 'sedang diproses', 99 => 'diabaikan'];
            $reason = $statusMap[$current] ?? 'status tidak diketahui';
            Log::warning("[SAP Push Manual] SPK {$summary->spk_code} SKIPPED - {$reason}.");
            return [
                'success' => false,
                'message' => "Tidak bisa dikirim: {$reason}.",
            ];
        }

        try {
            $payload = [
                [
                    'summary_id' => $summary->id, 
                    'spk_code'  => $summary->spk_code,
                    'item_code' => $scannedData->item_code,
                    'warehouse' => $summary->warehouse,
                    'quantity'  => $summary->total_quantity,
                    'label'     => $summary->label,
                ]
            ];
 
            $response = $this->post($this->endpoint, $payload);
            $json = $response->json();
            $status = $response->successful() && isset($json['status']) && $json['status'] === true;
 
            if ($status) {
                // Update dari PROCESSING (2) ke SUCCESS (1)
                DB::table('production_summary')
                    ->where('id', $summary->id)
                    ->where('sap_sent', 2)
                    ->update([
                        'sap_sent'    => 1,
                        'sap_sent_at' => now(),
                        'updated_at'  => now(),
                    ]);
 
                Log::info("SAP Push SUCCESS (Manual)", [
                    'summary_id' => $summary->id,
                    'spk_code' => $summary->spk_code,
                    'payload'  => $payload,
                    'response' => $json,
                ]);
 
                $this->saveApiLog(
                    'receipt_production',
                    'POST',
                    $this->endpoint,
                    $payload,
                    $json,
                    $response->status(),
                    'success',
                    'SPK ' . $summary->spk_code . ' sent to SAP successfully (Manual)'
                );
 
                return [
                    'success' => true,
                    'message' => 'Berhasil dikirim ke SAP',
                    'response' => $json,
                ];
            } else {
                Log::error("SAP Push FAILED (Manual)", [
                    'summary_id' => $summary->id,
                    'spk_code' => $summary->spk_code,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                    'json'     => $json,
                ]);

                // Revert lock ke 0 supaya bisa dicoba ulang
                DB::table('production_summary')
                    ->where('id', $summary->id)
                    ->where('sap_sent', 2)
                    ->update(['sap_sent' => 0, 'updated_at' => now()]);
 
                $this->saveApiLog(
                    'receipt_production',
                    'POST',
                    $this->endpoint,
                    $payload,
                    $json,
                    $response->status(),
                    'failed',
                    'SPK ' . $summary->spk_code . ' failed: ' . $response->body()
                );
 
                return [
                    'success' => false,
                    'message' => $response->body() ?? 'SAP returned error status',
                    'response' => $json,
                ];
            }
 
        } catch (\Throwable $e) {
            Log::error("SAP Push EXCEPTION (Manual)", [
                'summary_id' => $summary->id ?? null,
                'spk_code' => $summary->spk_code ?? null,
                'error'    => $e->getMessage(),
            ]);

            // Revert lock ke 0 supaya bisa dicoba ulang
            DB::table('production_summary')
                ->where('id', $summary->id)
                ->where('sap_sent', 2)
                ->update(['sap_sent' => 0, 'updated_at' => now()]);
 
            $this->saveApiLog(
                'receipt_production',
                'POST',
                $this->endpoint,
                $payload ?? [],
                [],
                500,
                'failed',
                'SPK ' . ($summary->spk_code ?? 'unknown') . ' exception: ' . $e->getMessage()
            );
 
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }
    

    protected function saveApiLog($apiName, $method, $endpoint, $request, $response, $statusCode, $status, $message)
    {
        DB::table('api_logs')->insert([
            'api_name'        => $apiName,
            'method'          => $method,
            'endpoint'        => $endpoint,
            'request_payload' => json_encode($request, JSON_PRETTY_PRINT),
            'response_payload'=> json_encode($response, JSON_PRETTY_PRINT),
            'status_code'     => $statusCode,
            'status'          => $status,
            'message'         => $message,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }
}