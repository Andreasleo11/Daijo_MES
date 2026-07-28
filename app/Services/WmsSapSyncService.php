<?php

namespace App\Services;

use App\Models\WmsPalletForm;
use App\Models\WmsPalletFormDetail;
use App\Models\SpkItemHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WmsSapSyncService extends BaseSapService
{
    /**
     * Sync an entire pallet (Header + All Details) to SAP for Inventory Transfer
     */
    public function syncPalletInventoryTransfer($palletId)
    {
        $startTime = microtime(true);
        $endpoint = '/api/inventory_transfer/create';

        // 1. ATOMIC LOCK: Tandai pallet sebagai PROCESSING (3) hanya jika status saat ini PENDING (0) atau FAILED (2)
        $locked = WmsPalletForm::where('pallet_id', $palletId)
            ->whereIn('sap_sync_status', [0, 2])
            ->update([
                'sap_sync_status' => 3, // Status 3 = PROCESSING
                'sap_error_msg'   => 'Syncing in progress...',
                'updated_at'      => now()
            ]);

        if (!$locked) {
            Log::warning("Pallet {$palletId} skipped: Already synced or processing by another thread.");
            return ['status' => false, 'message' => 'Already synced or processing'];
        }

        $pallet = WmsPalletForm::with('details')->find($palletId);
        if (!$pallet) return ['status' => false, 'message' => 'Pallet not found'];

        if ($pallet->details->isEmpty()) {
            $pallet->update(['sap_sync_status' => 2, 'sap_error_msg' => 'No items']);
            return ['status' => false, 'message' => 'Pallet has no items to sync'];
        }

        // 2. PARANOID CHECK: Selalu ambil data SEGAR dari DB tepat sebelum nembak
        $freshItemsToSync = WmsPalletFormDetail::where('pallet_form_id', $palletId)
            ->whereNotIn('sap_sync_status', [1, 4]) // Skip yang sudah Sukses (1) atau Abaikan (4)
            ->get();

        if ($freshItemsToSync->isEmpty()) {
            Log::info("Pallet {$palletId} skipped: All items are already synced or ignored.");
            return ['status' => true, 'message' => 'Already synced or ignored'];
        }

        // Prepare payload
        $lines = [];
        $currentItemIds = $freshItemsToSync->pluck('id')->toArray();
        $groupedBySpk = $freshItemsToSync->groupBy('spk_no');

        foreach ($groupedBySpk as $spkNo => $items) {
            $firstItem = $items->first();
            $spkHistory = SpkItemHistory::where('spk_number', $spkNo)->first();
            $actualItemCode = $spkHistory ? $spkHistory->item_code : '';

            $lines[] = [
                'itemCode'      => $actualItemCode,
                'quantity'      => (float)$items->sum('qty'),
                'fromWarehouse' => 'FFI',
                'toWarehouse'   => 'FG', 
                'u_NUMPR'       => (string)$spkNo,
            ];
        }

        $payload = [
            'docDate' => $pallet->prod_date ? date('Y-m-d', strtotime($pallet->prod_date)) : now()->format('Y-m-d'),
            'remarks' => "Inventory Transfer created from {$pallet->pallet_id}",
            'lines'   => $lines,
        ];

        try {
            Log::info("SAP Sync (Inventory Transfer) Payload for Pallet {$palletId}: " . json_encode($payload));
            
            $response = $this->post($endpoint, $payload);
            $rawBody = $response->body();
            $json = $response->json();
            
            Log::info("SAP Sync (Inventory Transfer) Response for Pallet {$palletId}: " . $rawBody);

            $success = $response->successful() && isset($json['status']) && $json['status'] === true;

            // Handle SAP Idempotency: Jika SAP bilang sudah ada/duplicate, anggap SUKSES
            $errorMsg = $json['message'] ?? $rawBody ?: "SAP rejected Pallet {$palletId}";
            $isDuplicate = (stripos($errorMsg, 'already exist') !== false || stripos($errorMsg, 'duplicate') !== false);

            if ($success || $isDuplicate) {
                WmsPalletFormDetail::whereIn('id', $currentItemIds)
                    ->whereNotIn('sap_sync_status', [1, 4])
                    ->update([
                        'sap_sync_status' => 1,
                        'sap_error_msg'   => $isDuplicate ? "SAP: " . $errorMsg : null,
                        'sap_sync_at'     => now(),
                    ]);
                
                $logMsg = $isDuplicate ? "Pallet {$palletId} marked as success (Duplicate/Already Exists)" : "Pallet {$palletId} synced successfully";
                Log::info("[WMS-SAP-TRANSFER] Pallet {$palletId} | IDs: " . implode(',', $currentItemIds) . " | " . $logMsg);
                $this->saveApiLog('InventoryTransfer', 'POST', $endpoint, $payload, $json, 200, 'success', $logMsg);
            } else {
                WmsPalletFormDetail::whereIn('id', $currentItemIds)
                    ->whereNotIn('sap_sync_status', [1, 4])
                    ->update([
                        'sap_sync_status' => 2,
                        'sap_error_msg'   => $errorMsg,
                        'sap_sync_at'     => now(),
                    ]);

                Log::warning("[WMS-SAP-TRANSFER] Pallet {$palletId} | IDs: " . implode(',', $currentItemIds) . " | FAILED: " . $errorMsg);
                $this->saveApiLog('InventoryTransfer', 'POST', $endpoint, $payload, $json, 400, 'failed', $errorMsg);
            }
        } catch (\Exception $e) {
            WmsPalletFormDetail::whereIn('id', $currentItemIds)
                ->whereNotIn('sap_sync_status', [1, 4])
                ->update([
                    'sap_sync_status' => 2,
                    'sap_error_msg'   => $e->getMessage(),
                    'sap_sync_at'     => now(),
                ]);
            Log::error("[WMS-SAP-TRANSFER] Pallet {$palletId} | EXCEPTION: " . $e->getMessage());
            $this->saveApiLog('InventoryTransfer', 'POST', $endpoint, $payload, [], 500, 'failed', 'Exception: ' . $e->getMessage());
        }

        // 3. GROUND TRUTH CHECK
        $hasPending = WmsPalletFormDetail::where('pallet_form_id', $palletId)->whereIn('sap_sync_status', [0, 3])->exists();
        $hasError   = WmsPalletFormDetail::where('pallet_form_id', $palletId)->where('sap_sync_status', 2)->exists();
        $allSynced  = !WmsPalletFormDetail::where('pallet_form_id', $palletId)->whereNotIn('sap_sync_status', [1, 4])->exists();

        if ($allSynced) {
            $pallet->sap_sync_status = 1; // All Success
            $pallet->sap_error_msg = null;
        } elseif ($hasError) {
            $pallet->sap_sync_status = 2; // Partial or Total Error
            $pallet->sap_error_msg = "Beberapa item gagal sinkron. Silakan cek detail.";
        } elseif ($hasPending) {
            $pallet->sap_sync_status = 0; // Masih ada yang antri
        }
        
        $duration = round(microtime(true) - $startTime, 2);
        $pallet->sap_sync_duration = $duration;
        $pallet->sap_sync_at = now();
        $pallet->save();

        Log::info("Pallet {$palletId} sync process finished in {$duration} seconds. Final Header Status: " . $pallet->sap_sync_status);

        return ['status' => $allSynced, 'message' => $allSynced ? 'Success' : 'Partial or Total Failure'];
    }

    /**
     * Test connection & authentication to SAP API endpoint.
     * Returns explicit error message if failed.
     */
    public function testConnection(): array
    {
        try {
            $token = $this->getToken();
            if ($token) {
                return [
                    'status'  => true,
                    'message' => 'Koneksi ke SAP API Berhasil (Token terverifikasi).',
                ];
            }
            return [
                'status'  => false,
                'message' => 'Koneksi ke SAP API Gagal: Token tidak ditemukan.',
            ];
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => 'Error Koneksi SAP API: ' . $e->getMessage(),
            ];
        }
    }
}
