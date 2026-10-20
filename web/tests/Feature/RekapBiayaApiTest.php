<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class RekapBiayaApiTest extends TestCase
{
    use WithoutMiddleware;

    public function test_rekap_biaya_success_payload_matches_schema_for_payroll_and_termin(): void
    {
        $response = $this->withToken('mock-e2')->getJson('/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde');

        $response->assertOk();
        $response->assertJsonPath('periodeId', '01a1193d-2500-7c04-854b-8bf03c711dde');
        $response->assertJsonPath('adaData', true);
        $response->assertJsonPath('jumlahKaryawan', 1);
        $this->assertEquals(7750000, $response->json('totalPendapatan'));
        $this->assertEquals(750000, $response->json('totalBonus'));
        $this->assertEquals(200000, $response->json('totalPotongan'));
        $this->assertEquals(65000, $response->json('totalBpjsKaryawan'));
        $this->assertEquals(7485000, $response->json('totalGajiBersih'));
        $this->assertEquals(260000, $response->json('totalBpjsPerusahaan'));
        $this->assertEquals(25000000, $response->json('totalTermin'));
        $response->assertJsonPath('jumlahTermin', 1);
        $response->assertJsonPath('totalBiaya', null);

        $payload = $response->json();
        $this->assertCount(17, $payload);
        $this->assertSame([
            'periodeId',
            'namaPeriode',
            'tanggalMulai',
            'tanggalAkhir',
            'statusPeriode',
            'adaData',
            'jumlahKaryawan',
            'totalPendapatan',
            'totalBonus',
            'totalPotongan',
            'totalBpjsKaryawan',
            'totalGajiBersih',
            'totalBpjsPerusahaan',
            'totalTermin',
            'jumlahTermin',
            'totalBiaya',
            'diperbaruiPada',
        ], array_keys($payload));
    }

    public function test_rekap_biaya_with_empty_period_returns_status_200_and_zero_values(): void
    {
        $response = $this->withToken('mock-keuangan')->getJson('/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c05-854b-8bf03c711dde');

        $response->assertOk();
        $response->assertJsonPath('adaData', false);
        $response->assertJsonPath('jumlahKaryawan', 0);
        $response->assertJsonPath('totalPendapatan', 0);
        $response->assertJsonPath('totalTermin', 0);
        $response->assertJsonPath('totalBiaya', null);
    }

    public function test_rekap_biaya_with_termin_only_returns_aggregates_for_termin_only(): void
    {
        $response = $this->withToken('mock-manajemen')->getJson('/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c06-854b-8bf03c711dde');

        $response->assertOk();
        $response->assertJsonPath('adaData', true);
        $response->assertJsonPath('jumlahKaryawan', 0);
        $response->assertJsonPath('totalPendapatan', 0);
        $this->assertEquals(25000000, $response->json('totalTermin'));
        $response->assertJsonPath('jumlahTermin', 1);
        $response->assertJsonPath('totalBiaya', null);
    }

    public function test_rekap_biaya_requires_valid_authentication_token(): void
    {
        $response = $this->getJson('/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde');

        $response->assertStatus(401);
        $response->assertExactJson([
            'code' => 'UNAUTHORIZED',
            'message' => 'Token tidak tersedia atau tidak valid.',
            'details' => [],
        ]);
    }

    public function test_rekap_biaya_rejects_forbidden_role(): void
    {
        $response = $this->withToken('mock-karyawan')->getJson('/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde');

        $response->assertStatus(403);
        $response->assertExactJson([
            'code' => 'FORBIDDEN',
            'message' => 'Pemanggil tidak memiliki izin membaca rekap biaya.',
            'details' => [],
        ]);
    }

    public function test_rekap_biaya_rejects_invalid_period_id(): void
    {
        $response = $this->withToken('mock-e2')->getJson('/api/payroll/v1/rekap-biaya?periodeId=invalid');

        $response->assertStatus(400);
        $response->assertExactJson([
            'code' => 'INVALID_PERIODE_ID',
            'message' => 'periodeId wajib diisi satu kali dengan format UUID yang benar.',
            'details' => [
                [
                    'field' => 'periodeId',
                    'message' => 'Gunakan satu UUID dalam format 8-4-4-4-12.',
                ],
            ],
        ]);
    }

    public function test_rekap_biaya_returns_404_for_unknown_period(): void
    {
        $response = $this->withToken('mock-e2')->getJson('/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c07-854b-8bf03c711dde');

        $response->assertStatus(404);
        $response->assertExactJson([
            'code' => 'PERIODE_NOT_FOUND',
            'message' => 'Periode payroll tidak ditemukan.',
            'details' => [
                [
                    'field' => 'periodeId',
                    'message' => 'UUID periode tidak dikenal.',
                ],
            ],
        ]);
    }
}
