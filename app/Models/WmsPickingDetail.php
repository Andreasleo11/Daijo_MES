<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WmsPickingDetail extends Model
{
    use HasFactory;

    protected $table = 'wms_picking_details';

    protected $fillable = [
        'picking_header_id',
        'item_code',
        'model_name',
        'spk_no',
        'label',
        'pallet_id',
        'position_code',
        'qty_to_pick',
        'qty_picked',
        'is_picked',
        'fifo_seq',
        'status',
        'notes',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(WmsPickingHeader::class, 'picking_header_id');
    }
}
