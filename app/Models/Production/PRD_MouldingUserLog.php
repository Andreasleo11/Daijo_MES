<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PRD_MouldingUserLog extends Model
{
    use HasFactory;
    protected $table = 'prd_moulding_user_logs';

    protected $fillable = [
        'material_log_id',
        'username',
        'shift',
    ];
    
    public function userlog()
    {
        return $this->belongsTo(PRDMaterialLog::class, 'material_log_id');
    }
}
