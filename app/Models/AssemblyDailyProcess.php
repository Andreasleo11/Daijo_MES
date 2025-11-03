<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssemblyDailyProcess extends Model
{
    use HasFactory;

    protected $table = 'assembly_daily_processes';

    protected $fillable = [
        'id',
        'plan_date',
        'line',
        'item_code',
        'item_description',
        'quantity',
        'remark',
    ];
}
