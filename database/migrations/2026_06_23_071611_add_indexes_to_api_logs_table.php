<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('api_name');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['api_name']);
            $table->dropIndex(['status']);
        });
    }
};
