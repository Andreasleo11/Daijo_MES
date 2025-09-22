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
        $records = DB::table('production_scanned_data')
        ->where('processed', 0)
        // ->where('spk_code', '25023034') 
        ->where('spk_code', '99999999') 
        ->get();

        // ->whereIn('spk_code', [25024585, 25024610])
        // ->whereIn('spk_code', [25023034])

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

        // Group by SPK
        $grouped = $records->groupBy('spk_code');
       

        Log::info("SAP Push START", ['spk_count' => $grouped->count()]);

        foreach ($grouped as $spkCode => $items) {
            $payload = [];
            $payload[] = [
                'spk_code'  => $spkCode,
                'item_code' => $items->first()->item_code,
                'warehouse' => $items->first()->warehouse,
                'quantity'  => $items->sum('quantity'),
                'label'     => $items->count(),
            ];
            

            try {
                $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->token,
                        'Accept'        => 'application/json',
                        'Host'          => 'localhost',
                    ])
                    ->post($this->baseUrl . $this->endpoint, $payload);

                $json   = $response->json();
                $status = $response->successful() && isset($json['status']) && $json['status'] === true;

                if ($status) {
                    DB::table('production_scanned_data')
                        ->whereIn('id', $items->pluck('id'))
                        ->update(['processed' => 1]);

                    Log::info("SAP Push SUCCESS", [
                        'spk_code' => $spkCode,
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
                        'SPK ' . $spkCode . ' processed successfully'
                    );
                } else {
                    Log::error("SAP Push FAILED", [
                        'spk_code' => $spkCode,
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
                        'SPK ' . $spkCode . ' failed: ' . $response->body()
                    );
                }
            } catch (\Throwable $e) {
                Log::error("SAP Push EXCEPTION", [
                    'spk_code' => $spkCode,
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
                    'SPK ' . $spkCode . ' exception: ' . $e->getMessage()
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
