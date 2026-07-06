<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WmsRack extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['whse_id', 'rack_code', 'x_pos', 'y_pos', 'width', 'height', 'orientation'];

    public function warehouse()
    {
        return $this->belongsTo(WmsWarehouse::class, 'whse_id');
    }

    public function positions()
    {
        return $this->hasMany(WmsPosition::class, 'rack_id');
    }
}
