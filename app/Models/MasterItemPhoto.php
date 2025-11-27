<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterItemPhoto extends Model
{
    protected $table = 'master_item_photos';

    protected $fillable = [
        'item_code',
        'item_description',
        'standard_packaging',
        'photo_path',
    ];

    // Relasi ke master_list_items (optional)
    public function item()
    {
        return $this->belongsTo(MasterListItem::class, 'item_code', 'item_code');
    }
}
