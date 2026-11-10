<?php

namespace Database\Factories;

use App\Models\PayrollComponent;
use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollComponent>
 */
class PayrollComponentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payroll_period_id' => PayrollPeriod::factory(),
            'employee_id' => fake()->bothify('EMP-###'),
            'gaji_pokok' => 6_500_000,
            'bonus' => 500_000,
            'bpjs_karyawan' => 65_000,
            'bpjs_perusahaan' => 260_000,
            'komponen' => [
                [
                    'nama' => 'Tunjangan',
                    'jenis' => 'pendapatan',
                    'nominal' => 500_000,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Potongan',
                    'jenis' => 'potongan',
                    'nominal' => 200_000,
                    'aktif' => true,
                ],
            ],
            'versi_komponen' => 1,
        ];
    }
}
