<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Delivery\sapInventoryFg;

class DailyItemCode extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'item_code',
        'quantity',
        'final_quantity',
        'loss_package_quantity',
        'actual_quantity',
        'shift',
        'start_time',
        'end_time',
        'is_done',
        'schedule_date',
        'start_date',
        'end_date',
        'remark',
        'temporal_cycletime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function spkRelation()
    {
        return $this->hasMany(SpkMaster::class);
    }

    public function masterItem()
    {
        return $this->hasOne(MasterListItem::class, 'item_code', 'item_code');
    }

    public function machinerelation()
    {
        return $this->hasOne(MachineJob::class, 'user_id', 'user_id');
    }

    public function scannedData()
    {
        return $this->hasMany(ProductionScannedData::class, 'dic_id', 'id');
    }

    public function hourlyRemarks()
    {
        return $this->hasMany(HourlyRemark::class, 'dic_id', 'id');
    }

    public function masterFg()
    {
        return $this->hasOne(sapInventoryFg::class, 'item_code', 'item_code');
    }
}
