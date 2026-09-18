<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluasiKelengkapanDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_admin_can_view_kelengkapan_data_evaluasi_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        Taman::create([
            'nama_taman' => 'Taman Data Lengkap',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1500,
            'alamat' => 'Alamat lengkap taman evaluasi data',
            'deskripsi' => 'Deskripsi lengkap untuk evaluasi kelengkapan data.',
            'latitude' => 1.0456,
            'longitude' => 104.0305,
            'tahun_pembangunan' => 2020,
            'nilai_pembangunan' => 100000000,
            'kontraktor' => 'PT Taman',
            'konsultan_perencana' => 'Konsultan A',
            'fasilitas' => [['nama' => 'Playground', 'kondisi' => 'Baik']],
            'foto' => 'taman/sample.jpg',
            'data_verified_at' => now(),
            'status_data' => Taman::STATUS_DATA_LENGKAP,
        ]);

        Taman::create([
            'nama_taman' => 'Taman Data Kosong',
            'kategori' => 'Taman Lingkungan',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 500,
            'alamat' => '',
            'deskripsi' => '',
            'status_data' => Taman::STATUS_DATA_BELUM_LENGKAP,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.kelengkapan-data.index'))
            ->assertOk()
            ->assertSee('Evaluasi Kelengkapan & Kemutakhiran Data')
            ->assertSee('Lokasi Perlu Perhatian')
            ->assertSee('Taman Data Kosong')
            ->assertSee('Field Paling Sering Kosong');
    }

    public function test_admin_can_export_kelengkapan_data_evaluasi_pdf(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension is required for PDF export.');
        }

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.kelengkapan-data.export-pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
