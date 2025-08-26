<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon; 
use Illuminate\Support\Facades\DB;

class SpkMasterService extends BaseSapService
{
    public function getAll()
    {
        $route = '/api/sap_production_order/list';

        $rawData = [];

        $response = $this->get($route);
        // dd($response);
        $data = $this->normalizeResponse($response, 'SPK');
        // dd($data);
        // $spkCodes = collect($data)->pluck('SPKNo')->toArray();
        // dd($spkCodes);

        return $this->transformData($data);
    }

    private function transformData(array $data)
    {
        $data = collect($data)->map(function ($item) {
            if (!is_array($item)) return [];

            // Format PostDate
            if (!empty($item['PostDate'])) {
                $item['PostDate'] = Carbon::createFromFormat('d/m/Y', $item['PostDate'])->format('Y-m-d');
            }

            // Format DueDate
            if (!empty($item['DueDate'])) {
                $item['DueDate'] = Carbon::createFromFormat('d/m/Y', $item['DueDate'])->format('Y-m-d');
            }

            // Bersihin PlannedQty & CompletedQty dari titik/koma
            if (isset($item['PlannedQty'])) {
                $item['PlannedQty'] = preg_replace('/[.,]/', '', $item['PlannedQty']);
            }
            if (isset($item['CompletedQty'])) {
                $item['CompletedQty'] = preg_replace('/[.,]/', '', $item['CompletedQty']);
            }

            return $item;
        })->filter();


        return $data->values()->all();
    }


    private function normalizeResponse($response, $tag = 'SAP')
    {
        if (!is_array($response)) {
            Log::warning("[{$tag}] Response bukan array", ['response' => $response]);
            return [];
        }

        if (array_key_exists('data', $response)) {
            return is_array($response['data']) ? $response['data'] : [];
        }

        return $response;
    }

    public function SyncData()
    {
        $spkData = $this->getAll();
        // Hapus data lama
        DB::table('spk_masters')->truncate();

        // Simpan data baru
        foreach ($spkData as $row) {
            DB::table('spk_masters')->insert([
                'spk_number' => $row['SPKNo'],
                'post_date' => $row['PostDate'],
                'due_date' => $row['DueDate'],
                'production_status' => $row['Status'],
                'item_code' => $row['ItemCode'],
                'planned_quantity' => $row['PlannedQty'],
                'completed_quantity' => $row['CompletedQty'],
                'warehouse' => $row['Warehouse'],
            ]);
        }

        return response()->json(['message' => 'Data forecast berhasil disinkronkan']);
    }
}
