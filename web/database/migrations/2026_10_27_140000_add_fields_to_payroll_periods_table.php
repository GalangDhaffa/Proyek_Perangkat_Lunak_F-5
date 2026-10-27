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
        Schema::table('payroll_periods', function (Blueprint $table) {
            if (! Schema::hasColumn('payroll_periods', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique();
            }
            if (! Schema::hasColumn('payroll_periods', 'nama_periode')) {
                $table->string('nama_periode')->nullable();
            }
            if (! Schema::hasColumn('payroll_periods', 'tanggal_mulai')) {
                $table->date('tanggal_mulai')->nullable();
            }
            if (! Schema::hasColumn('payroll_periods', 'tanggal_akhir')) {
                $table->date('tanggal_akhir')->nullable();
            }
            if (! Schema::hasColumn('payroll_periods', 'tanggal_pembayaran')) {
                $table->date('tanggal_pembayaran')->nullable();
            }
            if (! Schema::hasColumn('payroll_periods', 'status')) {
                $table->string('status', 50)->default('Draf');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe down
    }
};
