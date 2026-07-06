<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondProcessManpower extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'role',
        'no',
        'name',
    ];

    public function report()
    {
        return $this->belongsTo(SecondProcessReport::class, 'report_id');
    }
}
