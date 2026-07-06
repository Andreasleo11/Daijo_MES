<?php

namespace App\Services;

use App\Models\WmsPalletForm;
use App\Models\WmsPalletFormDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WmsSapSyncService extends ReceiptProductionService
{
    /**
     * Sync an entire pallet (Header + All Details) to SAP
     */
    public function syncPallet($palletId)
    {
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

        // Group items by SPK No
        $groupedItems = $pallet->details->groupBy('spk_no');
        $anyError = false;
        $allSuccess = true;

        foreach ($groupedItems as $spkNo => $items) {
            // 2. PARANOID CHECK: Selalu ambil data SEGAR dari DB tepat sebelum nembak
            // Jangan percaya data dari memori ($items) karena bisa stale
            $itemIds = $items->pluck('id')->toArray();
            $freshItemsToSync = WmsPalletFormDetail::whereIn('id', $itemIds)
                ->whereNotIn('sap_sync_status', [1, 4]) // Skip yang sudah Sukses (1) atau Abaikan (4)
                ->get();

            if ($freshItemsToSync->isEmpty()) {
                Log::info("SPK {$spkNo} in Pallet {$palletId} skipped: All items are already synced or ignored.");
                continue;
            }

            // Prepare payload
            $payload = [];
            $currentItemIds = $freshItemsToSync->pluck('id')->toArray();
            foreach ($freshItemsToSync as $item) {
                $payload[] = [
                    'summary_id' => (int)$item->id,
                    'spk_code'   => trim($item->spk_no),
                    'item_code'  => trim($item->part_no),
                    'warehouse'  => trim($item->warehouse ?: 'FFI'), 
                    'quantity'   => (float)$item->qty,
                    'label'      => (int)$item->label,
                ];
            }

            try {
                Log::info("SAP Sync Payload for SPK {$spkNo} in Pallet {$palletId}: " . json_encode($payload));
                
                $response = $this->post($this->endpoint, $payload);
                $rawBody = $response->body();
                $json = $response->json();
                
                Log::info("SAP Sync Response for SPK {$spkNo}: " . $rawBody);

                $success = $response->successful() && isset($json['status']) && $json['status'] === true;

                // Handle SAP Idempotency: Jika SAP bilang sudah ada/duplicate, anggap SUKSES
                $errorMsg = $json['message'] ?? $rawBody ?: "SAP rejected SPK {$spkNo}";
                $isDuplicate = (stripos($errorMsg, 'already exist') !== false || stripos($errorMsg, 'duplicate') !== false);

                if ($success || $isDuplicate) {
                    // PROTEKSI MATI: Hanya update yang statusnya BUKAN 1 (Success) atau 4 (Ignored)
                    WmsPalletFormDetail::whereIn('id', $currentItemIds)
                        ->whereNotIn('sap_sync_status', [1, 4])
                        ->update([
                            'sap_sync_status' => 1,
                            'sap_error_msg'   => $isDuplicate ? "SAP: " . $errorMsg : null,
                            'sap_sync_at'     => now(),
                        ]);
                    
                    $logMsg = $isDuplicate ? "SPK {$spkNo} marked as success (Duplicate/Already Exists)" : "SPK {$spkNo} synced successfully";
                    Log::info("[WMS-SAP] Pallet {$palletId} | IDs: " . implode(',', $currentItemIds) . " | " . $logMsg);
                    $this->saveApiLog('DeliveryReceiptFromProduction', 'POST', $this->endpoint, $payload, $json, 200, 'success', $logMsg);
                } else {
                    $anyError = true;
                    $allSuccess = false;
                    
                    WmsPalletFormDetail::whereIn('id', $currentItemIds)
                        ->whereNotIn('sap_sync_status', [1, 4]) // Guard agar yang sudah OK nggak kena timpa error temennya
                        ->update([
                            'sap_sync_status' => 2,
                            'sap_error_msg'   => $errorMsg,
                            'sap_sync_at'     => now(),
                        ]);

                    Log::warning("[WMS-SAP] Pallet {$palletId} | IDs: " . implode(',', $currentItemIds) . " | FAILED: " . $errorMsg);
                    $this->saveApiLog('DeliveryReceiptFromProduction', 'POST', $this->endpoint, $payload, $json, 400, 'failed', $errorMsg);
                }
            } catch (\Exception $e) {
                $anyError = true;
                $allSuccess = false;
                WmsPalletFormDetail::whereIn('id', $currentItemIds)
                    ->whereNotIn('sap_sync_status', [1, 4])
                    ->update([
                        'sap_sync_status' => 2,
                        'sap_error_msg'   => $e->getMessage(),
                        'sap_sync_at'     => now(),
                    ]);
                Log::error("[WMS-SAP] Pallet {$palletId} | EXCEPTION: " . $e->getMessage());
            }
        }

        // 3. GROUND TRUTH CHECK: Tanya database langsung buat nentuin status Header
        // Jangan percaya flag memori, tanya realita di tabel detail
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
        
        $pallet->sap_sync_at = now();
        $pallet->save();

        Log::info("Pallet {$palletId} sync process finished. Final Header Status: " . $pallet->sap_sync_status);

        return ['status' => $allSynced, 'message' => $allSynced ? 'Success' : 'Partial or Total Failure'];
    }
}
