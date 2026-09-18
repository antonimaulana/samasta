<?php

namespace Tests\Feature;

use App\Models\AduanMasyarakat;
use App\Models\Kelurahan;
use App\Models\SurveyKepuasan;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluasiMasukanMasyarakatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_admin_can_view_masukan_masyarakat_evaluasi_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        $taman = Taman::create([
            'nama_taman' => 'Taman Evaluasi Masukan',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat masukan',
            'deskripsi' => 'Deskripsi masukan.',
        ]);

        AduanMasyarakat::create([
            'nomor_aduan' => AduanMasyarakat::generateNomor(),
            'taman_id' => $taman->id,
            'lokasi' => $taman->nama_taman,
            'jenis_aduan' => 'Kondisi Taman Rusak',
            'deskripsi' => 'Lampu taman mati.',
            'nama_pelapor' => 'Budi',
            'kontak_pelapor' => '081234567890',
            'status' => 'Baru',
        ]);

        SurveyKepuasan::create([
            'kategori' => 'Kondisi Taman',
            'rating' => 5,
            'taman_id' => $taman->id,
            'saran' => 'Sangat baik',
            'nama' => 'Ani',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.masukan-masyarakat.index'))
            ->assertOk()
            ->assertSee('Evaluasi Masukan Masyarakat')
            ->assertSee('Backlog Aduan Aktif')
            ->assertSee('Kondisi Taman Rusak')
            ->assertSee('Rekap Survey per Kategori');
    }

    public function test_admin_can_export_masukan_masyarakat_evaluasi_pdf(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension is required for PDF export.');
        }

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.masukan-masyarakat.export-pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
