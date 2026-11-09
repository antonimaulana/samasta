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

        PemeliharaanTaman::create([
            'tanggal' => now()->format('Y-m-d').' 09:00:00',
            'tim' => PemeliharaanTaman::timNames()[0],
            'taman_id' => null,
            'lokasi_pelaksanaan' => 'Jalan Umum Batam',
            'jumlah_personil' => 3,
            'uraian_pekerjaan' => 'Pemangkasan pinggir jalan',
        ]);

        PemeliharaanTaman::create([
            'tanggal' => now()->format('Y-m-d').' 10:00:00',
            'tim' => PemeliharaanTaman::timNames()[0],
            'taman_id' => null,
            'lokasi_pelaksanaan' => 'Trotoar kompleks',
            'jumlah_personil' => 2,
            'uraian_pekerjaan' => 'Pembersihan semak',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.pemeliharaan.index'))
            ->assertOk()
            ->assertSee('Evaluasi Pemeliharaan RTH')
            ->assertSee('Luasan Terpelihara')
            ->assertSee('Belum Terpelihara')
            ->assertSee('Lokasi di luar wilayah pemeliharaan')
            ->assertSee('(67%)', false)
            ->assertDontSee('Total Personil')
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
