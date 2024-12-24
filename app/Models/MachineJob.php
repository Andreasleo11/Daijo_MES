<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineJob extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'item_code',
        'shift',
        'employee_name',
    ];
}
