<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Taman;
use App\Models\User;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TamanLaporanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_admin_can_view_taman_laporan_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        Taman::create([
            'nama_taman' => 'Taman Laporan Uji',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1500,
            'alamat' => 'Alamat laporan',
            'deskripsi' => 'Deskripsi laporan.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.taman-laporan.index'))
            ->assertOk()
            ->assertSee('Laporan Taman')
            ->assertSee('Capaian Ruang Terbuka Hijau (RTH) per Tahun')
            ->assertSee('Rekap Ruang Terbuka Hijau (RTH) per Kategori')
            ->assertSee('Rekap Ruang Terbuka Hijau (RTH) per Wilayah')
            ->assertSee('Per Kecamatan')
            ->assertSee('Per Kelurahan')
            ->assertSee('Daftar Ruang Terbuka Hijau (RTH) per Kategori')
            ->assertSee('Jumlah Luas RTH yang dikelola oleh Dinas')
            ->assertSee('Taman Laporan Uji');
    }

    public function test_admin_can_export_taman_laporan_pdf(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        Taman::create([
            'nama_taman' => 'Taman PDF Uji',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1500,
            'alamat' => 'Alamat PDF',
            'deskripsi' => 'Deskripsi PDF.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.taman-laporan.export-pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_laporan_pdf_includes_all_report_sections(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        Taman::create([
            'nama_taman' => 'Taman PDF Section',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1500,
            'alamat' => 'Alamat PDF',
            'deskripsi' => 'Deskripsi PDF.',
        ]);

        $this->actingAs($admin);

        $html = view('admin.taman_laporan.pdf', [
            ...app(\App\Support\TamanReportBuilder::class)->build(request()),
            'generatedAt' => now(),
        ])->render();

        $this->assertStringContainsString('Capaian Ruang Terbuka Hijau (RTH) per Tahun', $html);
        $this->assertStringContainsString('Rekap Ruang Terbuka Hijau (RTH) per Kategori', $html);
        $this->assertStringContainsString('Rekap Ruang Terbuka Hijau (RTH) per Wilayah', $html);
        $this->assertStringContainsString('Daftar Ruang Terbuka Hijau (RTH) per Kategori', $html);
        $this->assertStringContainsString('Taman PDF Section', $html);
    }

    public function test_legacy_export_route_redirects_to_laporan_export(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.tamans.export-pdf', ['kategori' => 'Taman Kota']))
            ->assertRedirect(route('admin.taman-laporan.export-pdf', ['kategori' => 'Taman Kota']));
    }

    public function test_viewer_can_access_taman_laporan(): void
    {
        $viewer = User::factory()->create(['role' => User::ROLE_VIEWER]);

        $this->actingAs($viewer)
            ->get(route('admin.taman-laporan.index'))
            ->assertOk();
    }

    public function test_laporan_daftar_taman_supports_sort_by_nama(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $kelurahanId = Kelurahan::query()->value('id');

        Taman::create([
            'nama_taman' => 'Zebra Laporan',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat Z',
            'deskripsi' => 'Deskripsi Z',
        ]);
        Taman::create([
            'nama_taman' => 'Alpha Laporan',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat A',
            'deskripsi' => 'Deskripsi A',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.taman-laporan.index', ['sort' => 'nama', 'direction' => 'asc']))
            ->assertOk()
            ->assertSee('sort=nama', false)
            ->assertSeeInOrder(['Alpha Laporan', 'Zebra Laporan'])
            ->assertSee('Lihat');
    }
}
