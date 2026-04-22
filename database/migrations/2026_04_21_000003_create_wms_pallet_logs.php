<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wms_pallet_logs', function (Blueprint $table) {
            $table->id();
            $table->string('pallet_id');
            $table->string('transaction_type'); // IN, OUT
            $table->foreignId('position_id')->nullable()->constrained('wms_positions');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wms_pallet_logs');
    }
};
