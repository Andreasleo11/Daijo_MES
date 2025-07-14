<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HourlyRemark extends Model
{
    use HasFactory;

    protected $fillable = [
        'dic_id', // DailyItemCode ID
        'start_time',
        'end_time',
        'target',
        'actual',
        'remark',
        'is_achieve',
        'pic',
        'actual_production',
    ];

    public function dailyItemCode()
    {
        return $this->belongsTo(DailyItemCode::class, 'dic_id');
    }
    
}
