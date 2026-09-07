<?php

namespace Tests\Feature;

use App\Support\PemerintahKotaBatam;
use App\Support\RthKotaBatam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KontenBerandaTest extends TestCase
{
    use RefreshDatabase;
    public function test_homepage_displays_static_pejabat_data(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('H. Amsakar Ahmad')
            ->assertSee('Walikota Batam')
            ->assertSee('Li Claudia Chandra')
            ->assertSee('Drs. Eryudhi Apriadi');
    }

    public function test_homepage_displays_static_visi_misi(): void
    {
        $visiMisi = PemerintahKotaBatam::visiMisiStatic();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($visiMisi['visi'], false);
    }

    public function test_static_rth_kategori_data_is_available(): void
    {
        $kategori = RthKotaBatam::kategori();

        $this->assertNotEmpty($kategori);
        $this->assertSame('RTH Taman Kota', $kategori[0]['nama']);
        $this->assertGreaterThan(0, RthKotaBatam::total()['luas']);
    }

    public function test_static_pejabat_data_is_available(): void
    {
        $this->assertCount(3, PemerintahKotaBatam::pimpinan());
    }
}
