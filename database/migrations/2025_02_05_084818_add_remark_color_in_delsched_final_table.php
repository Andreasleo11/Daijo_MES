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
        Schema::table('delsched_final', function (Blueprint $table) {
            $table->string('remark')->nullable();
            $table->string('colour')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delsched_final', function (Blueprint $table) {
            $table->dropColumn('remark');
            $table->dropColumn('colour');
        });
    }
};
