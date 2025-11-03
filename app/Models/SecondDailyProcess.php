<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondDailyProcess extends Model
{
    protected $table = 'second_daily_processes';

    protected $fillable = [
        'id',
        'plan_date',
        'line',
        'item_code',
        'item_description',
        'quantity_hour',
        'process_time',
        'quantity_plan',
        'pic',
        'customer',
        'shift',
    ];

}
