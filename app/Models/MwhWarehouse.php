<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MwhWarehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mwh_warehouses';

    protected $fillable = ['whse_code', 'whse_name'];

    public function racks()
    {
        return $this->hasMany(MwhRack::class, 'whse_id');
    }
}
