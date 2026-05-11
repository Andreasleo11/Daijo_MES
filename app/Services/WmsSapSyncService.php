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
        $pallet = WmsPalletForm::with('details')->find($palletId);
        if (!$pallet) return ['status' => false, 'message' => 'Pallet not found'];

        if ($pallet->details->isEmpty()) {
            return ['status' => false, 'message' => 'Pallet has no items to sync'];
        }

        // Group items by SPK No
        $groupedItems = $pallet->details->groupBy('spk_no');
        $anyError = false;
        $allSuccess = true;

        foreach ($groupedItems as $spkNo => $items) {
            // Filter hanya item yang BELUM sukses (status != 1)
            $itemsToSync = $items->where('sap_sync_status', '!=', 1);

            if ($itemsToSync->isEmpty()) {
                Log::info("Skipping SPK {$spkNo} in Pallet {$palletId} because it's already synced.");
                continue;
            }

            // Prepare payload for this specific SPK
            $payload = [];
            $itemIds = [];
            foreach ($itemsToSync as $item) {
                $payload[] = [
                    'summary_id' => (int)$item->id,
                    'spk_code'   => trim($item->spk_no),
                    'item_code'  => trim($item->part_no),
                    'warehouse'  => trim($item->warehouse ?: 'FFI'), 
                    'quantity'   => (float)$item->qty,
                    'label'      => (int)$item->label,
                ];
                $itemIds[] = $item->id;
            }

            try {
                Log::info("SAP Sync Payload for SPK {$spkNo} in Pallet {$palletId}: " . json_encode($payload));
                
                $response = $this->post($this->endpoint, $payload);
                $rawBody = $response->body();
                $json = $response->json();
                
                Log::info("SAP Sync Response for SPK {$spkNo}: " . $rawBody);

                $success = $response->successful() && isset($json['status']) && $json['status'] === true;

                if ($success) {
                    WmsPalletFormDetail::whereIn('id', $itemIds)->update([
                        'sap_sync_status' => 1,
                        'sap_error_msg'   => null,
                        'sap_sync_at'     => now(),
                    ]);
                    $this->saveApiLog('DeliveryReceiptFromProduction', 'POST', $this->endpoint, $payload, $json, 200, 'success', "SPK {$spkNo} synced successfully");
                } else {
                    $anyError = true;
                    $allSuccess = false;
                    $errorMsg = $json['message'] ?? $rawBody ?: "SAP rejected SPK {$spkNo}";
                    
                    WmsPalletFormDetail::whereIn('id', $itemIds)->update([
                        'sap_sync_status' => 2,
                        'sap_error_msg'   => $errorMsg,
                        'sap_sync_at'     => now(),
                    ]);
                    $this->saveApiLog('DeliveryReceiptFromProduction', 'POST', $this->endpoint, $payload, $json, 400, 'failed', $errorMsg);
                }
            } catch (\Exception $e) {
                $anyError = true;
                $allSuccess = false;
                WmsPalletFormDetail::whereIn('id', $itemIds)->update([
                    'sap_sync_status' => 2,
                    'sap_error_msg'   => $e->getMessage(),
                    'sap_sync_at'     => now(),
                ]);
                Log::error("SAP Sync Exception for SPK {$spkNo}: " . $e->getMessage());
            }
        }

        // Final Header Status Update
        if ($allSuccess) {
            $pallet->sap_sync_status = 1; // All Success
        } elseif ($anyError) {
            $pallet->sap_sync_status = 2; // Partial or Total Error
        }
        
        $pallet->sap_sync_at = now();
        $pallet->save();

        return ['status' => $allSuccess, 'message' => $allSuccess ? 'Success' : 'Partial or Total Failure'];
    }
}
