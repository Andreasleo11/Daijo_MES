<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOutputLog extends Model
{
    use HasFactory;

    protected $table = 'production_output_logs';

    protected $fillable = [
        'dic_id',
        'operator_name',
        'quantity',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public function dailyItemCode()
    {
        return $this->belongsTo(DailyItemCode::class, 'dic_id');
    }
}
