<?php

namespace Tests\Feature;

use App\Models\Taman;
use App\Models\User;
use App\Support\RthArProfileBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RthArScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_ar_scan_page_is_public_and_shows_park_profile(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Engku Putri',
            'kategori' => 'Taman Kota',
            'luasan' => 12000,
            'alamat' => 'Batam Center',
            'deskripsi' => 'Profil taman contoh untuk WebAR.',
            'data_verified_at' => now(),
        ]);

        $this->get(route('rth.ar-scan', $taman))
            ->assertOk()
            ->assertSee('Taman Engku Putri')
            ->assertSee('Kartu Hologram Profil')
            ->assertSee('ar-live-camera', false)
            ->assertSee('SIMTAMAN Live AR')
            ->assertSee('Buka Web Detail SIMTAMAN')
            ->assertDontSee('model-viewer', false);
    }

    public function test_profile_builder_marks_data_fresh_when_recently_verified(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Segar',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
            'data_verified_at' => now()->subDays(10),
        ]);

        $profile = app(RthArProfileBuilder::class)->build($taman);

        $this->assertTrue($profile['data_freshness']['is_fresh']);
        $this->assertSame('Diperbarui', $profile['data_freshness']['label']);
    }

    public function test_profile_builder_includes_fasilitas(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Fasilitas',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
            'fasilitas' => [
                ['nama' => 'Playground', 'kondisi' => 'Baik'],
                ['nama' => 'Toilet', 'kondisi' => 'Rusak Ringan'],
            ],
        ]);

        $profile = app(RthArProfileBuilder::class)->build($taman);

        $this->assertSame(2, $profile['fasilitas']['count']);
        $this->assertSame('Playground', $profile['fasilitas']['items'][0]['nama']);
    }

    public function test_ar_scan_page_shows_fasilitas(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman WebAR Fasilitas',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
            'fasilitas' => [
                ['nama' => 'Jogging Track', 'kondisi' => 'Baik'],
            ],
        ]);

        $tamanWithKondisi = Taman::create([
            'nama_taman' => 'Taman Kondisi Tersembunyi',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
            'fasilitas' => [
                ['nama' => 'Toilet', 'kondisi' => 'Rusak Berat'],
            ],
        ]);

        $this->get(route('rth.ar-scan', $taman))
            ->assertOk()
            ->assertSee('Fasilitas Tersedia')
            ->assertSee('Jogging Track')
            ->assertDontSee('Paket DPA Aktif');

        $this->get(route('rth.ar-scan', $tamanWithKondisi))
            ->assertOk()
            ->assertSee('Toilet')
            ->assertDontSee('Rusak Berat');
    }

    public function test_profile_builder_includes_latest_maintenance_summary(): void
    {
        $taman = Taman::create([
            'nama_taman' => 'Taman Pemeliharaan',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
        ]);

        \App\Models\PemeliharaanTaman::create([
            'tanggal' => '2026-07-15',
            'tim' => 'Tim Wilayah 1',
            'taman_id' => $taman->id,
            'lokasi_pelaksanaan' => 'Taman Pemeliharaan — Alamat',
            'uraian_pekerjaan' => 'Pembersihan rumput dan pangkas pagar.',
            'persentase_progres' => 75,
            'foto_sebelum_1' => 'pemeliharaan-taman/test1.jpg',
            'foto_sebelum_2' => 'pemeliharaan-taman/test2.jpg',
            'foto_saat_1' => 'pemeliharaan-taman/test3.jpg',
            'foto_saat_2' => 'pemeliharaan-taman/test4.jpg',
            'foto_sesudah_1' => 'pemeliharaan-taman/test5.jpg',
            'foto_sesudah_2' => 'pemeliharaan-taman/test6.jpg',
        ]);

        $profile = app(RthArProfileBuilder::class)->build($taman);

        $this->assertSame('15 Jul 2026', $profile['maintenance']['date_label']);
        $this->assertSame('Pembersihan rumput dan pangkas pagar.', $profile['maintenance']['uraian_pekerjaan']);
        $this->assertSame(75, $profile['maintenance']['progres']);
        $this->assertSame(
            '15 Jul 2026 - Pembersihan rumput dan pangkas pagar. - 75% progres',
            $profile['maintenance']['summary_label']
        );

        $this->get(route('rth.ar-scan', $taman))
            ->assertOk()
            ->assertSee('15 Jul 2026 - Pembersihan rumput dan pangkas pagar. - 75% progres', false);
    }

    public function test_admin_can_download_ar_qr_png(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $taman = Taman::create([
            'nama_taman' => 'Taman QR',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.tamans.ar-qr', $taman))
            ->assertOk()
            ->assertHeader('Content-Type', extension_loaded('imagick') ? 'image/png' : 'image/svg+xml');
    }
}
