<?php

namespace Database\Seeders;

use App\Models\PayrollPeriod;
use Illuminate\Database\Seeder;

class PayrollPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periods = [
            [
                'id' => '01a1193d-2500-7c01-854b-8bf03c711dde',
                'uuid' => '01a1193d-2500-7c01-854b-8bf03c711dde',
                'nama_periode' => 'September 2026',
                'tanggal_mulai' => '2026-09-01',
                'tanggalAkhir' => '2026-09-30',
                'tanggal_pembayaran' => '2026-10-01',
                'status' => 'Draf',
            ],
            [
                'id' => '01a1193d-2500-7c04-854b-8bf03c711dde',
                'uuid' => '01a1193d-2500-7c04-854b-8bf03c711dde',
                'nama_periode' => 'Oktober 2026 (contoh fiktif)',
                'tanggal_mulai' => '2026-10-01',
                'tanggalAkhir' => '2026-10-31',
                'tanggal_pembayaran' => '2026-11-01',
                'status' => 'Disetujui',
            ],
            [
                'id' => '01a1193d-2500-7c05-854b-8bf03c711dde',
                'uuid' => '01a1193d-2500-7c05-854b-8bf03c711dde',
                'nama_periode' => 'Periode kosong (contoh fiktif)',
                'tanggal_mulai' => '2026-10-01',
                'tanggalAkhir' => '2026-10-31',
                'tanggal_pembayaran' => '2026-11-01',
                'status' => 'Draf',
            ],
            [
                'id' => '01a1193d-2500-7c06-854b-8bf03c711dde',
                'uuid' => '01a1193d-2500-7c06-854b-8bf03c711dde',
                'nama_periode' => 'Termin saja (contoh fiktif)',
                'tanggal_mulai' => '2026-10-01',
                'tanggalAkhir' => '2026-10-31',
                'tanggal_pembayaran' => '2026-11-01',
                'status' => 'Draf',
            ],
        ];

        foreach ($periods as $item) {
            PayrollPeriod::updateOrCreate(
                ['id' => $item['id']],
                [
                    'uuid' => $item['uuid'],
                    'nama_periode' => $item['nama_periode'],
                    'tanggal_mulai' => $item['tanggal_mulai'],
                    'tanggal_akhir' => $item['tanggalAkhir'],
                    'tanggal_pembayaran' => $item['tanggal_pembayaran'],
                    'status' => $item['status'],
                ]
            );
        }
    }
}
