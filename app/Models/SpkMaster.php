<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpkMaster extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'planned_quantity',
        'completed_quantity',
    ];
}
