<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondProcessNgHourlyDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'ng_record_id',
        'hour_ke',
        'qty',
    ];

    public function ngRecord()
    {
        return $this->belongsTo(SecondProcessNgRecord::class, 'ng_record_id');
    }
}
