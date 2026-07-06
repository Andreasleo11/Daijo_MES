<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondProcessTrouble extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'penyebab',
        'penanganan',
        'loss_time',
    ];

    public function report()
    {
        return $this->belongsTo(SecondProcessReport::class, 'report_id');
    }
}
