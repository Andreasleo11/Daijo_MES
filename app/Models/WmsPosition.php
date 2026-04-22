<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WmsPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'rack_id', 'level_no', 'slot_no', 'customer_code', 
        'position_code', 'status', 'last_item_code', 'max_capacity'
    ];

    public function rack()
    {
        return $this->belongsTo(WmsRack::class, 'rack_id');
    }

    public function palletForms()
    {
        return $this->hasMany(WmsPalletForm::class, 'position_id');
    }
}
