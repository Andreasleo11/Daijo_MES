<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouldChangeLog extends Model
{
    use HasFactory;

    protected $table = 'mould_change_logs';

    protected $fillable = [
        'user_id',
        'item_code',
        'end_time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function masterListItem()
    {
        return $this->belongsTo(MasterListItem::class, 'item_code', 'item_code'); // Adjust if the foreign key or field name is different
    }
}
