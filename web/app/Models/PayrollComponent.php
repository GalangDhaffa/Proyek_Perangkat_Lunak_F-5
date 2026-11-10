<?php

namespace App\Models;

use Database\Factories\PayrollComponentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollComponent extends Model
{
    /** @use HasFactory<PayrollComponentFactory> */
    use HasFactory;

    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'gaji_pokok',
        'bonus',
        'bpjs_karyawan',
        'bpjs_perusahaan',
        'komponen',
        'versi_komponen',
    ];

    protected function casts(): array
    {
        return [
            'gaji_pokok' => 'decimal:2',
            'bonus' => 'decimal:2',
            'bpjs_karyawan' => 'decimal:2',
            'bpjs_perusahaan' => 'decimal:2',
            'komponen' => 'array',
            'versi_komponen' => 'integer',
        ];
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }
}
