<?php

namespace Tests\Feature;

use App\Models\Taman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TamanLocationTagFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_edit_form_includes_gps_tag_location_controls(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $taman = Taman::create([
            'nama_taman' => 'Taman Tag Lokasi',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Jl. Uji Tag',
            'deskripsi' => 'Deskripsi.',
            'latitude' => Taman::DEFAULT_LATITUDE,
            'longitude' => Taman::DEFAULT_LONGITUDE,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.tamans.edit', $taman))
            ->assertOk()
            ->assertSee('Tag lokasi saya (GPS)', false)
            ->assertSee('id="taman-tag-my-location"', false)
            ->assertSee('id="taman-location-map"', false)
            ->assertSee('Perlu tag / verifikasi lapangan', false);
    }

    public function test_admin_create_form_includes_gps_tag_location_controls(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.tamans.create'))
            ->assertOk()
            ->assertSee('Tag lokasi saya (GPS)', false)
            ->assertSee('leaflet@1.9.4/dist/leaflet.js', false);
    }
}
