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
        Schema::create('second_process_reports', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('unit_line');
            $table->string('shift');
            $table->string('process_prod');
            $table->string('model');
            $table->string('part_number');
            $table->string('part_name');
            $table->string('customer');
            $table->integer('target_per_hour')->default(0);
            $table->integer('jml_input_wip')->default(0);
            $table->integer('repairan')->default(0);
            $table->integer('jumlah_output')->default(0);
            $table->integer('jumlah_ok')->default(0);
            $table->integer('jumlah_ng')->default(0);
            $table->decimal('ng_prosentase', 5, 2)->default(0.00);
            $table->integer('jml_ng_lebur')->default(0);
            $table->text('next_production_schedule')->nullable();
            $table->text('absent_employees')->nullable();
            $table->text('production_notes')->nullable();
            $table->string('created_by_name')->nullable();
            $table->string('pqc_name')->nullable();
            $table->string('acknowledged_by_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('second_process_reports');
    }
};
