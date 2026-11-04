<?php

namespace App\Support;

use App\Models\Taman;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
/**
 * Capaian RTH per tahun (Laporan Taman), baris a–g:
 * a = jumlah luas RTH dikelola (kumulatif tahun pembangunan ≤ kolom tahun)
 * b = jumlah lokasi (unit), idem
 * c = luas RTH publik RTRW (tetap)
 * d = a ÷ c (%)
 * e = a − penyesuaian tidak terpelihara (min 0)
 * f = b
 * g = e ÷ a (%)
 *
 * Aturan kolom tahun Y: lokasi dihitung jika tahun pembangunan ≤ Y.
 */
class TamanRthYearlySummary
{
    public const COLUMN_RULE_LABEL = 'Kolom tahun Y = RTH dengan tahun pembangunan Y ke bawah (≤ Y).';
    /**
     * @return list<int>
     */
    public function years(): array
    {
        $years = config('simtaman.rth_laporan.tahun', [2025, 2026]);

        return collect($years)
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     years: list<int>,
     *     rows: list<array{key: string, label: string, values: array<int, float|int>, format: string}>
     * }
     */
    public function build(?User $user = null): array
    {
        $scope = app(OperatorWilayahScope::class);
        $baseQuery = $scope->scopeTamans(Taman::query(), $user);

        $rtrwLuasan = (int) config('simtaman.rth_laporan.luasan_rth_publik_rtrw_m2', 52_990_000);
        $penyesuaianTidakTerpelihara = (int) config('simtaman.rth_laporan.luasan_penyesuaian_tidak_terpelihara_m2', 556_600);

        $years = $this->years();
        $metrics = [];

        foreach ($years as $year) {
            $query = self::applyYearColumnScope(clone $baseQuery, $year);

            $luasan = (int) (clone $query)->sum('luasan');
            $lokasi = (int) (clone $query)->count();
            $luasanTerpelihara = max(0, $luasan - $penyesuaianTidakTerpelihara);

            $metrics[$year] = [
                'luasan' => $luasan,
                'lokasi' => $lokasi,
                'luasan_terpelihara' => $luasanTerpelihara,
                'persen_rtrw' => $rtrwLuasan > 0
                    ? round(($luasan / $rtrwLuasan) * 100, 2)
                    : 0.0,
                'persen_terpelihara' => $luasan > 0
                    ? round(($luasanTerpelihara / $luasan) * 100, 2)
                    : 0.0,
            ];
        }

        $valueForYear = fn (string $field) => collect($years)
            ->mapWithKeys(fn (int $year) => [$year => $metrics[$year][$field]])
            ->all();

        return [
            'years' => $years,
            'columnRuleLabel' => self::COLUMN_RULE_LABEL,
            'rtrwLuasan' => $rtrwLuasan,
            'rows' => [
                [
                    'key' => 'A',
                    'label' => 'Jumlah Luas RTH yang dikelola oleh Dinas (m²)',
                    'values' => $valueForYear('luasan'),
                    'format' => 'area',
                ],
                [
                    'key' => 'B',
                    'label' => 'Jumlah Lokasi RTH yang dikelola oleh Dinas (Unit)',
                    'values' => $valueForYear('lokasi'),
                    'format' => 'count',
                ],
                [
                    'key' => 'C',
                    'label' => 'Jumlah Luas RTH Publik sesuai RTRW (m²)',
                    'values' => collect($years)->mapWithKeys(fn (int $year) => [$year => $rtrwLuasan])->all(),
                    'format' => 'area',
                ],
                [
                    'key' => 'D',
                    'label' => 'Persentase Luas RTH yang dikelola oleh Dinas dengan RTH Publik sesuai RTRW',
                    'values' => $valueForYear('persen_rtrw'),
                    'format' => 'percent',
                ],
                [
                    'key' => 'E',
                    'label' => 'Jumlah Luas RTH yang dikelola oleh Dinas dalam kondisi terpelihara (m²)',
                    'values' => $valueForYear('luasan_terpelihara'),
                    'format' => 'area',
                ],
                [
                    'key' => 'F',
                    'label' => 'Jumlah Lokasi RTH yang dikelola oleh Dinas dalam kondisi terpelihara (Unit)',
                    'values' => $valueForYear('lokasi'),
                    'format' => 'count',
                ],
                [
                    'key' => 'G',
                    'label' => 'Persentase Luas RTH dalam kondisi terpelihara dengan total RTH yang dikelola',
                    'values' => $valueForYear('persen_terpelihara'),
                    'format' => 'percent',
                ],
            ],
        ];
    }

    /**
     * Kolom tahun Y: tahun pembangunan ≤ Y (kumulatif “Y ke bawah”).
     */
    public static function applyYearColumnScope(Builder $query, int $year): Builder
    {
        return $query->whereNotNull('tahun_pembangunan')
            ->where('tahun_pembangunan', '<=', $year);
    }
}
