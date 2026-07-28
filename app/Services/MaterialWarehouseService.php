<?php

namespace App\Services;

use App\Models\MwhIncomingHeader;
use App\Models\MwhOutgoing;
use App\Models\MwhPallet;
use App\Models\MwhPosition;
use Illuminate\Support\Facades\DB;

class MaterialWarehouseService
{
    /**
     * Generate unique Document No for Incoming Header.
     */
    public function generateDocumentNo(): string
    {
        $prefix = 'IN-' . date('Ymd') . '-';
        $latest = MwhIncomingHeader::withTrashed()
            ->where('document_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $num = $latest ? ((int) substr($latest->document_no, -4)) + 1 : 1;

        do {
            $code = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
            $exists = MwhIncomingHeader::withTrashed()->where('document_no', $code)->exists();
            if ($exists) {
                $num++;
            }
        } while ($exists);

        return $code;
    }

    /**
     * Generate unique Pallet ID.
     */
    public function generatePalletId(): string
    {
        $prefix = 'MPLT-' . date('Ymd') . '-';
        $latest = MwhPallet::withTrashed()
            ->where('pallet_id', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $num = $latest ? ((int) substr($latest->pallet_id, -4)) + 1 : 1;

        do {
            $code = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
            $exists = MwhPallet::withTrashed()->where('pallet_id', $code)->exists();
            if ($exists) {
                $num++;
            }
        } while ($exists);

        return $code;
    }

    /**
     * Generate unique Outgoing Code.
     */
    public function generateOutgoingCode(): string
    {
        $prefix = 'OUT-' . date('Ymd') . '-';
        $latest = MwhOutgoing::withTrashed()
            ->where('outgoing_code', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $num = $latest ? ((int) substr($latest->outgoing_code, -4)) + 1 : 1;

        do {
            $code = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
            $exists = MwhOutgoing::withTrashed()->where('outgoing_code', $code)->exists();
            if ($exists) {
                $num++;
            }
        } while ($exists);

        return $code;
    }

    /**
     * Recalculate and update the status of a rack slot position.
     */
    public function updatePositionStatus(int $positionId): void
    {
        $position = MwhPosition::find($positionId);
        if (!$position) return;

        $activePallets = MwhPallet::where('position_id', $positionId)
            ->where('current_qty', '>', 0)
            ->get();

        if ($activePallets->isEmpty()) {
            $position->update([
                'status' => 'EMPTY',
                'last_item_code' => null,
            ]);
        } else {
            $totalQty = $activePallets->sum('current_qty');
            $status = $totalQty >= $position->max_capacity ? 'FULL' : 'PARTIAL';
            $lastItem = $activePallets->last()->item_code;

            $position->update([
                'status' => $status,
                'last_item_code' => $lastItem,
            ]);
        }
    }

    /**
     * Get FIFO recommended pallets for a given item code.
     */
    public function getFifoRecommendations(string $itemCode)
    {
        return MwhPallet::with(['position.rack', 'material', 'incomingHeader'])
            ->leftJoin('mwh_incoming_headers', 'mwh_pallets.incoming_header_id', '=', 'mwh_incoming_headers.id')
            ->where('mwh_pallets.item_code', $itemCode)
            ->where('mwh_pallets.current_qty', '>', 0)
            ->whereIn('mwh_pallets.status', ['STORED', 'PARTIAL'])
            ->select('mwh_pallets.*')
            ->orderByRaw('COALESCE(mwh_incoming_headers.arrival_date, DATE(mwh_pallets.created_at)) ASC')
            ->orderBy('mwh_pallets.id', 'asc')
            ->get();
    }

    /**
     * Process partial/full outgoing picking for a pallet.
     */
    public function processOutgoingPicking(string $palletId, float $qtyTaken, string $outgoingDate, ?string $issuedTo = null, ?string $remarks = null): MwhOutgoing
    {
        return DB::transaction(function () use ($palletId, $qtyTaken, $outgoingDate, $issuedTo, $remarks) {
            $pallet = MwhPallet::where('pallet_id', $palletId)->firstOrFail();

            if ($pallet->is_qc_hold) {
                $reason = $pallet->qc_hold_reason ?: 'Tanpa keterangan';
                throw new \InvalidArgumentException("Pallet {$palletId} sedang di-HOLD oleh QC (Alasan: {$reason}) dan tidak dapat diambil.");
            }

            if ($qtyTaken <= 0 || $qtyTaken > $pallet->current_qty) {
                throw new \InvalidArgumentException("Jumlah pengambilan ({$qtyTaken} KG) melebihi sisa stok di Pallet {$palletId} ({$pallet->current_qty} KG).");
            }

            $newQty = $pallet->current_qty - $qtyTaken;
            $newStatus = $newQty <= 0 ? 'EMPTY' : 'PARTIAL';

            $oldPositionId = $pallet->position_id;

            $pallet->update([
                'current_qty' => $newQty,
                'status'      => $newStatus,
            ]);

            $outgoing = MwhOutgoing::create([
                'outgoing_code' => $this->generateOutgoingCode(),
                'pallet_id'     => $pallet->pallet_id,
                'position_id'   => $oldPositionId,
                'item_code'     => $pallet->item_code,
                'qty_taken'     => $qtyTaken,
                'uom'           => $pallet->uom ?? 'KG',
                'outgoing_date' => $outgoingDate,
                'issued_to'     => $issuedTo,
                'remarks'       => $remarks,
            ]);

            if ($oldPositionId) {
                $this->updatePositionStatus($oldPositionId);
            }

            return $outgoing;
        });
    }
}
