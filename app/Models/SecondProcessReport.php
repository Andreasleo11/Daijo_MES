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
        'status',
        'output_destination',
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
        'ng_remarks',
        'created_by_name',
        'created_by_signed_at',
        'pqc_name',
        'pqc_signed_at',
        'leader_name',
        'leader_signed_at',
        'acknowledged_by_name',
        'acknowledged_signed_at',
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
