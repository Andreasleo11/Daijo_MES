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
        Schema::table('so_datas', function (Blueprint $table) {
            $table->boolean('is_finish')->nullable();
            $table->boolean('is_done')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('so_datas', function (Blueprint $table) {
            $table->dropColumn('is_finish');
            $table->dropColumn('is_done');
        });
    }
};
