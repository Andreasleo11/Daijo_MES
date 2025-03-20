<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionScannedData extends Model
{
    use HasFactory;

    protected $table = 'production_scanned_data';

    protected $fillable = [
        'spk_code',
        'dic_id',
        'item_code',
        'warehouse',
        'quantity',
        'label',
        'user',
        'processed',
    ];

    public function ParentDailyItemCode()
    {
        return $this->hasOne(DailyItemCode::class, 'id', 'dic_id');
    }
}
