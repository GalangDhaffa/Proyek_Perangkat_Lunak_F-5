<?php

namespace Database\Factories;

use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollPeriod>
 */
class PayrollPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $id = fake()->uuid();

        return [
            'id' => $id,
            'uuid' => $id,
            'nama_periode' => 'November 2026',
            'tanggal_mulai' => '2026-11-01',
            'tanggal_akhir' => '2026-11-30',
            'tanggal_pembayaran' => '2026-12-01',
            'status' => 'Draf',
        ];
    }
}
