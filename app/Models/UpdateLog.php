<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpdateLog extends Model
{
    use HasFactory;

    protected $table = 'update_logs';

    // Allow mass assignment for these fields
    protected $fillable = ['last_upload_time'];
}
