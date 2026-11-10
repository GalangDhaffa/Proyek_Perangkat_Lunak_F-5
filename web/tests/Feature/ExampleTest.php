<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Route / seharusnya redirect ke /rekap-biaya (302), bukan langsung 200.
     */
    public function test_root_redirects_to_rekap_biaya(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/rekap-biaya');
    }

    /**
     * Halaman utama rekap biaya dapat diakses dan mengembalikan 200.
     */
    public function test_rekap_biaya_page_returns_successful_response(): void
    {
        $response = $this->get('/rekap-biaya');

        $response->assertStatus(200);
    }

    /**
     * Halaman komponen gaji dapat diakses dan mengembalikan 200.
     */
    public function test_komponen_gaji_page_returns_successful_response(): void
    {
        $response = $this->get('/komponen-gaji');

        $response->assertStatus(200);
        $response->assertSee('Komponen gaji');
        $response->assertSee('select-karyawan');
        $response->assertSee('select-periode');
    }

    /**
     * Halaman komponen gaji dapat diakses dengan membawa parameter periodeId.
     */
    public function test_komponen_gaji_page_with_periode_id_query_param(): void
    {
        $response = $this->get('/komponen-gaji?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde');

        $response->assertStatus(200);
        $response->assertSee('01a1193d-2500-7c04-854b-8bf03c711dde');
    }

    /**
     * Halaman pratinjau perhitungan dapat diakses dan mengembalikan 200.
     */
    public function test_pratinjau_perhitungan_page_returns_successful_response(): void
    {
        $response = $this->get('/pratinjau-perhitungan');

        $response->assertStatus(200);
        $response->assertSee('Pratinjau perhitungan');
        $response->assertSee('select-karyawan');
        $response->assertSee('select-periode');
        $response->assertSee('btn-hitung-pratinjau');
    }

    /**
     * Halaman pratinjau perhitungan dapat diakses dengan parameter periodeId dan employeeId.
     */
    public function test_pratinjau_perhitungan_page_with_query_params(): void
    {
        $response = $this->get('/pratinjau-perhitungan?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde&employeeId=EMP-002');

        $response->assertStatus(200);
        $response->assertSee('01a1193d-2500-7c04-854b-8bf03c711dde');
        $response->assertSee('EMP-002');
    }

    /**
     * Memastikan pelabelan EMP-001 adalah "Referensi demo cuti F-4" tanpa klaim referensi F-2,
     * serta placeholder jabatan dan departemen adalah "Belum tersedia".
     */
    public function test_emp_001_label_and_placeholders(): void
    {
        $komponen = $this->get('/komponen-gaji');
        $komponen->assertStatus(200);
        $komponen->assertSee('Referensi demo cuti F-4');
        $komponen->assertDontSee('Referensi F-2 / Seed Cuti F-4');
        $komponen->assertDontSee('Contoh F-2');

        $pratinjau = $this->get('/pratinjau-perhitungan');
        $pratinjau->assertStatus(200);
        $pratinjau->assertSee('Referensi demo cuti F-4');
        $pratinjau->assertDontSee('Referensi F-2 / Seed Cuti F-4');
        $pratinjau->assertSee('Belum tersedia');
    }

    /**
     * Halaman periode payroll mendukung retensi query param periodeId.
     */
    public function test_periode_payroll_page_with_periode_id(): void
    {
        $response = $this->get('/periode-payroll?periodeId=01a1193d-2500-7c04-854b-8bf03c711dde');
        $response->assertStatus(200);
        $response->assertSee('01a1193d-2500-7c04-854b-8bf03c711dde');
    }
}
