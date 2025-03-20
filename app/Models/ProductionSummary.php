<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionSummary extends Model
{
    use HasFactory;
    protected $table = 'production_summary';
    
    protected $fillable = [
        'spk_code',
        'total_quantity',
        'warehouse',
        'label',
        'created_date',
    ];
}
