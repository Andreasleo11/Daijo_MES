<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondProcessNgRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'ng_name',
        'hour_1',
        'hour_2',
        'hour_3',
        'hour_4',
        'hour_5',
        'hour_6',
        'hour_7',
        'total_ng',
        'ng_input_item',
        'ng_input_qty',
    ];

    public function report()
    {
        return $this->belongsTo(SecondProcessReport::class, 'report_id');
    }
}
