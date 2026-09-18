<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class PublicRthStatisticsBuilder
{
    private const CACHE_KEY = 'public_rth_statistics';

    private const CACHE_TTL_SECONDS = 600;

    /**
     * @return array<string, mixed>
     */
    public function build(bool $fresh = false): array
    {
        if ($fresh) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, fn () => $this->compile());
    }

    /**
     * @return array<string, mixed>
     */
    private function compile(): array
    {
        $report = app(TamanReportBuilder::class)->build(Request::create('/', 'GET'));
        $yearly = $report['rthYearlySummary'];
        $latestYear = collect($yearly['years'])->last();
        $latestMetrics = $this->extractLatestYearMetrics($yearly, $latestYear);

        $totalTaman = (int) $report['totalTaman'];
        $totalLuasan = (int) $report['totalLuasan'];
        $lengkap = (int) ($report['rekapStatusData']['Lengkap'] ?? 0);
        $belumLengkap = (int) ($report['rekapStatusData']['Belum Lengkap'] ?? 0);

        $kategoriCards = collect($report['rekapKategori'])
            ->map(function (array $row) use ($totalLuasan, $totalTaman) {
                $meta = RthKotaBatam::metaForKategori($row['kategori']);

                return [
                    'kategori' => $row['kategori'],
                    'label' => $meta['nama'],
                    'icon' => $meta['icon'],
                    'ringkas' => $meta['ringkas'],
                    'accent' => $meta['accent'],
                    'jumlah' => $row['jumlah'],
                    'luasan' => $row['luasan'],
                    'persen_luas' => $totalLuasan > 0 ? round(($row['luasan'] / $totalLuasan) * 100, 1) : 0.0,
                    'persen_lokasi' => $totalTaman > 0 ? round(($row['jumlah'] / $totalTaman) * 100, 1) : 0.0,
                ];
            })
            ->values()
            ->all();

        $kecamatanRows = $report['rekapKecamatan']
            ->map(function (array $row) use ($totalLuasan) {
                return [
                    ...$row,
                    'persen_luas' => $totalLuasan > 0 ? round(($row['luasan'] / $totalLuasan) * 100, 1) : 0.0,
                ];
            })
            ->values()
            ->all();

        return [
            'snapshotAt' => now()->timezone(config('app.timezone')),
            'totalTaman' => $totalTaman,
            'totalLuasan' => $totalLuasan,
            'rekapStatusData' => $report['rekapStatusData'],
            'persenDataLengkap' => $totalTaman > 0 ? (int) round(($lengkap / $totalTaman) * 100) : 0,
            'jumlahDataLengkap' => $lengkap,
            'jumlahBelumLengkap' => $belumLengkap,
            'kategoriCards' => $kategoriCards,
            'chartKategoriLabels' => collect($kategoriCards)->pluck('label')->all(),
            'chartKategoriLuasan' => collect($kategoriCards)->pluck('luasan')->all(),
            'chartKategoriColors' => collect($kategoriCards)->pluck('accent.chart')->all(),
            'kecamatanRows' => $kecamatanRows,
            'kelurahanGrouped' => $report['rekapKelurahanPerKecamatan']->all(),
            'rthYearlySummary' => $yearly,
            'latestYear' => $latestYear,
            'latestMetrics' => $latestMetrics,
            'rtrwLuasan' => (int) ($yearly['rtrwLuasan'] ?? config('simtaman.rth_laporan.luasan_rth_publik_rtrw_m2', 52_990_000)),
            'hasData' => $totalTaman > 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $yearly
     * @return array<string, float|int|null>
     */
    private function extractLatestYearMetrics(array $yearly, ?int $year): array
    {
        if ($year === null) {
            return [
                'luasan' => null,
                'lokasi' => null,
                'luasan_terpelihara' => null,
                'persen_rtrw' => null,
                'persen_terpelihara' => null,
            ];
        }

        $value = fn (string $key) => collect($yearly['rows'] ?? [])
            ->firstWhere('key', $key)['values'][$year] ?? null;

        return [
            'luasan' => $value('A') !== null ? (int) $value('A') : null,
            'lokasi' => $value('B') !== null ? (int) $value('B') : null,
            'luasan_terpelihara' => $value('E') !== null ? (int) $value('E') : null,
            'persen_rtrw' => $value('D') !== null ? (float) $value('D') : null,
            'persen_terpelihara' => $value('G') !== null ? (float) $value('G') : null,
        ];
    }

    public static function formatArea(?int $value): string
    {
        return $value !== null ? number_format($value, 0, ',', '.') : '—';
    }

    public static function formatCount(?int $value): string
    {
        return $value !== null ? number_format($value) : '—';
    }

    public static function formatPercent(?float $value, int $decimals = 1): string
    {
        return $value !== null ? number_format($value, $decimals, ',', '.').'%' : '—';
    }

    public static function formatSnapshot(Carbon $at): string
    {
        return $at->translatedFormat('d F Y, H:i').' WIB';
    }
}
