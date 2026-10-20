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
}
