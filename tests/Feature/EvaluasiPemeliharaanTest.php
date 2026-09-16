<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\PemeliharaanTaman;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\TimPelaksanaSeeder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluasiPemeliharaanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_admin_can_view_pemeliharaan_evaluasi_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        $tamanA = Taman::create([
            'nama_taman' => 'Taman Evaluasi Alpha',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat alpha',
            'deskripsi' => 'Deskripsi alpha.',
        ]);

        $tamanB = Taman::create([
            'nama_taman' => 'Taman Evaluasi Beta',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 800,
            'alamat' => 'Alamat beta',
            'deskripsi' => 'Deskripsi beta.',
        ]);

        PemeliharaanTaman::create([
            'tanggal' => now()->format('Y-m-d').' 08:00:00',
            'tim' => PemeliharaanTaman::timNames()[0],
            'taman_id' => $tamanA->id,
            'lokasi_pelaksanaan' => $tamanA->nama_taman,
            'jumlah_personil' => 5,
            'uraian_pekerjaan' => 'Pembersihan rutin',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.pemeliharaan.index'))
            ->assertOk()
            ->assertSee('Evaluasi Pemeliharaan per Taman')
            ->assertSee('Taman Evaluasi Alpha')
            ->assertSee('Taman Evaluasi Beta')
            ->assertSee('Taman Belum Dipelihara');
    }

    public function test_admin_can_export_pemeliharaan_evaluasi_pdf(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension is required for PDF export.');
        }

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.pemeliharaan.export-pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
