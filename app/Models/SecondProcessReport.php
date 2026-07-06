<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondProcessReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'unit_line',
        'shift',
        'process_prod',
        'model',
        'part_number',
        'part_name',
        'customer',
        'target_per_hour',
        'jml_input_wip',
        'repairan',
        'jumlah_output',
        'jumlah_ok',
        'jumlah_ng',
        'ng_prosentase',
        'jml_ng_lebur',
        'next_production_schedule',
        'absent_employees',
        'production_notes',
        'created_by_name',
        'pqc_name',
        'acknowledged_by_name',
    ];

    protected $casts = [
        'next_production_schedule' => 'array',
    ];

    public function materials()
    {
        return $this->hasMany(SecondProcessMaterial::class, 'report_id');
    }

    public function hourlyProductions()
    {
        return $this->hasMany(SecondProcessHourlyProduction::class, 'report_id');
    }

    public function manpowers()
    {
        return $this->hasMany(SecondProcessManpower::class, 'report_id');
    }

    public function ngRecords()
    {
        return $this->hasMany(SecondProcessNgRecord::class, 'report_id');
    }

    public function troubles()
    {
        return $this->hasMany(SecondProcessTrouble::class, 'report_id');
    }
}
