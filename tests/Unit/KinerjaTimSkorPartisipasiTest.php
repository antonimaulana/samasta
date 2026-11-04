<?php

namespace Tests\Unit;

use App\Support\Evaluasi\KinerjaTimReportBuilder;
use Database\Seeders\TimPelaksanaSeeder;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KinerjaTimSkorPartisipasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
        $this->seed(TimPelaksanaSeeder::class);
    }

    public function test_skor_is_average_of_six_team_participation_percentages(): void
    {
        $aktif = [
            'Tim Wilayah 1' => 4,
            'Tim Wilayah 2' => 2,
            'Tim Wilayah 3' => 0,
            'Tim Wilayah 4' => 0,
            'Tim Armada' => 1,
            'Tim Nursery' => 0,
        ];
        $roster = [
            'Tim Wilayah 1' => 4,
            'Tim Wilayah 2' => 4,
            'Tim Wilayah 3' => 3,
            'Tim Wilayah 4' => 3,
            'Tim Armada' => 2,
            'Tim Nursery' => 2,
        ];

        // (100 + 50 + 0 + 0 + 50 + 0) / 6 = 33.33 → 33
        $this->assertSame(33, KinerjaTimReportBuilder::computeSkorPartisipasi($aktif, $roster));
    }

    public function test_skor_null_when_no_roster_petugas(): void
    {
        $this->assertNull(KinerjaTimReportBuilder::computeSkorPartisipasi([], []));
    }

    public function test_team_without_roster_contributes_zero_to_sum(): void
    {
        $aktif = ['Tim Wilayah 1' => 2];
        $roster = ['Tim Wilayah 1' => 2];

        // 100% for one team, five others 0 → 100/6 = 17
        $this->assertSame(17, KinerjaTimReportBuilder::computeSkorPartisipasi($aktif, $roster));
    }
}
