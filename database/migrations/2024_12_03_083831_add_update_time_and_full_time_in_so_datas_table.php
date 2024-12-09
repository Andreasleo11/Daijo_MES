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
            $table->date('update_date');
            $table->integer('update_fulltime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('so_datas', function (Blueprint $table) {
            $table->dropColumn('update_date');
            $table->dropColumn('update_fulltime');
        });
    }
};
