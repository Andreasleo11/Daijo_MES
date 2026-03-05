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
        $records = DB::table('production_summary')
            ->where('sap_sent', 0)
            ->where('warehouse', strtoupper('FFI'))
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
            // Cari item_code dari production_scanned_data menggunakan spk_code
            $scannedData = DB::table('production_scanned_data')
                ->where('spk_code', $summary->spk_code)
                ->first();

            if (!$scannedData) {
                Log::warning("Scanned data not found for SPK", ['spk_code' => $summary->spk_code]);
                
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
                    // Update production_summary set sap_sent = 1 dan sap_sent_at = now()
                    DB::table('production_summary')
                        ->where('id', $summary->id)
                        ->update([
                            'sap_sent' => 1,
                            'sap_sent_at' => now(),
                        ]);

                    Log::info("SAP Push SUCCESS", [
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
                        'spk_code' => $summary->spk_code,
                        'status'   => $response->status(),
                        'body'     => $response->body(),
                        'json'     => $json,
                    ]);

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
                    'spk_code' => $summary->spk_code,
                    'error'    => $e->getMessage(),
                ]);

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