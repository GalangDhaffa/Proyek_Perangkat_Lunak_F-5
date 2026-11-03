<?php

namespace Tests\Feature;

use App\Models\PayrollPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LeaveIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private string $validPeriodId = '01a1193d-2500-7c07-854b-8bf03c711dde';

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan periode November 2026 (30 hari inklusif)
        PayrollPeriod::create([
            'id' => $this->validPeriodId,
            'uuid' => $this->validPeriodId,
            'nama_periode' => 'November 2026',
            'tanggal_mulai' => '2026-11-01',
            'tanggal_akhir' => '2026-11-30',
            'tanggal_pembayaran' => '2026-12-01',
            'status' => 'Draf',
        ]);
    }

    /**
     * 1. Akses ditolak jika tanpa token (401).
     */
    public function test_access_denied_without_token(): void
    {
        $response = $this->getJson("/api/payroll/v1/periode/{$this->validPeriodId}/cuti?employeeId=EMP-001");

        $response->assertStatus(401)
            ->assertJson([
                'code' => 'UNAUTHORIZED',
                'message' => 'Token tidak tersedia atau tidak valid.',
            ]);
    }

    /**
     * 2. Akses ditolak jika token tidak valid (401).
     */
    public function test_access_denied_with_invalid_token(): void
    {
        $response = $this->withToken('token-salah-acak')
            ->getJson("/api/payroll/v1/periode/{$this->validPeriodId}/cuti?employeeId=EMP-001");

        $response->assertStatus(401)
            ->assertJson([
                'code' => 'UNAUTHORIZED',
            ]);
    }

    /**
     * 3. Akses ditolak untuk peran non-payroll (403): manajemen, keuangan, e2, karyawan, tenaga ahli.
     */
    public function test_access_denied_for_non_payroll_roles(): void
    {
        $forbiddenTokens = [
            'mock-manajemen',
            'mock-keuangan',
            'mock-e2',
            'mock-karyawan',
            'mock-tenaga-ahli',
        ];

        foreach ($forbiddenTokens as $token) {
            $response = $this->withToken($token)
                ->getJson("/api/payroll/v1/periode/{$this->validPeriodId}/cuti?employeeId=EMP-001");

            $response->assertStatus(403)
                ->assertJson([
                    'code' => 'FORBIDDEN',
                    'message' => 'Hanya petugas atau admin payroll yang memiliki izin mengambil data cuti karyawan.',
                ]);
        }
    }

    /**
     * 4. UUID periode salah format menghasilkan 400 INVALID_PERIODE_ID.
     */
    public function test_invalid_uuid_returns_400(): void
    {
        $response = $this->withToken('mock-payroll')
            ->getJson('/api/payroll/v1/periode/bukan-uuid/cuti?employeeId=EMP-001');

        $response->assertStatus(400)
            ->assertJson([
                'code' => 'INVALID_PERIODE_ID',
            ]);
    }

    /**
     * 5. UUID periode tidak ditemukan di database menghasilkan 404 PERIODE_NOT_FOUND.
     */
    public function test_non_existent_period_returns_404(): void
    {
        $nonExistent = '01a1193d-2500-7c99-854b-8bf03c711dde';
        $response = $this->withToken('mock-payroll')
            ->getJson("/api/payroll/v1/periode/{$nonExistent}/cuti?employeeId=EMP-001");

        $response->assertStatus(404)
            ->assertJson([
                'code' => 'PERIODE_NOT_FOUND',
            ]);
    }

    /**
     * 6. Parameter employeeId kosong menghasilkan 400 VALIDATION_ERROR.
     */
    public function test_missing_employee_id_returns_400(): void
    {
        $response = $this->withToken('mock-payroll')
            ->getJson("/api/payroll/v1/periode/{$this->validPeriodId}/cuti");

        $response->assertStatus(400)
            ->assertJson([
                'code' => 'VALIDATION_ERROR',
                'message' => 'Parameter employeeId wajib diisi.',
            ]);
    }

    /**
     * 7. Format employeeId tidak valid menghasilkan 400 INVALID_EMPLOYEE_ID.
     */
    public function test_invalid_employee_id_returns_400(): void
    {
        $response = $this->withToken('mock-payroll')
            ->getJson("/api/payroll/v1/periode/{$this->validPeriodId}/cuti?employeeId=EMP%20001!@#");

        $response->assertStatus(400)
            ->assertJson([
                'code' => 'INVALID_EMPLOYEE_ID',
            ]);
    }

    /**
     * 8. Rentang tanggal periode lebih dari 31 hari menghasilkan 400 RANGE_TOO_LONG.
     */
    public function test_range_too_long_returns_400(): void
    {
        $longPeriodId = '01a1193d-2500-7c88-854b-8bf03c711dde';
        PayrollPeriod::create([
            'id' => $longPeriodId,
            'uuid' => $longPeriodId,
            'nama_periode' => 'Oktober - November 2026',
            'tanggal_mulai' => '2026-10-01',
            'tanggal_akhir' => '2026-11-15', // 46 hari inklusif
            'tanggal_pembayaran' => '2026-11-16',
            'status' => 'Draf',
        ]);

        $response = $this->withToken('mock-payroll')
            ->getJson("/api/payroll/v1/periode/{$longPeriodId}/cuti?employeeId=EMP-001");

        $response->assertStatus(400)
            ->assertJson([
                'code' => 'RANGE_TOO_LONG',
            ]);
    }

    /**
     * 9. Pengambilan data cuti berhasil dengan HTTP Fake (200).
     */
    public function test_successful_leave_fetch_with_http_fake(): void
    {
        Http::fake([
            '*/api/v1/leave-status*' => Http::response([
                'employeeId' => 'EMP-001',
                'items' => [
                    [
                        'leaveRequestId' => '0192f1b2-3c4d-7e5f-9a6b-7c8d9e0f1a2b',
                        'leaveType' => 'ANNUAL',
                        'startDate' => '2026-11-16',
                        'endDate' => '2026-11-18',
                        'workingDays' => 3,
                        'workingDaysInRange' => 3,
                        'status' => 'APPROVED',
                        'paid' => true,
                        'approvedAt' => '2026-10-27T02:00:04Z',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withToken('mock-payroll')
            ->getJson("/api/payroll/v1/periode/{$this->validPeriodId}/cuti?employeeId=EMP-001");

        $response->assertStatus(200)
            ->assertJson([
                'periodeId' => $this->validPeriodId,
                'namaPeriode' => 'November 2026',
                'tanggalMulai' => '2026-11-01',
                'tanggalAkhir' => '2026-11-30',
                'employeeId' => 'EMP-001',
                'from' => '2026-11-01',
                'to' => '2026-11-30',
                'totalCuti' => 1,
                'totalHariCuti' => 3,
            ]);

        $this->assertCount(1, $response->json('items'));
        $this->assertEquals('ANNUAL', $response->json('items.0.leaveType'));
        $this->assertTrue($response->json('items.0.paid'));
        $this->assertEquals(3, $response->json('items.0.workingDaysInRange'));
    }

    /**
     * 10. Pengambilan data cuti kosong dengan HTTP Fake (200).
     */
    public function test_empty_leave_fetch_with_http_fake(): void
    {
        Http::fake([
            '*/api/v1/leave-status*' => Http::response([
                'employeeId' => 'EMP-001',
                'items' => [],
            ], 200),
        ]);

        $response = $this->withToken('mock-admin-payroll')
            ->getJson("/api/payroll/v1/periode/{$this->validPeriodId}/cuti?employeeId=EMP-001");

        $response->assertStatus(200)
            ->assertJson([
                'employeeId' => 'EMP-001',
                'items' => [],
                'totalCuti' => 0,
                'totalHariCuti' => 0,
            ]);
    }

    /**
     * 11. Kegagalan koneksi F-4 ditangani sebagai 503 SERVICE_UNAVAILABLE.
     */
    public function test_f4_connection_failure_handled_as_503(): void
    {
        Http::fake([
            '*/api/v1/leave-status*' => function () {
                throw new ConnectionException('Connection refused to http://localhost:3000');
            },
        ]);

        $response = $this->withToken('mock-payroll')
            ->getJson("/api/payroll/v1/periode/{$this->validPeriodId}/cuti?employeeId=EMP-001");

        $response->assertStatus(503)
            ->assertJson([
                'code' => 'SERVICE_UNAVAILABLE',
                'message' => 'Layanan cuti F-4 tidak dapat dihubungi. Pastikan layanan F-4 berjalan.',
            ]);
    }

    /**
     * 12. Respons 404 dari F-4 (Karyawan tidak ditemukan) diteruskan secara bersih.
     */
    public function test_f4_employee_not_found_forwarded_as_404(): void
    {
        Http::fake([
            '*/api/v1/leave-status*' => Http::response([
                'code' => 'EMPLOYEE_NOT_FOUND',
                'message' => 'Karyawan tidak ditemukan di referensi karyawan.',
            ], 404),
        ]);

        $response = $this->withToken('mock-payroll')
            ->getJson("/api/payroll/v1/periode/{$this->validPeriodId}/cuti?employeeId=EMP-999");

        $response->assertStatus(404)
            ->assertJson([
                'code' => 'EMPLOYEE_NOT_FOUND',
                'message' => 'Karyawan tidak ditemukan di referensi karyawan.',
            ]);
    }

    /**
     * 13. Respons 401/403 dari upstream F-4 ditangani sebagai 502 UPSTREAM_AUTH_ERROR tanpa membocorkan token.
     */
    public function test_f4_upstream_auth_error_handled_as_502(): void
    {
        Http::fake([
            '*/api/v1/leave-status*' => Http::response([
                'code' => 'UNAUTHORIZED',
                'message' => 'Token tidak valid.',
            ], 401),
        ]);

        $response = $this->withToken('mock-payroll')
            ->getJson("/api/payroll/v1/periode/{$this->validPeriodId}/cuti?employeeId=EMP-001");

        $response->assertStatus(502)
            ->assertJson([
                'code' => 'UPSTREAM_AUTH_ERROR',
            ]);
    }

    /**
     * 14. Pengujian langsung ke layanan F-4 aktual jika server berjalan di http://localhost:3000.
     */
    public function test_live_f4_service_integration_if_reachable(): void
    {
        $isLive = false;
        try {
            $health = @file_get_contents('http://localhost:3000/health');
            if ($health !== false) {
                $isLive = true;
            }
        } catch (\Throwable) {
            $isLive = false;
        }

        if (! $isLive) {
            $this->markTestSkipped('Layanan F-4 tidak aktif pada http://localhost:3000.');
        }

        // Jangan gunakan Http::fake di sini; lakukan pemanggilan langsung ke F-4
        $response = $this->withToken('mock-payroll')
            ->getJson("/api/payroll/v1/periode/{$this->validPeriodId}/cuti?employeeId=EMP-001");

        $response->assertStatus(200)
            ->assertJson([
                'periodeId' => $this->validPeriodId,
                'employeeId' => 'EMP-001',
                'totalCuti' => 1,
                'totalHariCuti' => 3,
            ]);

        $items = $response->json('items');
        $this->assertNotEmpty($items);
        $this->assertEquals('ANNUAL', $items[0]['leaveType']);
        $this->assertEquals('APPROVED', $items[0]['status']);
        $this->assertTrue($items[0]['paid']);
        $this->assertEquals('2026-11-16', $items[0]['startDate']);
        $this->assertEquals('2026-11-18', $items[0]['endDate']);
    }
}
