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
        Schema::create('payroll_components', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('payroll_period_id')
                ->constrained('payroll_periods')
                ->cascadeOnDelete();
            $table->string('employee_id', 64);
            $table->decimal('gaji_pokok', 15, 2);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('bpjs_karyawan', 15, 2)->default(0);
            $table->decimal('bpjs_perusahaan', 15, 2)->default(0);
            $table->json('komponen');
            $table->unsignedInteger('versi_komponen')->default(1);
            $table->timestamps();

            $table->unique(['payroll_period_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_components');
    }
};
