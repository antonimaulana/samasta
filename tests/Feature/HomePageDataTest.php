<?php

namespace Tests\Feature;

use App\Models\Taman;
use App\Support\HomePageData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_featured_tamans_limits_results_and_prioritizes_photo(): void
    {
        $withoutPhoto = Taman::create([
            'nama_taman' => 'Tanpa Foto',
            'kategori' => 'Taman Kota',
            'luasan' => 1000,
            'alamat' => 'Alamat A',
            'deskripsi' => 'Deskripsi taman tanpa foto.',
        ]);

        $withPhoto = Taman::create([
            'nama_taman' => 'Dengan Foto',
            'kategori' => 'Taman Kota',
            'luasan' => 1200,
            'alamat' => 'Alamat B',
            'deskripsi' => 'Deskripsi taman dengan foto.',
            'foto' => 'taman/sample.jpg',
        ]);

        $featured = HomePageData::featuredTamans(6);

        $this->assertLessThanOrEqual(6, $featured->count());
        $this->assertTrue($featured->contains('id', $withPhoto->id));
        $this->assertSame($withPhoto->id, $featured->first()->id);
        $this->assertTrue($featured->contains('id', $withoutPhoto->id));
    }

    public function test_stats_are_cached(): void
    {
        HomePageData::forgetStatsCache();

        Taman::withoutEvents(function () {
            Taman::create([
                'nama_taman' => 'Taman Cache Test',
                'kategori' => 'Taman Kota',
                'luasan' => 800,
                'alamat' => 'Alamat cache',
                'deskripsi' => 'Deskripsi taman cache test.',
            ]);
        });

        $first = HomePageData::stats();
        $second = HomePageData::stats();

        $this->assertSame($first, $second);
    }

    public function test_stats_cache_is_invalidated_when_taman_is_saved(): void
    {
        HomePageData::forgetStatsCache();
        HomePageData::stats();

        Taman::create([
            'nama_taman' => 'Taman Invalidasi Cache',
            'kategori' => 'Taman Kota',
            'luasan' => 900,
            'alamat' => 'Alamat invalidasi',
            'deskripsi' => 'Deskripsi invalidasi cache.',
        ]);

        $this->assertSame(1, HomePageData::stats()['totalTaman']);
    }

    public function test_homepage_loads_successfully(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Ketertiban Umum di Taman')
            ->assertSee('Perda Kota Batam Nomor 16 Tahun 2007');
    }
}
