<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WmsPalletFormDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'pallet_form_id', 
        'spk_no', 
        'qty', 
        'warehouse', 
        'label'
    ];

    public function header()
    {
        return $this->belongsTo(WmsPalletForm::class, 'pallet_form_id', 'pallet_id');
    }
}
