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
        Schema::table('prd_material_logs', function (Blueprint $table) {
            $table->datetime('scan_start')->after('scan_in')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prd_material_logs', function (Blueprint $table) {
            $table->dropColumn('scan_start');
        });
    }
};
