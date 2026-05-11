<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WmsPalletForm extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'wms_pallet_forms';
    protected $primaryKey = 'pallet_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'pallet_id', 'position_id', 'part_no', 'model_name', 
        'prod_date', 'lot_no', 'delivery_name', 'delivery_shift', 
        'box_qty', 'total_pallet_qty', 'remarks',
        'sap_sync_status', 'sap_error_msg', 'sap_sync_at'
    ];

    protected $casts = [
        'sap_sync_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(WmsPalletFormDetail::class, 'pallet_form_id', 'pallet_id');
    }

    public function position()
    {
        return $this->belongsTo(WmsPosition::class, 'position_id');
    }
}
