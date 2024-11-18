<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PRD_MouldingJob extends Model
{
    use HasFactory;
    protected $table = 'prd_moulding_jobs';

    protected $fillable = [
        'user_id',
        'scan_start',
        'scan_finish',
    ];
}
