<?php

namespace Tests\Feature;

use App\Models\Taman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTamanNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_shows_navigation_for_placeholder_coordinates(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Navigasi Publik',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Jl. Contoh Batam',
            'deskripsi' => 'Deskripsi.',
            'latitude' => Taman::DEFAULT_LATITUDE,
            'longitude' => Taman::DEFAULT_LONGITUDE,
        ]);

        $this->assertNull($taman->normalizedMapCoordinates());
        $this->assertNotNull($taman->navigationCoordinates());

        $this->get(route('tamans.show', $taman))
            ->assertOk()
            ->assertSee('Lokasi & Navigasi', false)
            ->assertSee('Rute Google Maps')
            ->assertSee('Waze')
            ->assertSee('Hitung jarak dari lokasi saya')
            ->assertSee('id="map"', false)
            ->assertSee('leaflet@1.9.4/dist/leaflet.js', false);
    }

    public function test_public_profile_shows_address_based_navigation_without_coordinates(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Tanpa Koordinat',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Jl. Hanya Alamat, Batam',
            'deskripsi' => 'Deskripsi.',
            'latitude' => null,
            'longitude' => null,
        ]);

        $this->get(route('tamans.show', $taman))
            ->assertOk()
            ->assertSee('Lokasi & Navigasi', false)
            ->assertSee('Rute Google Maps')
            ->assertSee('Waze')
            ->assertSee('Koordinat belum lengkap');
    }
}
