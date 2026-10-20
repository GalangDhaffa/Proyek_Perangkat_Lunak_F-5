<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class RekapBiayaApiTest extends TestCase
{
    use WithoutMiddleware;

    // ──────────────────────────────────────────────
    // Skenario Sukses (200)
    // ──────────────────────────────────────────────

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

        // Pastikan tepat 17 field dan urutan sesuai kontrak
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

    // ──────────────────────────────────────────────
    // UUID huruf besar (case-insensitive sesuai kontrak)
    // ──────────────────────────────────────────────

    public function test_rekap_biaya_uuid_uppercase_finds_same_period(): void
    {
        // UUID huruf besar harus menemukan periode yang sama dengan huruf kecil
        $response = $this->withToken('mock-manajemen')->getJson('/api/payroll/v1/rekap-biaya?periodeId=01A1193D-2500-7C04-854B-8BF03C711DDE');

        $response->assertOk();
        $response->assertJsonPath('periodeId', '01a1193d-2500-7c04-854b-8bf03c711dde');
        $response->assertJsonPath('adaData', true);
    }

    public function test_rekap_biaya_uuid_mixed_case_finds_same_period(): void
    {
        // UUID campuran huruf besar dan kecil harus menemukan periode yang sama
        $response = $this->withToken('mock-e2')->getJson('/api/payroll/v1/rekap-biaya?periodeId=01a1193D-2500-7C04-854b-8BF03c711dde');

        $response->assertOk();
        $response->assertJsonPath('adaData', true);
    }

    // ──────────────────────────────────────────────
    // Skenario 401 - Token tidak valid/tidak tersedia
    // ──────────────────────────────────────────────

    public function test_rekap_biaya_returns_401_without_token(): void
    {
        // Request tanpa Authorization header sama sekali
        $response = $this->getJson('/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde');

        $response->assertStatus(401);
        $response->assertExactJson([
            'code' => 'UNAUTHORIZED',
            'message' => 'Token tidak tersedia atau tidak valid.',
            'details' => [],
        ]);
    }

    public function test_rekap_biaya_returns_401_with_invalid_token(): void
    {
        // Token yang tidak dikenali backend (bukan salah satu mock token)
        $response = $this->withToken('invalid-token-demo')->getJson('/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde');

        $response->assertStatus(401);
        $response->assertExactJson([
            'code' => 'UNAUTHORIZED',
            'message' => 'Token tidak tersedia atau tidak valid.',
            'details' => [],
        ]);
    }

    public function test_rekap_biaya_returns_401_with_empty_bearer_string(): void
    {
        // Bearer dengan nilai kosong (edge case)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ',
        ])->getJson('/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde');

        $response->assertStatus(401);
        $response->assertJsonPath('code', 'UNAUTHORIZED');
    }

    // ──────────────────────────────────────────────
    // Skenario 403 - Token valid, peran tidak diizinkan
    // ──────────────────────────────────────────────

    public function test_rekap_biaya_rejects_karyawan_role_with_403(): void
    {
        // mock-karyawan dikenali backend sebagai peran 'karyawan' yang tidak diizinkan
        $response = $this->withToken('mock-karyawan')->getJson('/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde');

        $response->assertStatus(403);
        $response->assertExactJson([
            'code' => 'FORBIDDEN',
            'message' => 'Pemanggil tidak memiliki izin membaca rekap biaya.',
            'details' => [],
        ]);
    }

    public function test_rekap_biaya_rejects_tenaga_ahli_role_with_403(): void
    {
        // mock-tenaga-ahli juga peran yang tidak diizinkan
        $response = $this->withToken('mock-tenaga-ahli')->getJson('/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde');

        $response->assertStatus(403);
        $response->assertJsonPath('code', 'FORBIDDEN');
    }

    // ──────────────────────────────────────────────
    // Skenario 400 - periodeId tidak valid
    // ──────────────────────────────────────────────

    public function test_rekap_biaya_returns_400_for_invalid_uuid_format(): void
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

    public function test_rekap_biaya_returns_400_when_periode_id_is_missing(): void
    {
        // Tanpa parameter periodeId sama sekali
        $response = $this->withToken('mock-manajemen')->getJson('/api/payroll/v1/rekap-biaya');

        $response->assertStatus(400);
        $response->assertJsonPath('code', 'INVALID_PERIODE_ID');
    }

    public function test_rekap_biaya_returns_400_when_periode_id_is_empty_string(): void
    {
        // periodeId ada tapi nilainya string kosong
        $response = $this->withToken('mock-manajemen')->getJson('/api/payroll/v1/rekap-biaya?periodeId=');

        $response->assertStatus(400);
        $response->assertJsonPath('code', 'INVALID_PERIODE_ID');
    }

    public function test_rekap_biaya_returns_400_when_periode_id_is_duplicated(): void
    {
        // periodeId muncul lebih dari satu kali di query string
        $response = $this->withToken('mock-manajemen')->getJson(
            '/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde&periodeId=01a1193d-2500-7c05-854b-8bf03c711dde'
        );

        $response->assertStatus(400);
        $response->assertJsonPath('code', 'INVALID_PERIODE_ID');
    }

    // ──────────────────────────────────────────────
    // Skenario 404 - UUID valid tapi tidak dikenal
    // ──────────────────────────────────────────────

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

    public function test_rekap_biaya_returns_404_for_all_zeros_uuid(): void
    {
        // UUID nil (semua nol) — format valid tapi tidak terdaftar
        $response = $this->withToken('mock-manajemen')->getJson('/api/payroll/v1/rekap-biaya?periodeId=00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404);
        $response->assertJsonPath('code', 'PERIODE_NOT_FOUND');
    }

    // ──────────────────────────────────────────────
    // Pastikan gagal tidak menyisakan hasil sukses
    // ──────────────────────────────────────────────

    public function test_error_response_does_not_contain_success_fields(): void
    {
        // Respons error tidak boleh memuat field rekap biaya
        $response = $this->withToken('mock-karyawan')->getJson('/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde');

        $response->assertStatus(403);
        $payload = $response->json();

        // Pastikan tidak ada field rekap biaya dalam respons error
        $this->assertArrayNotHasKey('periodeId', $payload);
        $this->assertArrayNotHasKey('totalPendapatan', $payload);
        $this->assertArrayNotHasKey('adaData', $payload);
    }

    public function test_unauthorized_response_does_not_contain_success_fields(): void
    {
        $response = $this->getJson('/api/payroll/v1/rekap-biaya?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde');

        $response->assertStatus(401);
        $payload = $response->json();

        $this->assertArrayNotHasKey('periodeId', $payload);
        $this->assertArrayNotHasKey('totalPendapatan', $payload);
    }
}
