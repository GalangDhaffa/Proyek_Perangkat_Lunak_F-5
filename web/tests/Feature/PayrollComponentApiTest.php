<?php

namespace Tests\Feature;

use App\Models\PayrollComponent;
use App\Models\PayrollPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayrollComponentApiTest extends TestCase
{
    use RefreshDatabase;

    private const PERIODO_ID = '01a1193d-2500-7c88-854b-8bf03c711dde';

    protected function setUp(): void
    {
        parent::setUp();

        PayrollPeriod::create([
            'id' => self::PERIODO_ID,
            'uuid' => self::PERIODO_ID,
            'nama_periode' => 'November 2026',
            'tanggal_mulai' => '2026-11-01',
            'tanggal_akhir' => '2026-11-30',
            'tanggal_pembayaran' => '2026-12-01',
            'status' => 'Draf',
        ]);
    }

    public function test_put_component_replaces_all_fields_in_one_row(): void
    {
        $firstPayload = $this->componentPayload();
        $this->withToken('mock-payroll')
            ->putJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/komponen', $firstPayload)
            ->assertOk()
            ->assertJsonPath('employeeId', 'EMP-001')
            ->assertJsonPath('versiKomponen', 1);

        $updatedPayload = $this->componentPayload();
        $updatedPayload['gajiPokok'] = 7_000_000;
        $updatedPayload['versiKomponen'] = 2;
        $updatedPayload['komponen'][0]['nominal'] = 600_000;

        $response = $this->withToken('mock-payroll')
            ->putJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/komponen', $updatedPayload);

        $response->assertOk()
            ->assertJsonPath('gajiPokok', 7_000_000)
            ->assertJsonPath('komponen.0.nominal', 600_000)
            ->assertJsonPath('versiKomponen', 2);

        $this->assertSame(
            1,
            PayrollComponent::query()
                ->where('payroll_period_id', self::PERIODO_ID)
                ->where('employee_id', 'EMP-001')
                ->count(),
        );
        $this->assertDatabaseHas('payroll_components', [
            'payroll_period_id' => self::PERIODO_ID,
            'employee_id' => 'EMP-001',
            'gaji_pokok' => '7000000.00',
            'versi_komponen' => 2,
        ]);
    }

    public function test_invalid_component_payload_does_not_create_partial_record(): void
    {
        $payload = $this->componentPayload();
        unset($payload['bpjsKaryawan']);

        $this->withToken('mock-payroll')
            ->putJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/komponen', $payload)
            ->assertUnprocessable()
            ->assertJsonPath('code', 'VALIDATION_ERROR');

        $this->assertDatabaseCount('payroll_components', 0);
    }

    public function test_get_components_returns_saved_employee_data(): void
    {
        $this->withToken('mock-payroll')
            ->putJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/komponen', $this->componentPayload())
            ->assertOk();

        $response = $this->withToken('mock-payroll')
            ->getJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/komponen?employeeId=EMP-001');

        $response->assertOk()
            ->assertJsonPath('employeeId', 'EMP-001')
            ->assertJsonPath('gajiPokok', 6_500_000)
            ->assertJsonPath('versiKomponen', 1);
    }

    public function test_get_components_requires_employee_id(): void
    {
        $this->withToken('mock-payroll')
            ->getJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/komponen')
            ->assertBadRequest()
            ->assertJsonPath('code', 'VALIDATION_ERROR');
    }

    public function test_preview_uses_active_components_and_fetches_actual_leave_data(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*/api/v1/leave-status*' => Http::response([
                'employeeId' => 'EMP-001',
                'items' => [
                    [
                        'leaveType' => 'ANNUAL',
                        'workingDaysInRange' => 3,
                        'paid' => true,
                    ],
                ],
            ], 200),
        ]);

        $payload = $this->componentPayload();
        $payload['komponen'] = [
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
            [
                'nama' => 'Bonus tidak aktif',
                'jenis' => 'pendapatan',
                'nominal' => 1_000_000,
                'aktif' => false,
            ],
        ];
        $this->withToken('mock-payroll')
            ->putJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/komponen', $payload)
            ->assertOk();

        $response = $this->withToken('mock-payroll')
            ->postJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/pratinjau', [
                'employeeId' => 'EMP-001',
            ]);

        $response->assertOk()
            ->assertJsonPath('komponenPendapatan', 500_000)
            ->assertJsonPath('komponenPotongan', 200_000)
            ->assertJsonPath('totalPendapatan', 7_500_000)
            ->assertJsonPath('totalPotongan', 200_000)
            ->assertJsonPath('totalGajiBersih', 7_235_000)
            ->assertJsonPath('bpjsPerusahaan', 260_000)
            ->assertJsonPath('cuti.totalHariCuti', 3);

        Http::assertSent(static fn (ClientRequest $request): bool => $request->method() === 'GET'
            && str_contains($request->url(), 'employeeId=EMP-001')
            && str_contains($request->url(), 'from=2026-11-01')
            && str_contains($request->url(), 'to=2026-11-30')
        );
    }

    public function test_api_round_trip_returns_the_expected_net_pay(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*/api/v1/leave-status*' => Http::response([
                'employeeId' => 'EMP-001',
                'items' => [],
            ], 200),
        ]);

        $payload = [
            'employeeId' => 'EMP-001',
            'gajiPokok' => 6_000_000,
            'bonus' => 500_000,
            'bpjsKaryawan' => 60_000,
            'bpjsPerusahaan' => 240_000,
            'versiKomponen' => 1,
            'komponen' => [
                [
                    'nama' => 'Tunjangan',
                    'jenis' => 'pendapatan',
                    'nominal' => 300_000,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Potongan',
                    'jenis' => 'potongan',
                    'nominal' => 100_000,
                    'aktif' => true,
                ],
            ],
        ];

        $this->withToken('mock-payroll')
            ->putJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/komponen', $payload)
            ->assertOk()
            ->assertJsonPath('komponen.0.nominal', 300_000)
            ->assertJsonPath('versiKomponen', 1);

        $this->withToken('mock-payroll')
            ->getJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/komponen?employeeId=EMP-001')
            ->assertOk()
            ->assertJsonPath('gajiPokok', 6_000_000)
            ->assertJsonPath('bonus', 500_000)
            ->assertJsonPath('bpjsKaryawan', 60_000)
            ->assertJsonPath('bpjsPerusahaan', 240_000)
            ->assertJsonPath('komponen.0.nominal', 300_000)
            ->assertJsonPath('komponen.1.nominal', 100_000);

        $this->withToken('mock-payroll')
            ->postJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/pratinjau', [
                'employeeId' => 'EMP-001',
            ])
            ->assertOk()
            ->assertJsonPath('totalPendapatan', 6_800_000)
            ->assertJsonPath('totalPotongan', 100_000)
            ->assertJsonPath('totalGajiBersih', 6_640_000)
            ->assertJsonPath('bpjsPerusahaan', 240_000);
    }

    public function test_preview_returns_upstream_failure_instead_of_a_success_payload(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*/api/v1/leave-status*' => Http::response([
                'code' => 'SERVICE_UNAVAILABLE',
                'message' => 'F-4 sedang tidak tersedia.',
            ], 503),
        ]);

        $this->withToken('mock-payroll')
            ->putJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/komponen', $this->componentPayload())
            ->assertOk();

        $response = $this->withToken('mock-payroll')
            ->postJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/pratinjau', [
                'employeeId' => 'EMP-001',
            ]);

        $response->assertServiceUnavailable()
            ->assertJsonPath('code', 'SERVICE_UNAVAILABLE')
            ->assertJsonMissingPath('totalGajiBersih');
    }

    public function test_preview_rejects_malformed_success_response_from_f4(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*/api/v1/leave-status*' => Http::response([
                'employeeId' => 'EMP-001',
            ], 200),
        ]);

        $this->withToken('mock-payroll')
            ->putJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/komponen', $this->componentPayload())
            ->assertOk();

        $this->withToken('mock-payroll')
            ->postJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/pratinjau', [
                'employeeId' => 'EMP-001',
            ])
            ->assertStatus(502)
            ->assertJsonPath('code', 'INVALID_UPSTREAM_RESPONSE')
            ->assertJsonMissingPath('totalGajiBersih');
    }

    public function test_component_endpoints_reject_roles_without_payroll_access(): void
    {
        $this->withToken('mock-e2')
            ->getJson('/api/payroll/v1/periode/'.self::PERIODO_ID.'/komponen?employeeId=EMP-001')
            ->assertForbidden()
            ->assertJsonPath('code', 'FORBIDDEN');
    }

    private function componentPayload(): array
    {
        return [
            'employeeId' => 'EMP-001',
            'gajiPokok' => 6_500_000,
            'bonus' => 500_000,
            'bpjsKaryawan' => 65_000,
            'bpjsPerusahaan' => 260_000,
            'versiKomponen' => 1,
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
        ];
    }
}
