<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
     protected $fillable = [
        'api_name',
        'method',
        'endpoint',
        'request_payload',
        'response_payload',
        'status_code',
        'status',
        'message',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];
}
