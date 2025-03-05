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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Relation to DailyItemCode (assuming they are linked by user_id)
    public function dailyItemCode()
    {
        return $this->hasMany(DailyItemCode::class, 'user_id', 'user_id');
    }

    public function mouldChangeLogs()
    {
        return $this->hasMany(MouldChangeLog::class, 'user_id', 'user_id');
    }
}
