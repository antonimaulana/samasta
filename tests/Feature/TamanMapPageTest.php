<?php

namespace Tests\Feature;

use App\Models\Taman;
use App\Models\TamanImage;
use App\Support\TamanMapData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class TamanMapPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_map_page_renders_leaflet_map_with_normalized_coordinates(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Peta Uji',
            'kategori' => 'Taman Kota',
            'luasan' => 1200,
            'alamat' => 'Batam Center',
            'deskripsi' => 'Deskripsi taman peta uji.',
            'latitude' => 1.08286,
            'longitude' => 104.0305,
        ]);

        TamanImage::create([
            'taman_id' => $taman->id,
            'path_foto' => 'tamans/peta-uji.jpg',
        ]);

        $this->get(route('tamans.map'))
            ->assertOk()
            ->assertSee('taman-map-shell', false)
            ->assertSee('taman-map-canvas', false)
            ->assertSee('leaflet@1.9.4/dist/leaflet.js', false)
            ->assertSee('Taman Peta Uji')
            ->assertSee('"lat":1.08286', false)
            ->assertSee('peta-uji.jpg', false);
    }

    public function test_map_points_normalize_legacy_southern_hemisphere_latitude(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Koordinat Legacy',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat legacy',
            'deskripsi' => 'Deskripsi legacy.',
            'latitude' => -1.08,
            'longitude' => 104.03,
        ]);

        $points = TamanMapData::mapPoints(Taman::query()->whereKey($taman->id), 'legacy-test');

        $this->assertCount(1, $points);
        $this->assertSame(1.08, $points->first()['lat']);
        $this->assertSame(104.03, $points->first()['lng']);
    }

    public function test_map_points_exclude_placeholder_default_coordinates(): void
    {
        Taman::create([
            'nama_taman' => 'Taman Default Koordinat',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat default',
            'deskripsi' => 'Deskripsi default.',
            'latitude' => Taman::DEFAULT_LATITUDE,
            'longitude' => Taman::DEFAULT_LONGITUDE,
        ]);

        $points = TamanMapData::mapPoints(Taman::query(), 'placeholder-test');

        $this->assertCount(0, $points);
    }

    public function test_map_points_fix_swapped_coordinates(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Koordinat Terbalik',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat terbalik',
            'deskripsi' => 'Deskripsi terbalik.',
            'latitude' => 104.096198,
            'longitude' => 1.132142,
        ]);

        $points = TamanMapData::mapPoints(Taman::query()->whereKey($taman->id), 'swapped-test');

        $this->assertCount(1, $points);
        $this->assertSame(1.132142, $points->first()['lat']);
        $this->assertSame(104.096198, $points->first()['lng']);
    }

    public function test_map_points_fix_scaled_coordinates_missing_decimal(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Koordinat Skala',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat skala',
            'deskripsi' => 'Deskripsi skala.',
            'latitude' => 1038944,
            'longitude' => 104049552,
        ]);

        $points = TamanMapData::mapPoints(Taman::query()->whereKey($taman->id), 'scaled-test');

        $this->assertCount(1, $points);
        $this->assertSame(1.038944, $points->first()['lat']);
        $this->assertSame(104.049552, $points->first()['lng']);
    }

    public function test_map_page_shows_empty_state_when_no_coordinates(): void
    {
        Taman::create([
            'nama_taman' => 'Taman Tanpa Koordinat',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi.',
        ]);

        $this->get(route('tamans.map'))
            ->assertOk()
            ->assertSee('Tidak ada taman dengan koordinat untuk filter ini.')
            ->assertDontSee('taman-map-shell', false);
    }
}
