<?php

namespace App\Services;

use App\Models\WmsPosition;
use App\Models\WmsPalletForm;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WmsService
{
    /**
     * Generate a unique Pallet ID: PLT-YYYYMMDD-XXXX
     */
    public function generatePalletId()
    {
        $date = Carbon::now()->format('Ymd');
        $prefix = 'PLT-' . $date . '-';
        
        $lastPallet = WmsPalletForm::where('pallet_id', 'LIKE', $prefix . '%')
            ->orderBy('pallet_id', 'desc')
            ->first();

        if ($lastPallet) {
            $lastNum = (int) substr($lastPallet->pallet_id, -4);
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }

        return $prefix . $newNum;
    }

    /**
     * Recommend a position for the given customer codes and primary part no.
     * Supports multi-item pallets (mixed customer codes).
     *
     * @param  array  $customerCodes  All customer codes from scanned items
     * @param  string $primaryPartNo  First/primary item code (for consolidation)
     */
    public function recommendPosition(array $customerCodes, string $primaryPartNo): ?WmsPosition
    {
        $uniqueCustomers = array_unique(array_filter($customerCodes));
        $isMixed         = count($uniqueCustomers) > 1;

        if (!$isMixed && count($uniqueCustomers) === 1) {
            $customerCode = $uniqueCustomers[0];

            // 1. Try PARTIAL slot with same item for consolidation
            $partialSlot = WmsPosition::withCount('palletForms')
                ->where('customer_code', $customerCode)
                ->where('last_item_code', $primaryPartNo)
                ->where('status', 'PARTIAL')
                ->get()
                ->filter(fn($pos) => $pos->pallet_forms_count < $pos->max_capacity)
                ->first();

            if ($partialSlot) {
                return $partialSlot;
            }

            // 2. Try EMPTY slot for the same customer
            $emptyCustomer = WmsPosition::where('customer_code', $customerCode)
                ->where('status', 'EMPTY')
                ->orderBy('id')
                ->first();

            if ($emptyCustomer) {
                return $emptyCustomer;
            }
        }

        // 3. Fallback: any EMPTY slot (for mixed customers or when customer slots are full)
        return WmsPosition::where('status', 'EMPTY')->orderBy('id')->first();
    }

    /**
     * Update the status of a position based on its occupancy and max capacity
     */
    public function updatePositionStatus($positionId)
    {
        $pos = WmsPosition::with(['palletForms' => function($q) {
            $q->where('status', 'STORED');
        }])->find($positionId);
        
        if (!$pos) return;

        $totalQtySum = $pos->palletForms->sum('total_pallet_qty');
        $palletCount = $pos->palletForms->count();
        
        if ($palletCount <= 0) {
            $pos->update(['status' => 'EMPTY', 'last_item_code' => null]);
        } elseif ($totalQtySum >= $pos->max_capacity) {
            $pos->update(['status' => 'FULL']);
        } else {
            $pos->update(['status' => 'PARTIAL']);
        }
    }

    /**
     * Log a pallet transaction (IN/OUT)
     */
    public function logTransaction($palletId, $type, $positionId = null, $notes = null)
    {
        \App\Models\WmsPalletLog::create([
            'pallet_id' => $palletId,
            'transaction_type' => $type,
            'position_id' => $positionId,
            'user_id' => auth()->id(),
            'notes' => $notes
        ]);
    }
}
