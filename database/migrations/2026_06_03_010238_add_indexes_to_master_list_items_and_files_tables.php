<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_list_items', function (Blueprint $table) {
            $table->index('item_code');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->index('item_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_list_items', function (Blueprint $table) {
            $table->dropIndex(['item_code']);
        });

        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex(['item_code']);
        });
    }
};
