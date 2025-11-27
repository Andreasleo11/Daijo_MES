<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_item_photos', function (Blueprint $table) {
            $table->id();

            $table->string('item_code')->index();
            $table->string('item_description')->nullable();
            $table->integer('standard_packaging')->nullable();

            // path foto
            $table->string('photo_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_item_photos');
    }
};
