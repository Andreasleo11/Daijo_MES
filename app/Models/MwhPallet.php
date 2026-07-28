<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MwhPallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mwh_pallets';

    protected $fillable = [
        'pallet_id',
        'incoming_header_id',
        'item_code',
        'lot_no',
        'initial_qty',
        'current_qty',
        'uom',
        'position_id',
        'status',
        'is_qc_hold',
        'qc_hold_reason',
        'created_at',
    ];

    protected $casts = [
        'initial_qty' => 'decimal:2',
        'current_qty' => 'decimal:2',
        'is_qc_hold'  => 'boolean',
    ];

    public function incomingHeader()
    {
        return $this->belongsTo(MwhIncomingHeader::class, 'incoming_header_id');
    }

    public function material()
    {
        return $this->belongsTo(MasterListMaterial::class, 'item_code', 'item_code');
    }

    public function position()
    {
        return $this->belongsTo(MwhPosition::class, 'position_id');
    }

    public function outgoings()
    {
        return $this->hasMany(MwhOutgoing::class, 'pallet_id', 'pallet_id');
    }

    /**
     * Dynamically compute live pallet status based on current_qty vs initial_qty.
     */
    public function getStatusAttribute($value): string
    {
        if ($this->current_qty <= 0) {
            return 'EMPTY';
        }
        if ($this->current_qty < $this->initial_qty) {
            return 'PARTIAL';
        }
        return $value ?: 'STORED';
    }
}
