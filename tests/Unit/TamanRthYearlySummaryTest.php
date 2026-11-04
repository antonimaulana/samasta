<?php

namespace Tests\Unit;

use App\Models\Kelurahan;
use App\Models\Taman;
use App\Models\User;
use App\Support\TamanRthYearlySummary;
use Database\Seeders\WilayahBatamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TamanRthYearlySummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayahBatamSeeder::class);
    }

    public function test_builds_yearly_metrics_from_tahun_pembangunan(): void
    {
        $kelurahanId = Kelurahan::query()->value('id');

        Taman::create([
            'nama_taman' => 'Taman 2024',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
            'tahun_pembangunan' => 2024,
        ]);

        Taman::create([
            'nama_taman' => 'Taman 2025',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 2000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
            'tahun_pembangunan' => 2025,
        ]);

        Taman::create([
            'nama_taman' => 'Taman 2026',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 500,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
            'tahun_pembangunan' => 2026,
        ]);

        $summary = app(TamanRthYearlySummary::class)->build();

        $this->assertSame([2025, 2026], $summary['years']);

        $rowA = collect($summary['rows'])->firstWhere('key', 'A');
        $rowB = collect($summary['rows'])->firstWhere('key', 'B');
        $rowC = collect($summary['rows'])->firstWhere('key', 'C');
        $rowD = collect($summary['rows'])->firstWhere('key', 'D');
        $rowE = collect($summary['rows'])->firstWhere('key', 'E');
        $rowF = collect($summary['rows'])->firstWhere('key', 'F');
        $rowG = collect($summary['rows'])->firstWhere('key', 'G');

        $this->assertSame(3000, $rowA['values'][2025]);
        $this->assertSame(3500, $rowA['values'][2026]);
        $this->assertSame(2, $rowB['values'][2025]);
        $this->assertSame(3, $rowB['values'][2026]);
        $this->assertSame(52_990_000, $rowC['values'][2025]);
        $this->assertSame(52_990_000, $rowC['values'][2026]);
        $this->assertEqualsWithDelta(round(3000 / 52_990_000 * 100, 2), $rowD['values'][2025], 0.0001);
        $this->assertSame(0, $rowE['values'][2025]);
        $this->assertSame($rowB['values'][2025], $rowF['values'][2025]);
        $this->assertSame($rowB['values'][2026], $rowF['values'][2026]);
        $this->assertEqualsWithDelta(0.0, $rowG['values'][2025], 0.0001);
    }

    public function test_row_e_is_luasan_minus_penyesuaian_and_g_is_ratio(): void
    {
        $kelurahanId = Kelurahan::query()->value('id');

        Taman::create([
            'nama_taman' => 'Taman Luas',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 1_000_000,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
            'tahun_pembangunan' => 2025,
        ]);

        $summary = app(TamanRthYearlySummary::class)->build();
        $rowA = collect($summary['rows'])->firstWhere('key', 'A');
        $rowE = collect($summary['rows'])->firstWhere('key', 'E');
        $rowG = collect($summary['rows'])->firstWhere('key', 'G');

        $a = $rowA['values'][2025];
        $e = $rowE['values'][2025];
        $this->assertSame(1_000_000, $a);
        $this->assertSame(1_000_000 - 556_600, $e);
        $this->assertEqualsWithDelta(round($e / $a * 100, 2), $rowG['values'][2025], 0.0001);
    }

    public function test_year_column_is_cumulative_by_tahun_pembangunan(): void
    {
        $kelurahanId = Kelurahan::query()->value('id');

        Taman::create([
            'nama_taman' => 'Taman 2024',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 100,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
            'tahun_pembangunan' => 2024,
        ]);

        Taman::create([
            'nama_taman' => 'Taman 2025',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 200,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
            'tahun_pembangunan' => 2025,
        ]);

        Taman::create([
            'nama_taman' => 'Taman 2026',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 300,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
            'tahun_pembangunan' => 2026,
        ]);

        Taman::create([
            'nama_taman' => 'Taman 2027',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 400,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
            'tahun_pembangunan' => 2027,
        ]);

        Taman::create([
            'nama_taman' => 'Tanpa Tahun',
            'kategori' => 'Taman Kota',
            'kelurahan_id' => $kelurahanId,
            'luasan' => 999,
            'alamat' => 'Alamat',
            'deskripsi' => 'Deskripsi',
        ]);

        $summary = app(TamanRthYearlySummary::class)->build();
        $rowB = collect($summary['rows'])->firstWhere('key', 'B');

        $this->assertSame(2, $rowB['values'][2025], 'Kolom 2025: tahun pembangunan ≤ 2025');
        $this->assertSame(3, $rowB['values'][2026], 'Kolom 2026: tahun pembangunan ≤ 2026 (tanpa 2027+)');
    }
}
