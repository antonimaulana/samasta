<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Taman;
use App\Support\PublicRthStatisticsBuilder;
use App\Support\RthWilayahStatisticsBuilder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RthWilayahStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_rth_page_shows_integrated_statistics_from_laporan_taman(): void
    {
        $this->seed(WilayahBatamSeeder::class);

        $kelurahan = Kelurahan::whereHas('kecamatan', fn ($q) => $q->where('nama', 'Batam Kota'))
            ->where('nama', 'Belian')
            ->first();

        Taman::create([
            'nama_taman' => 'Taman RTH Stat',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 5000,
            'alamat' => 'Alamat rth stat',
            'deskripsi' => 'Deskripsi taman statistik rth test.',
            'status_data' => Taman::STATUS_DATA_LENGKAP,
            'data_verified_at' => now(),
        ]);

        app(PublicRthStatisticsBuilder::class)->build(fresh: true);

        $this->get(route('rth.index'))
            ->assertOk()
            ->assertSee('Pedoman Data Resmi')
            ->assertSee('Statistik Ruang Terbuka Hijau Kota Batam')
            ->assertSee('RTH per Kategori')
            ->assertSee('Capaian RTH per Tahun')
            ->assertSee('Batam Kota')
            ->assertSee('Belian')
            ->assertSee('5.000');
    }

    public function test_statistics_builder_aggregates_per_kecamatan(): void
    {
        $this->seed(WilayahBatamSeeder::class);

        $kelurahan = Kelurahan::whereHas('kecamatan', fn ($q) => $q->where('nama', 'Sekupang'))
            ->where('nama', 'Tiban Indah')
            ->first();

        Taman::create([
            'nama_taman' => 'Taman Builder',
            'kategori' => 'Taman Lingkungan',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 3000,
            'alamat' => 'Alamat builder',
            'deskripsi' => 'Deskripsi builder test.',
        ]);

        $row = app(RthWilayahStatisticsBuilder::class)
            ->perKecamatan()
            ->firstWhere('kecamatan', 'Sekupang');

        $this->assertNotNull($row);
        $this->assertSame(1, (int) $row->jumlah_taman);
        $this->assertSame(3000, (int) $row->total_luasan);
    }

    public function test_public_rth_statistics_builder_aligns_with_laporan_taman_totals(): void
    {
        $this->seed(WilayahBatamSeeder::class);

        $kelurahan = Kelurahan::query()->first();

        Taman::create([
            'nama_taman' => 'Taman Align',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 1200,
            'alamat' => 'Alamat align',
            'deskripsi' => 'Deskripsi align test.',
        ]);

        Taman::create([
            'nama_taman' => 'Taman Align 2',
            'kategori' => 'TPU',
            'kelurahan_id' => $kelurahan->id,
            'luasan' => 800,
            'alamat' => 'Alamat align 2',
            'deskripsi' => 'Deskripsi align 2 test.',
        ]);

        $stats = app(PublicRthStatisticsBuilder::class)->build(fresh: true);

        $this->assertSame(2, $stats['totalTaman']);
        $this->assertSame(2000, $stats['totalLuasan']);
        $this->assertCount(5, $stats['kategoriCards']);
        $this->assertNotEmpty($stats['rthYearlySummary']['rows']);
    }
}
