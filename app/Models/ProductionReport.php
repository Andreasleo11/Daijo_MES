<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'spk_no',
        'target',
        'scanned',
        'outstanding',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'machine_id');
    }
}
