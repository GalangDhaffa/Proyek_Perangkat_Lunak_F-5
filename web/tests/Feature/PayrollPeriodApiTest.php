<?php

namespace Tests\Feature;

use App\Models\PayrollPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PayrollPeriodApiTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN_PAYROLL = 'mock-payroll';
    private const TOKEN_ADMIN = 'mock-admin-payroll';
    private const TOKEN_MANAJEMEN = 'mock-manajemen';
    private const TOKEN_KARYAWAN = 'mock-karyawan';

    /**
     * Halaman web /periode-payroll dapat diakses (HTTP 200).
     */
    public function test_periode_page_returns_successful_response(): void
    {
        $response = $this->get('/periode-payroll');
        $response->assertStatus(200);
        $response->assertSee('Periode payroll');
    }

    /**
     * Request tanpa token ditolak (401 UNAUTHORIZED).
     */
    public function test_list_periods_requires_authentication(): void
    {
        $response = $this->getJson('/api/payroll/v1/periode');
        $response->assertStatus(401);
        $response->assertJson([
            'code' => 'UNAUTHORIZED',
            'message' => 'Token tidak tersedia atau tidak valid.',
        ]);
    }

    /**
     * Pengguna dengan hak baca dapat mengambil daftar periode (HTTP 200).
     */
    public function test_list_periods_returns_data_for_authorized_roles(): void
    {
        PayrollPeriod::create([
            'id' => (string) Str::uuid(),
            'nama_periode' => 'September 2026',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_akhir' => '2026-09-30',
            'tanggal_pembayaran' => '2026-10-01',
            'status' => 'Draf',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->getJson('/api/payroll/v1/periode');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'periodeId',
                    'namaPeriode',
                    'tanggalMulai',
                    'tanggalAkhir',
                    'tanggalPembayaran',
                    'status',
                    'statusPeriode',
                ],
            ],
            'total',
        ]);
        $this->assertGreaterThanOrEqual(1, $response->json('total'));
    }

    /**
     * Membaca detail periode mengembalikan data yang persis sesuai yang disimpan.
     */
    public function test_read_period_detail_matches_stored_data(): void
    {
        $uuid = (string) Str::uuid();
        $period = PayrollPeriod::create([
            'id' => $uuid,
            'uuid' => $uuid,
            'nama_periode' => 'Agustus 2026',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_akhir' => '2026-08-31',
            'tanggal_pembayaran' => '2026-09-01',
            'status' => 'Draf',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->getJson('/api/payroll/v1/periode/' . $uuid);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $uuid,
            'periodeId' => $uuid,
            'namaPeriode' => 'Agustus 2026',
            'tanggalMulai' => '2026-08-01',
            'tanggalAkhir' => '2026-08-31',
            'tanggalPembayaran' => '2026-09-01',
            'status' => 'Draf',
        ]);
    }

    /**
     * Detail dengan UUID tidak valid mengembalikan 400 INVALID_PERIODE_ID.
     */
    public function test_read_period_with_invalid_uuid_returns_400(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->getJson('/api/payroll/v1/periode/invalid-uuid-format');

        $response->assertStatus(400);
        $response->assertJson([
            'code' => 'INVALID_PERIODE_ID',
        ]);
    }

    /**
     * Detail dengan UUID yang tidak ditemukan di DB mengembalikan 404 PERIODE_NOT_FOUND.
     */
    public function test_read_period_not_found_returns_404(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->getJson('/api/payroll/v1/periode/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404);
        $response->assertJson([
            'code' => 'PERIODE_NOT_FOUND',
        ]);
    }

    /**
     * Tambah periode valid berhasil disimpan ke database (HTTP 201).
     */
    public function test_create_period_valid_succeeds(): void
    {
        $payload = [
            'tanggalMulai' => '2026-11-01',
            'tanggalAkhir' => '2026-11-30',
            'tanggalPembayaran' => '2026-12-01',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->postJson('/api/payroll/v1/periode', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'periodeId',
            'namaPeriode',
            'tanggalMulai',
            'tanggalAkhir',
            'tanggalPembayaran',
            'status',
        ]);

        $createdId = $response->json('id');
        $this->assertNotEmpty($createdId);
        $this->assertEquals('November 2026', $response->json('namaPeriode'));
        $this->assertEquals('Draf', $response->json('status'));

        // Pastikan tersimpan di database
        $this->assertDatabaseHas('payroll_periods', [
            'id' => $createdId,
            'tanggal_mulai' => '2026-11-01',
            'tanggal_akhir' => '2026-11-30',
            'tanggal_pembayaran' => '2026-12-01',
            'status' => 'Draf',
        ]);
    }

    /**
     * PATCH berhasil memperbarui record yang sama tanpa mengubah ID.
     */
    public function test_patch_period_succeeds_with_same_id(): void
    {
        $uuid = (string) Str::uuid();
        $period = PayrollPeriod::create([
            'id' => $uuid,
            'uuid' => $uuid,
            'nama_periode' => 'September 2026',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_akhir' => '2026-09-30',
            'tanggal_pembayaran' => '2026-10-01',
            'status' => 'Draf',
        ]);

        $patchPayload = [
            'tanggalPembayaran' => '2026-10-05', // Hanya ubah tanggal pembayaran
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->patchJson('/api/payroll/v1/periode/' . $uuid, $patchPayload);

        $response->assertStatus(200);
        $this->assertEquals($uuid, $response->json('id'));
        $this->assertEquals($uuid, $response->json('periodeId'));
        $this->assertEquals('2026-09-01', $response->json('tanggalMulai'));
        $this->assertEquals('2026-09-30', $response->json('tanggalAkhir'));
        $this->assertEquals('2026-10-05', $response->json('tanggalPembayaran'));

        // Cek database: ID tetap sama, kolom terupdate
        $this->assertDatabaseHas('payroll_periods', [
            'id' => $uuid,
            'tanggal_pembayaran' => '2026-10-05',
        ]);
    }

    /**
     * PATCH satu field tetap memvalidasi keadaan akhir (contoh: tanggalAkhir < tanggalMulai yang sudah ada di DB).
     */
    public function test_patch_single_field_validates_end_state_date_order(): void
    {
        $uuid = (string) Str::uuid();
        PayrollPeriod::create([
            'id' => $uuid,
            'uuid' => $uuid,
            'nama_periode' => 'Oktober 2026',
            'tanggal_mulai' => '2026-10-15',
            'tanggal_akhir' => '2026-10-31',
            'tanggal_pembayaran' => '2026-11-01',
            'status' => 'Draf',
        ]);

        // Kirim tanggalAkhir baru yang mendahului tanggalMulai di DB (10-10 < 10-15)
        $response = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->patchJson('/api/payroll/v1/periode/' . $uuid, [
                'tanggalAkhir' => '2026-10-10',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'code' => 'VALIDATION_ERROR',
        ]);

        // Verifikasi tidak ada perubahan parsial di database
        $this->assertDatabaseHas('payroll_periods', [
            'id' => $uuid,
            'tanggal_akhir' => '2026-10-31',
        ]);
    }

    /**
     * Data yang diperbarui tetap konsisten pada request berikutnya.
     */
    public function test_updated_data_persists_in_subsequent_request(): void
    {
        $uuid = (string) Str::uuid();
        PayrollPeriod::create([
            'id' => $uuid,
            'uuid' => $uuid,
            'nama_periode' => 'Desember 2026',
            'tanggal_mulai' => '2026-12-01',
            'tanggal_akhir' => '2026-12-31',
            'tanggal_pembayaran' => '2027-01-02',
            'status' => 'Draf',
        ]);

        $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->patchJson('/api/payroll/v1/periode/' . $uuid, [
                'tanggalMulai' => '2026-12-01',
                'tanggalAkhir' => '2026-12-31',
                'tanggalPembayaran' => '2027-01-05',
            ])->assertStatus(200);

        // Request berikutnya
        $fetchResponse = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->getJson('/api/payroll/v1/periode/' . $uuid);

        $fetchResponse->assertStatus(200);
        $this->assertEquals('2027-01-05', $fetchResponse->json('tanggalPembayaran'));
    }

    /**
     * Kolom kosong ditolak dengan format pesan kesalahan per field (422).
     */
    public function test_empty_fields_rejected(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->postJson('/api/payroll/v1/periode', [
                'tanggalMulai' => '',
                'tanggalAkhir' => '',
                'tanggalPembayaran' => '',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'code' => 'VALIDATION_ERROR',
        ]);

        $fields = collect($response->json('details'))->pluck('field')->all();
        $this->assertContains('tanggalMulai', $fields);
        $this->assertContains('tanggalAkhir', $fields);
        $this->assertContains('tanggalPembayaran', $fields);
    }

    /**
     * Tanggal akhir sebelum tanggal mulai ditolak.
     */
    public function test_invalid_date_order_rejected(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->postJson('/api/payroll/v1/periode', [
                'tanggalMulai' => '2026-10-31',
                'tanggalAkhir' => '2026-10-01',
                'tanggalPembayaran' => '2026-11-01',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'code' => 'VALIDATION_ERROR',
        ]);

        $details = $response->json('details');
        $this->assertTrue(collect($details)->contains(function ($item) {
            return $item['field'] === 'tanggalAkhir' && str_contains($item['message'], 'tidak boleh sebelum tanggal mulai');
        }));
    }

    /**
     * Penolakan validasi tidak membuat atau mengubah record di database.
     */
    public function test_rejection_does_not_create_or_modify_records(): void
    {
        $initialCount = PayrollPeriod::count();

        $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->postJson('/api/payroll/v1/periode', [
                'tanggalMulai' => '',
                'tanggalAkhir' => '2026-10-01',
                'tanggalPembayaran' => '2026-10-05',
            ])->assertStatus(422);

        $this->assertEquals($initialCount, PayrollPeriod::count());
    }

    /**
     * Request hanya dapat mengubah field yang diizinkan (whitelist).
     */
    public function test_unallowed_additional_properties_rejected_with_422(): void
    {
        $uuid = (string) Str::uuid();
        PayrollPeriod::create([
            'id' => $uuid,
            'uuid' => $uuid,
            'nama_periode' => 'September 2026',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_akhir' => '2026-09-30',
            'tanggal_pembayaran' => '2026-10-01',
            'status' => 'Draf',
        ]);

        // Coba kirim field tambahan seperti 'status' atau atribut terlarang lainnya
        $response = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->patchJson('/api/payroll/v1/periode/' . $uuid, [
                'status' => 'Disetujui',
                'tanggalPembayaran' => '2026-10-02',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('code', 'VALIDATION_ERROR');

        // Pastikan database tidak berubah
        $this->assertDatabaseHas('payroll_periods', [
            'id' => $uuid,
            'status' => 'Draf',
            'tanggal_pembayaran' => '2026-10-01',
        ]);
    }

    /**
     * Pengguna tanpa hak tulis (seperti karyawan atau manajemen) ditolak saat membuat/mengubah periode (403).
     */
    public function test_unauthorized_user_cannot_write_data(): void
    {
        $payload = [
            'tanggalMulai' => '2026-11-01',
            'tanggalAkhir' => '2026-11-30',
            'tanggalPembayaran' => '2026-12-01',
        ];

        // Manajemen hanya pembaca rekap, tidak memiliki izin tulis periode
        $resManajemen = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_MANAJEMEN)
            ->postJson('/api/payroll/v1/periode', $payload);
        $resManajemen->assertStatus(403);
        $resManajemen->assertJson(['code' => 'FORBIDDEN']);

        // Karyawan tidak memiliki izin
        $resKaryawan = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_KARYAWAN)
            ->postJson('/api/payroll/v1/periode', $payload);
        $resKaryawan->assertStatus(403);
        $resKaryawan->assertJson(['code' => 'FORBIDDEN']);
    }

    /**
     * Periode baru di database otomatis terintegrasi dengan endpoint Rekap Biaya (adaData=false, totalBiaya=null).
     */
    public function test_new_db_period_integrates_with_rekap_biaya(): void
    {
        $uuid = (string) Str::uuid();
        PayrollPeriod::create([
            'id' => $uuid,
            'uuid' => $uuid,
            'nama_periode' => 'November 2026',
            'tanggal_mulai' => '2026-11-01',
            'tanggal_akhir' => '2026-11-30',
            'tanggal_pembayaran' => '2026-12-01',
            'status' => 'Draf',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer mock-manajemen')
            ->getJson('/api/payroll/v1/rekap-biaya?periodeId=' . $uuid);

        $response->assertStatus(200);
        $response->assertJson([
            'periodeId' => $uuid,
            'namaPeriode' => 'November 2026',
            'adaData' => false,
            'jumlahKaryawan' => 0,
            'totalPendapatan' => 0,
            'totalBiaya' => null,
        ]);
    }

    /**
     * Input dengan tipe data tidak valid (array, boolean, number, null) ditolak dengan 422.
     */
    public function test_invalid_data_types_rejected_with_422(): void
    {
        // 1. Tipe array
        $resArray = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->postJson('/api/payroll/v1/periode', [
                'tanggalMulai' => ['2026-11-01'],
                'tanggalAkhir' => '2026-11-30',
                'tanggalPembayaran' => '2026-12-01',
            ]);
        $resArray->assertStatus(422);
        $resArray->assertJson(['code' => 'VALIDATION_ERROR']);

        // 2. Tipe boolean
        $resBool = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->postJson('/api/payroll/v1/periode', [
                'tanggalMulai' => true,
                'tanggalAkhir' => '2026-11-30',
                'tanggalPembayaran' => '2026-12-01',
            ]);
        $resBool->assertStatus(422);

        // 3. Tipe integer
        $resInt = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->postJson('/api/payroll/v1/periode', [
                'tanggalMulai' => 20261101,
                'tanggalAkhir' => '2026-11-30',
                'tanggalPembayaran' => '2026-12-01',
            ]);
        $resInt->assertStatus(422);

        // 4. Tanggal kalender tidak valid (misal: 30 Februari)
        $resFeb = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->postJson('/api/payroll/v1/periode', [
                'tanggalMulai' => '2026-02-30',
                'tanggalAkhir' => '2026-02-31',
                'tanggalPembayaran' => '2026-03-01',
            ]);
        $resFeb->assertStatus(422);
    }

    /**
     * PATCH tanpa field kanonikal ditolak dengan 422.
     */
    public function test_patch_with_no_allowed_fields_rejected_with_422(): void
    {
        $uuid = (string) Str::uuid();
        PayrollPeriod::create([
            'id' => $uuid,
            'uuid' => $uuid,
            'nama_periode' => 'September 2026',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_akhir' => '2026-09-30',
            'tanggal_pembayaran' => '2026-10-01',
            'status' => 'Draf',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->patchJson('/api/payroll/v1/periode/' . $uuid, []);

        $response->assertStatus(422);
        $response->assertJson(['code' => 'VALIDATION_ERROR']);
    }

    /**
     * Field tambahan di luar skema kanonikal ditolak dengan 422 tanpa memodifikasi DB.
     */
    public function test_additional_unrecognized_fields_rejected_with_422(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->postJson('/api/payroll/v1/periode', [
                'tanggalMulai' => '2026-11-01',
                'tanggalAkhir' => '2026-11-30',
                'tanggalPembayaran' => '2026-12-01',
                'extraField' => 'unallowed',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'code' => 'VALIDATION_ERROR',
            'details' => [
                [
                    'field' => 'extraField',
                ],
            ],
        ]);
    }

    /**
     * PUT mewajibkan ketiga tanggal kanonikal.
     */
    public function test_put_requires_all_three_canonical_dates(): void
    {
        $uuid = (string) Str::uuid();
        PayrollPeriod::create([
            'id' => $uuid,
            'uuid' => $uuid,
            'nama_periode' => 'September 2026',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_akhir' => '2026-09-30',
            'tanggal_pembayaran' => '2026-10-01',
            'status' => 'Draf',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . self::TOKEN_PAYROLL)
            ->putJson('/api/payroll/v1/periode/' . $uuid, [
                'tanggalMulai' => '2026-09-01',
                // tanggalAkhir sengaja dihilangkan
                'tanggalPembayaran' => '2026-10-05',
            ]);

        $response->assertStatus(422);
        $response->assertJson(['code' => 'VALIDATION_ERROR']);
    }

    /**
     * Rekap Biaya menggunakan metadata yang konsisten dari database jika record contoh diperbarui.
     */
    public function test_rekap_biaya_metadata_is_consistent_when_example_period_updated_in_database(): void
    {
        $exampleId = '01a1193d-2500-7c04-854b-8bf03c711dde';

        // Simpan periode contoh ke database dengan nama dan tanggal yang dimodifikasi
        PayrollPeriod::updateOrCreate(
            ['id' => $exampleId],
            [
                'uuid' => $exampleId,
                'nama_periode' => 'Oktober 2026 (Diperbarui)',
                'tanggal_mulai' => '2026-10-05',
                'tanggal_akhir' => '2026-10-31',
                'tanggal_pembayaran' => '2026-11-05',
                'status' => 'Disetujui',
            ]
        );

        // Rekap biaya harus menampilkan metadata yang baru diperbarui dari DB
        $response = $this->withHeader('Authorization', 'Bearer mock-manajemen')
            ->getJson('/api/payroll/v1/rekap-biaya?periodeId=' . $exampleId);

        $response->assertStatus(200);
        $response->assertJson([
            'periodeId' => $exampleId,
            'namaPeriode' => 'Oktober 2026 (Diperbarui)',
            'tanggalMulai' => '2026-10-05',
            'tanggalAkhir' => '2026-10-31',
            'statusPeriode' => 'Disetujui',
            // Agregat fiktif demo tetap dipertahankan
            'totalPendapatan' => 7750000.0,
            'totalBiaya' => null,
        ]);
    }
}
