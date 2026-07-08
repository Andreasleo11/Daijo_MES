<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondProcessNgRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'ng_category',
        'ng_name',
        'total_ng',
        'ng_input_item',
        'ng_input_qty',
        'remark',
    ];

    public function report()
    {
        return $this->belongsTo(SecondProcessReport::class, 'report_id');
    }

    public function hourlyDetails()
    {
        return $this->hasMany(SecondProcessNgHourlyDetail::class, 'ng_record_id');
    }
}
