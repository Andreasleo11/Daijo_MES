<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterListItem extends Model
{
    use HasFactory;

    public function files()
    {
        return $this->hasMany(File::class, 'item_code', 'item_code');
    }

    public function photo()
    {
        return $this->hasOne(MasterItemPhoto::class, 'item_code', 'item_code');
    }

    public function customer()
    {
        return $this->belongsTo(
            MasterCustomerDelivery::class,
            'customer_code',   // foreign key di master_list_items
            'customer_code'    // owner key di master_customer_delivery
        );
    }
}
