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

    public function test_legacy_rth_terpelihara_route_redirects_to_pemeliharaan_rth(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.rth-terpelihara.index', ['fresh_days' => 60]))
            ->assertRedirect(route('admin.evaluasi.pemeliharaan.index', ['fresh_days' => 60]));
    }

    public function test_pemeliharaan_rth_page_shows_luasan_terpelihara_snapshot(): void
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

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.pemeliharaan.index'))
            ->assertOk()
            ->assertSee('Evaluasi Pemeliharaan RTH')
            ->assertSee('Luasan Terpelihara')
            ->assertSee('Belum Terpelihara')
            ->assertSee('1.200')
            ->assertSee('50%');
    }

    public function test_legacy_rth_terpelihara_pdf_redirects_to_pemeliharaan_export(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.evaluasi.rth-terpelihara.export-pdf'))
            ->assertRedirect(route('admin.evaluasi.pemeliharaan.export-pdf'));
    }
}
