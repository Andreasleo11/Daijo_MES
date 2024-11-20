<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PRD_MouldingJob extends Model
{
    use HasFactory;
    protected $table = 'prd_moulding_jobs';

    protected $fillable = [
        'user_id',
        'material_id',
        'scan_start',
        'scan_finish',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function material()
    {
        return $this->belongsTo(PRD_BillOfMaterialChild::class, 'material_id');
    }
}
