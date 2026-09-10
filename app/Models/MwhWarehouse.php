<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToBranch;

class MwhWarehouse extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $table = 'mwh_warehouses';

    protected $fillable = ['branch_id', 'whse_code', 'whse_name'];

    public function racks()
    {
        return $this->hasMany(MwhRack::class, 'whse_id');
    }

    public function incomingHeaders()
    {
        return $this->hasMany(MwhIncomingHeader::class, 'whse_id');
    }

    public function pallets()
    {
        return $this->hasMany(MwhPallet::class, 'whse_id');
    }

    public function outgoings()
    {
        return $this->hasMany(MwhOutgoing::class, 'whse_id');
    }
}
