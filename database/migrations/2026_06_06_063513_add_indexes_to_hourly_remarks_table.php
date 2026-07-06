<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hourly_remarks', function (Blueprint $table) {
            $table->index('dic_id', 'hourly_remarks_dic_id_idx');
            $table->index('created_at', 'hourly_remarks_created_at_idx');
            $table->index('pic', 'hourly_remarks_pic_idx');
        });
    }

    public function down(): void
    {
        Schema::table('hourly_remarks', function (Blueprint $table) {
            $table->dropIndex('hourly_remarks_dic_id_idx');
            $table->dropIndex('hourly_remarks_created_at_idx');
            $table->dropIndex('hourly_remarks_pic_idx');
        });
    }
};
