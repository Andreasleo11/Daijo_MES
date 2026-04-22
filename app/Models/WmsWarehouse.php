<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WmsWarehouse extends Model
{
    use HasFactory;

    protected $fillable = ['whse_code', 'whse_name'];

    public function racks()
    {
        return $this->hasMany(WmsRack::class, 'whse_id');
    }
}
