<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wms_racks', function (Blueprint $table) {
            $table->integer('x_pos')->default(0);
            $table->integer('y_pos')->default(0);
            $table->integer('width')->default(200);
            $table->integer('height')->default(100);
            $table->string('orientation')->default('HORIZONTAL'); // HORIZONTAL, VERTICAL
        });
    }

    public function down(): void
    {
        Schema::table('wms_racks', function (Blueprint $table) {
            $table->dropColumn(['x_pos', 'y_pos', 'width', 'height', 'orientation']);
        });
    }
};
