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
        Schema::create('store_box_details', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('part_no');
            $blueprint->integer('label');
            $blueprint->string('status')->default('active'); // 'active' or 'not active'
            $blueprint->text('remark')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_box_details');
    }
};
