<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpProductionSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'operator_id',
        'unit_line',
        'shift',
        'status',
        'started_at',
        'finished_at',
        'total_input',
        'total_good',
        'total_reject',
        'total_rework_in',
        'total_rework_recovered',
        'total_scrap',
        'remarks',
        'approved_by',
        'approved_at',
        'is_qc_bypassed',
        'qc_bypass_reason',
        'qc_bypassed_at',
        'qc_bypassed_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_qc_bypassed' => 'boolean',
        'qc_bypassed_at' => 'datetime',
    ];

    public function workOrder()
    {
        return $this->belongsTo(SpWorkOrder::class, 'work_order_id');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function qcBypassedBy()
    {
        return $this->belongsTo(User::class, 'qc_bypassed_by');
    }

    public function productionEntries()
    {
        return $this->hasMany(SpProductionEntry::class, 'session_id');
    }

    public function rejectEntries()
    {
        return $this->hasMany(SpRejectEntry::class, 'session_id');
    }

    public function reworkEntries()
    {
        return $this->hasMany(SpReworkEntry::class, 'session_id');
    }

    public function downtimeEntries()
    {
        return $this->hasMany(SpDowntimeEntry::class, 'session_id');
    }

    public function inputEntries()
    {
        return $this->hasMany(SpInputEntry::class, 'session_id');
    }

    public function manpowerEntries()
    {
        return $this->hasMany(SpSessionManpower::class, 'session_id');
    }

    public function defectEntries()
    {
        return $this->rejectEntries();
    }

    public function manpowers()
    {
        return $this->manpowerEntries();
    }

    public function getYieldAttribute(): float
    {
        $totalOutput = $this->total_good + $this->total_reject;
        if ($totalOutput == 0) return 0;
        return round(($this->total_good / $totalOutput) * 100, 2);
    }

    public function getNgRateAttribute(): float
    {
        $totalOutput = $this->total_good + $this->total_reject;
        if ($totalOutput == 0) return 0;
        return round(($this->total_reject / $totalOutput) * 100, 2);
    }

    public function recalculateTotals(): void
    {
        $this->total_good = $this->productionEntries()->sum('good_qty');
        $this->total_reject = $this->productionEntries()->sum('reject_qty');
        $this->total_input = $this->inputEntries()->sum('quantity');
        $this->total_rework_in = $this->reworkEntries()->sum('input_qty');
        $this->total_rework_recovered = $this->reworkEntries()->sum('recovered_qty');
        $this->total_scrap = $this->reworkEntries()->sum('scrapped_qty');
        $this->save();
    }
}
