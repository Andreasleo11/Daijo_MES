<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondProcessHourlyProduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'hour_ke',
        'ok_qty',
        'acumulasi_qty',
    ];

    public function report()
    {
        return $this->belongsTo(SecondProcessReport::class, 'report_id');
    }
}
