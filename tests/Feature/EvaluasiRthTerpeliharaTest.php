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

class EvaluasiRthTerpeliharaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_admin_can_view_rth_terpelihara_evaluasi_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        $tamanTerpelihara = Taman::create([
            'nama_taman' => 'Taman RTH Terawat',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1200,
            'alamat' => 'Alamat terawat',
            'deskripsi' => 'Deskripsi terawat.',
        ]);

        $tamanPerluPerhatian = Taman::create([
            'nama_taman' => 'Taman RTH Lama',
            'kategori' => 'Taman Lingkungan',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 900,
            'alamat' => 'Alamat lama',
            'deskripsi' => 'Deskripsi lama.',
        ]);

        PemeliharaanTaman::create([
            'tanggal' => now()->format('Y-m-d').' 08:00:00',
            'tim' => PemeliharaanTaman::timNames()[0],
            'taman_id' => $tamanTerpelihara->id,
            'lokasi_pelaksanaan' => $tamanTerpelihara->nama_taman,
            'jumlah_personil' => 4,
            'uraian_pekerjaan' => 'Pemeliharaan rutin',
        ]);

        PemeliharaanTaman::create([
            'tanggal' => now()->subDays(120)->format('Y-m-d').' 08:00:00',
            'tim' => PemeliharaanTaman::timNames()[0],
            'taman_id' => $tamanPerluPerhatian->id,
            'lokasi_pelaksanaan' => $tamanPerluPerhatian->nama_taman,
            'jumlah_personil' => 3,
            'uraian_pekerjaan' => 'Pemeliharaan lama',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.evaluasi.rth-terpelihara.index'));

        $response->assertOk()
            ->assertSee('Evaluasi RTH Terpelihara')
            ->assertSee('Lokasi Perlu Perhatian')
            ->assertSee('Taman RTH Lama')
            ->assertSee('1')
            ->assertSee('50%');
    }

    public function test_admin_can_export_rth_terpelihara_evaluasi_pdf(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('PHP GD extension is required for PDF export.');
        }

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.rth-terpelihara.export-pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
