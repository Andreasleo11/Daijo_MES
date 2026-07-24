<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MwhIncomingHeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mwh_incoming_headers';

    protected $fillable = [
        'document_no',
        'supplier_name',
        'po_number',
        'arrival_date',
        'remarks',
        'created_at',
    ];

    protected $casts = [
        'arrival_date' => 'date',
    ];

    public function pallets()
    {
        return $this->hasMany(MwhPallet::class, 'incoming_header_id');
    }
}
