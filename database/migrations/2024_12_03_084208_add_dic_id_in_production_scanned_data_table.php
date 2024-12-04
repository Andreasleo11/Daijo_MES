<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('production_scanned_data', function (Blueprint $table) {
            $table->integer('dic_id')->after('spk_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_scanned_data', function (Blueprint $table) {
            $table->dropColumn('dic_id');
        });
    }
};
