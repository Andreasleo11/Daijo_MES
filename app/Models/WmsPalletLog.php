<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WmsPalletLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'pallet_id', 'transaction_type', 'position_id', 'user_id', 'notes'
    ];

    public function position()
    {
        return $this->belongsTo(WmsPosition::class, 'position_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
