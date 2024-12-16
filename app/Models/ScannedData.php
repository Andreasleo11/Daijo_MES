<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScannedData extends Model
{
    use HasFactory;

    protected $fillable = [
        'doc_num',
        'item_code',
        'quantity',
        'warehouse',
        'label',
    ];

    public function soData()
    {
        return $this->belongsTo(SoData::class, 'doc_num', 'doc_num');
    }
}
