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
        Schema::table('mould_change_logs', function (Blueprint $table) {
            $table->string('pic')->nullable()->after('item_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mould_change_logs', function (Blueprint $table) {
            $table->dropColumn('pic');
        });
    }
};
