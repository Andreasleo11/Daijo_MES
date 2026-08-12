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
        try {
            $spkData = $this->getAll();

            // 1. Fetch current existing SPKs from spk_masters before truncate
            $oldSpks = DB::table('spk_masters')->get()->keyBy('spk_number');

            // 2. Generate a unique batch ID for this sync
            $batchId = 'SYNC-' . now('Asia/Jakarta')->format('YmdHis');

            // 3. Map new SPKs key-by SPKNo
            $newSpkMap = [];
            foreach ($spkData as $row) {
                $spkNo = (string) ($row['SPKNo'] ?? '');
                if ($spkNo !== '') {
                    $newSpkMap[$spkNo] = $row;
                }
            }

            $changeLogs = [];
            $now = now();

            // Check for NEW, QTY_CHANGE, STATUS_CHANGE
            foreach ($newSpkMap as $spkNo => $row) {
                $itemCode = $row['ItemCode'] ?? null;
                $newPlanned = isset($row['PlannedQty']) ? (int)$row['PlannedQty'] : null;
                $newCompleted = isset($row['CompletedQty']) ? (int)$row['CompletedQty'] : null;
                $newStatus = $row['Status'] ?? null;

                if (!isset($oldSpks[$spkNo])) {
                    // NEW SPK
                    $changeLogs[] = [
                        'sync_batch_id' => $batchId,
                        'spk_number' => $spkNo,
                        'item_code' => $itemCode,
                        'change_type' => 'NEW',
                        'old_planned_qty' => null,
                        'new_planned_qty' => $newPlanned,
                        'old_completed_qty' => null,
                        'new_completed_qty' => $newCompleted,
                        'old_status' => null,
                        'new_status' => $newStatus,
                        'details' => json_encode(['note' => 'SPK Baru dirilis dari SAP']),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                } else {
                    $old = $oldSpks[$spkNo];
                    $oldPlanned = (int)$old->planned_quantity;
                    $oldCompleted = (int)$old->completed_quantity;
                    $oldStatus = (string)$old->production_status;

                    $isQtyChanged = ($oldPlanned !== $newPlanned) || ($oldCompleted !== $newCompleted);
                    $isStatusChanged = ($oldStatus !== (string)$newStatus);

                    if ($isQtyChanged || $isStatusChanged) {
                        $changeType = $isQtyChanged ? 'QTY_CHANGE' : 'STATUS_CHANGE';
                        $changeLogs[] = [
                            'sync_batch_id' => $batchId,
                            'spk_number' => $spkNo,
                            'item_code' => $itemCode ?: $old->item_code,
                            'change_type' => $changeType,
                            'old_planned_qty' => $oldPlanned,
                            'new_planned_qty' => $newPlanned,
                            'old_completed_qty' => $oldCompleted,
                            'new_completed_qty' => $newCompleted,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus,
                            'details' => json_encode([
                                'planned_diff' => $newPlanned - $oldPlanned,
                                'completed_diff' => $newCompleted - $oldCompleted,
                            ]),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            // Check for REMOVED SPK (in old, not in new)
            foreach ($oldSpks as $spkNo => $old) {
                if (!isset($newSpkMap[$spkNo])) {
                    $changeLogs[] = [
                        'sync_batch_id' => $batchId,
                        'spk_number' => $spkNo,
                        'item_code' => $old->item_code,
                        'change_type' => 'REMOVED',
                        'old_planned_qty' => (int)$old->planned_quantity,
                        'new_planned_qty' => null,
                        'old_completed_qty' => (int)$old->completed_quantity,
                        'new_completed_qty' => null,
                        'old_status' => $old->production_status,
                        'new_status' => 'CLOSED/REMOVED',
                        'details' => json_encode(['note' => 'SPK tidak ditemukan di sync SAP terbaru']),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Insert change logs if any
            if (!empty($changeLogs)) {
                DB::table('spk_change_logs')->insert($changeLogs);
            }

            // Hapus data lama & simpan data baru ke spk_masters
            DB::table('spk_masters')->truncate();

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

            // Simpan log sukses
            DB::table('api_logs')->insert([
                'api_name' => 'SPK_SYNC',
                'method'   => 'GET',
                'endpoint' => $this->baseUrl . '/api/sap_production_order/list',
                'status_code' => 200,
                'status'   => 'success',
                'message'  => 'Data SPK berhasil disinkronkan. Terdeteksi ' . count($changeLogs) . ' perubahan.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data SPK berhasil disinkronkan.',
                'changes_count' => count($changeLogs),
                'batch_id' => $batchId
            ]);
        } catch (\Exception $e) {
            DB::table('api_logs')->insert([
                'api_name' => 'SPK_SYNC',
                'method'   => 'GET',
                'endpoint' => $this->baseUrl . '/api/sap_production_order/list',
                'status_code' => 500,
                'status'   => 'failed',
                'message'  => $e->getMessage(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['message' => 'Gagal sinkron data SPK', 'error' => $e->getMessage()], 500);
        }
    }

}
