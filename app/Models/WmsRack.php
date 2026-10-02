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

    public function getDistanceScoreAttribute(): int
    {
        $whseCode = strtoupper($this->warehouse?->whse_code ?? '');
        $exitLoc = $this->warehouse?->exit_location;

        if ($exitLoc === 'BOTTOM_LEFT' || str_contains($whseCode, 'GUA')) {
            $exitX = 180;
            $exitY = 730;
        } elseif ($exitLoc === 'BOTTOM_CENTER' || str_contains($whseCode, '06') || str_contains($whseCode, 'J06')) {
            $exitX = 500;
            $exitY = 730;
        } elseif ($exitLoc === 'TOP_LEFT') {
            $exitX = 180;
            $exitY = 100;
        } elseif ($exitLoc === 'TOP_RIGHT') {
            $exitX = 880;
            $exitY = 100;
        } elseif ($exitLoc === 'TOP_CENTER') {
            $exitX = 500;
            $exitY = 100;
        } else {
            // Default: BOTTOM_RIGHT (e.g. GUB)
            $exitX = 880;
            $exitY = 730;
        }

        return abs(($this->x_pos ?: 500) - $exitX) + abs(($this->y_pos ?: 500) - $exitY);
    }
}
