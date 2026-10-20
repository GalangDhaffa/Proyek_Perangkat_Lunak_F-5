<?php

namespace App\Services;

class RekapBiayaFixtureService
{
    /**
     * Fixture demo untuk endpoint rekap biaya.
     * Sumber data ini bersifat simulasi lokal dan bukan hasil integrasi modul lain.
     */
    public function getByPeriodoId(string $periodeId): ?array
    {
        $fixtures = [
            '01a1193d-2500-7c04-854b-8bf03c711dde' => [
                'periodeId' => '01a1193d-2500-7c04-854b-8bf03c711dde',
                'namaPeriode' => 'Oktober 2026 (contoh fiktif)',
                'tanggalMulai' => '2026-10-01',
                'tanggalAkhir' => '2026-10-31',
                'statusPeriode' => 'Disetujui',
                'adaData' => true,
                'jumlahKaryawan' => 1,
                'totalPendapatan' => 7750000.0,
                'totalBonus' => 750000.0,
                'totalPotongan' => 200000.0,
                'totalBpjsKaryawan' => 65000.0,
                'totalGajiBersih' => 7485000.0,
                'totalBpjsPerusahaan' => 260000.0,
                'totalTermin' => 25000000.0,
                'jumlahTermin' => 1,
                'totalBiaya' => null,
                'diperbaruiPada' => '2026-10-13T19:43:00+07:00',
            ],
            '01a1193d-2500-7c05-854b-8bf03c711dde' => [
                'periodeId' => '01a1193d-2500-7c05-854b-8bf03c711dde',
                'namaPeriode' => 'Periode kosong (contoh fiktif)',
                'tanggalMulai' => '2026-10-01',
                'tanggalAkhir' => '2026-10-31',
                'statusPeriode' => 'Draf',
                'adaData' => false,
                'jumlahKaryawan' => 0,
                'totalPendapatan' => 0,
                'totalBonus' => 0,
                'totalPotongan' => 0,
                'totalBpjsKaryawan' => 0,
                'totalGajiBersih' => 0,
                'totalBpjsPerusahaan' => 0,
                'totalTermin' => 0,
                'jumlahTermin' => 0,
                'totalBiaya' => null,
                'diperbaruiPada' => '2026-10-13T19:43:00+07:00',
            ],
            '01a1193d-2500-7c06-854b-8bf03c711dde' => [
                'periodeId' => '01a1193d-2500-7c06-854b-8bf03c711dde',
                'namaPeriode' => 'Termin saja (contoh fiktif)',
                'tanggalMulai' => '2026-10-01',
                'tanggalAkhir' => '2026-10-31',
                'statusPeriode' => 'Draf',
                'adaData' => true,
                'jumlahKaryawan' => 0,
                'totalPendapatan' => 0,
                'totalBonus' => 0,
                'totalPotongan' => 0,
                'totalBpjsKaryawan' => 0,
                'totalGajiBersih' => 0,
                'totalBpjsPerusahaan' => 0,
                'totalTermin' => 25000000.0,
                'jumlahTermin' => 1,
                'totalBiaya' => null,
                'diperbaruiPada' => '2026-10-13T19:43:00+07:00',
            ],
        ];

        // Pencarian case-insensitive: normalisasi UUID ke huruf kecil.
        // UUID huruf besar/kecil dianggap sama sesuai kontrak dan mock Python.
        $normalizedId = strtolower($periodeId);

        return $fixtures[$normalizedId] ?? null;
    }

    public function isValidUuid(string $value): bool
    {
        return preg_match('/^[0-9a-fA-F]{8}-(?:[0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}$/', $value) === 1;
    }
}
