<?php

namespace App\Support;

use App\Models\Taman;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Capaian RTH per tahun (Laporan Taman), baris a–g:
 * a = jumlah luas RTH dikelola (kumulatif tahun verifikasi ≤ kolom tahun)
 * b = jumlah lokasi (unit), idem
 * c = luas RTH publik RTRW (tetap)
 * d = a ÷ c (%)
 * e = a − penyesuaian tidak terpelihara (min 0)
 * f = b
 * g = e ÷ a (%)
 *
 * Aturan kolom tahun Y: lokasi dihitung jika pemutakhiran/verifikasi data
 * pada atau sebelum 31 Desember Y (termasuk seluruh tahun Y dan tahun-tahun
 * sebelumnya). Lokasi dengan verifikasi setelah Y tidak masuk kolom Y.
 * Lokasi tanpa tanggal verifikasi dianggap ikut semua kolom (legacy).
 */
class TamanRthYearlySummary
{
    public const COLUMN_RULE_LABEL = 'Kolom tahun Y = lokasi dengan verifikasi data s.d. 31 Des Y (Y ke bawah, termasuk Y).';
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
     * Kolom tahun Y: verifikasi ≤ akhir tahun Y (kumulatif “Y ke bawah”), atau belum pernah diverifikasi.
     */
    public static function applyYearColumnScope(Builder $query, int $year): Builder
    {
        $endOfYear = Carbon::create($year, 12, 31, 23, 59, 59, config('app.timezone'));

        return $query->where(function (Builder $builder) use ($endOfYear) {
            $builder->where(function (Builder $verified) use ($endOfYear) {
                $verified->whereNotNull('data_verified_at')
                    ->where('data_verified_at', '<=', $endOfYear);
            })->orWhereNull('data_verified_at');
        });
    }
}
