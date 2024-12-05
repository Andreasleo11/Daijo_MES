<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PRD_BillOfMaterialParent extends Model
{
    use HasFactory;
    protected $table = 'prd_bill_of_material_parents';

    protected $fillable = [
        'item_code',
        'item_description',
        'type',
        'customer',
    ];

    public function child()
    {
        return $this->hasMany(PRD_BillOfMaterialChild::class, 'parent_id');
    }

}
