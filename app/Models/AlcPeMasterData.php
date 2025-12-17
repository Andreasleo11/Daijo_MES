<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlcPeMasterData extends Model
{
    use HasFactory;

    // Nama tabelnya
    protected $table = 'alc_pe_master_data';

    // Kolom yang boleh diisi massal (fillable)
    protected $fillable = [
        'part_code',
        'part_name',
        'qad',
        'ukuran_label',
        'alc_code',
        'project_code',
    ];
}
