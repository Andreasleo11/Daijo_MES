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
        Schema::table('master_list_items', function (Blueprint $table) {
            $table->string('setup_time_minute')->nullable()->after('standart_packaging_list');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_list_items', function (Blueprint $table) {
            $table->dropColumn('setup_time_minute');
        });
    }
};
