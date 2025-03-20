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
        Schema::table('operator_user', function (Blueprint $table) {
            $table->string('department')->nullable()->after('profile_picture');
            $table->string('position')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operator_user', function (Blueprint $table) {
            $table->dropColumn('department');
            $table->dropColumn('position');
        });
    }
};
