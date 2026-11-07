<?php

namespace Tests\Unit;

use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Support\TimPelaksanaPdfOrder;
use Database\Seeders\TimPelaksanaSeeder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TimPelaksanaPdfOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayahBatamSeeder::class);
        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_sort_grouped_tim_follows_wilayah_nursery_armada_order(): void
    {
        $grouped = collect([
            'Tim Armada' => collect(['a']),
            'Tim Wilayah 2' => collect(['b']),
            'Tim Wilayah 1' => collect(['c']),
            'Tim Nursery' => collect(['d']),
        ]);

        $sorted = TimPelaksanaPdfOrder::sortGrouped($grouped);

        $this->assertSame(
            ['Tim Wilayah 1', 'Tim Wilayah 2', 'Tim Nursery', 'Tim Armada'],
            $sorted->keys()->all(),
        );
    }

    public function test_group_pemangkasan_progres_by_tim_field(): void
    {
        $permohonan = new Pemangkasan([
            'pelaksana' => ['Tim Wilayah 1'],
        ]);

        $entries = collect([
            new PemangkasanProgres(['tim' => 'Tim Wilayah 2', 'hari_ke' => 1]),
            new PemangkasanProgres(['tim' => 'Tim Wilayah 1', 'hari_ke' => 1]),
        ]);

        $grouped = TimPelaksanaPdfOrder::groupPemangkasanProgres($entries, $permohonan);

        $this->assertInstanceOf(Collection::class, $grouped);
        $this->assertTrue($grouped->has('Tim Wilayah 1'));
        $this->assertTrue($grouped->has('Tim Wilayah 2'));
        $this->assertSame('Tim Wilayah 1', $grouped->keys()->first());
    }
}
