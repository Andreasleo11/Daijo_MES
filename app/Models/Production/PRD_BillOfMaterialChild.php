<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PRD_BillOfMaterialChild extends Model
{
    use HasFactory;
    protected $table = 'prd_bill_of_material_childs';

    protected $fillable = [
        'parent_id',
        'item_code',
        'item_description',
        'quantity',
        'measure',
    ];
}
