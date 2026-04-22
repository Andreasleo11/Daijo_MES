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
     * Recommend a position for the given item and customer
     */
    public function recommendPosition($customerCode, $partNo)
    {
        // 1. Try to find a PARTIAL slot with the same item code for consolidation
        // MUST have remaining capacity
        $partialSlot = WmsPosition::withCount('palletForms')
            ->where('last_item_code', $partNo)
            ->where('status', 'PARTIAL')
            ->get()
            ->filter(function($pos) {
                return $pos->pallet_forms_count < $pos->max_capacity;
            })
            ->first();

        if ($partialSlot) {
            return $partialSlot;
        }

        // 2. Try to find the first EMPTY slot (No restriction)
        $emptySlot = WmsPosition::where('status', 'EMPTY')
            ->orderBy('id')
            ->first();

        if ($emptySlot) {
            return $emptySlot;
        }

        return null; // Warehouse full
    }

    /**
     * Update the status of a position based on its occupancy and max capacity
     */
    public function updatePositionStatus($positionId)
    {
        $pos = WmsPosition::withCount(['palletForms' => function($q) {
            $q->where('status', 'STORED');
        }])->find($positionId);
        
        if (!$pos) return;

        if ($pos->pallet_forms_count <= 0) {
            $pos->update(['status' => 'EMPTY', 'last_item_code' => null]);
        } elseif ($pos->pallet_forms_count >= $pos->max_capacity) {
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
