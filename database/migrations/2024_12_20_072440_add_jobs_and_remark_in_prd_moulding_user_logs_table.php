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
        Schema::table('prd_moulding_user_logs', function (Blueprint $table) {
            $table->string('jobs')->after('shift')->nullable();
            $table->string('remark')->after('shift')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prd_moulding_user_logs', function (Blueprint $table) {
            $table->dropColumn('jobs');
            $table->dropColumn('remark');
        });
    }
};
