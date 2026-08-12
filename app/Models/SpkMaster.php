<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpkMaster extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'spk_number',
        'post_date',
        'due_date',
        'production_status',
        'item_code',
        'planned_quantity',
        'completed_quantity',
        'warehouse',
    ];

    public function masterItem()
    {
        return $this->belongsTo(MasterListItem::class, 'item_code', 'item_code');
    }
}
