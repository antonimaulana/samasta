<?php

namespace App\Support;

use App\Models\Taman;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TamanRthYearlySummary
{
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
        $penyesuaianTidakTerpelihara = (int) config('simtaman.rth_laporan.luasan_penyesuaian_tidak_terpelihara_m2', 300_000);

        $years = $this->years();
        $metrics = [];

        foreach ($years as $year) {
            $query = (clone $baseQuery)->where(function (Builder $builder) use ($year) {
                $builder->whereYear('data_verified_at', '<=', $year)
                    ->orWhereNull('data_verified_at');
            });

            $luasan = (int) (clone $query)->sum('luasan');
            $lokasi = (int) (clone $query)->count();
            $luasanTerpelihara = max(0, $luasan - $penyesuaianTidakTerpelihara);

            $metrics[$year] = [
                'luasan' => $luasan,
                'lokasi' => $lokasi,
                'luasan_terpelihara' => $luasanTerpelihara,
                'persen_rtrw' => $rtrwLuasan > 0 ? ($luasan / $rtrwLuasan) * 100 : 0.0,
                'persen_terpelihara' => $luasan > 0 ? ($luasanTerpelihara / $luasan) * 100 : 0.0,
            ];
        }

        $valueForYear = fn (string $field) => collect($years)
            ->mapWithKeys(fn (int $year) => [$year => $metrics[$year][$field]])
            ->all();

        return [
            'years' => $years,
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
}
