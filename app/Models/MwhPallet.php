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
    ];

    protected $casts = [
        'initial_qty' => 'decimal:2',
        'current_qty' => 'decimal:2',
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
}
