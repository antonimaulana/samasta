<?php

namespace Tests\Feature;

use App\Models\Kelurahan;
use App\Models\Taman;
use App\Support\RthWilayahStatisticsBuilder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RthWilayahStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_rth_page_shows_wilayah_statistics_when_taman_exists(): void
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
        ]);

        $this->get(route('rth.index'))
            ->assertOk()
            ->assertSee('Statistik RTH per Wilayah Administratif')
            ->assertSee('Batam Kota')
            ->assertSee('Belian');
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
}
