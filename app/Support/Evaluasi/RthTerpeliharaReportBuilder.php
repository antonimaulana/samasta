<?php

namespace App\Support\Evaluasi;

use App\Models\Kecamatan;
use App\Models\PemeliharaanTaman;
use App\Models\Taman;
use App\Models\User;
use App\Support\OperatorWilayahScope;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RthTerpeliharaReportBuilder
{
    /** @var list<int> */
    public const FRESH_DAY_OPTIONS = [30, 45, 60, 90];

    /**
     * @return array<string, mixed>
     */
    public static function build(Request $request, ?User $user, bool $forPdf = false): array
    {
        $kategori = (string) $request->input('kategori', '');
        $kecamatanId = (string) $request->input('kecamatan_id', '');
        $freshDays = self::resolveFreshDays($request);
        $cutoff = now()->subDays($freshDays);
        $scope = app(OperatorWilayahScope::class);

        $tamans = $scope->scopeTamans(Taman::query(), $user)
            ->with(['kelurahan.kecamatan'])
            ->when($kategori !== '', fn ($query) => $query->where('kategori', $kategori))
            ->when($kecamatanId !== '', fn ($query) => $query->whereHas(
                'kelurahan',
                fn ($kelurahanQuery) => $kelurahanQuery->where('kecamatan_id', $kecamatanId),
            ))
            ->orderBy('nama_taman')
            ->get();

        $tamanIds = $tamans->pluck('id');

        $latestByTaman = $scope->scopePemeliharaan(
            PemeliharaanTaman::query()
                ->whereIn('taman_id', $tamanIds)
                ->whereNotNull('taman_id'),
            $user,
        )
            ->get(['taman_id', 'tanggal'])
            ->groupBy('taman_id')
            ->map(fn (Collection $group) => $group->max('tanggal'));

        $rows = $tamans->map(function (Taman $taman) use ($latestByTaman, $cutoff) {
            /** @var Carbon|null $latestDate */
            $latestDate = $latestByTaman->get($taman->id);
            $isTerpelihara = $latestDate !== null && $latestDate->greaterThanOrEqualTo($cutoff);

            return [
                'taman' => $taman,
                'taman_id' => $taman->id,
                'nama' => $taman->nama_taman,
                'kategori' => $taman->kategori ?? '—',
                'wilayah' => $taman->kelurahan?->kecamatan?->nama
                    ?? $taman->kelurahan?->nama
                    ?? '—',
                'kecamatan_id' => $taman->kelurahan?->kecamatan_id,
                'luasan' => (int) $taman->luasan,
                'is_terpelihara' => $isTerpelihara,
                'latest_maintenance' => $latestDate,
                'hari_sejak' => $latestDate?->diffInDays(now()),
                'url' => route('admin.pemeliharaan-tamans.index', ['taman_id' => $taman->id]),
                'taman_url' => route('admin.tamans.show', $taman),
            ];
        });

        $totalLokasi = $rows->count();
        $lokasiTerpelihara = $rows->where('is_terpelihara', true)->count();
        $totalLuasan = (int) $rows->sum('luasan');
        $luasanTerpelihara = (int) $rows->where('is_terpelihara', true)->sum('luasan');

        $persenLokasi = $totalLokasi > 0
            ? round(($lokasiTerpelihara / $totalLokasi) * 100, 1)
            : 0.0;
        $persenLuasan = $totalLuasan > 0
            ? round(($luasanTerpelihara / $totalLuasan) * 100, 1)
            : 0.0;

        $rtrwLuasan = (int) config('simtaman.rth_laporan.luasan_rth_publik_rtrw_m2', 52_990_000);
        $persenRtrw = $rtrwLuasan > 0
            ? round(($totalLuasan / $rtrwLuasan) * 100, 2)
            : 0.0;

        $rekapPerKategori = self::buildRekapGroup($rows, 'kategori');
        $rekapPerKecamatan = self::buildRekapGroup($rows, 'wilayah');

        $belumTerpelihara = $rows
            ->where('is_terpelihara', false)
            ->sortBy(fn (array $row) => $row['latest_maintenance']?->timestamp ?? 0)
            ->values();

        $chart = self::buildKategoriChart($rekapPerKategori);

        return [
            'freshDays' => $freshDays,
            'cutoffDate' => $cutoff,
            'labelSnapshot' => 'Snapshot per '.now()->timezone(config('app.timezone'))->translatedFormat('d F Y'),
            'labelKriteria' => "Terpelihara = ada pemeliharaan rutin dalam {$freshDays} hari terakhir",
            'kategori' => $kategori,
            'kecamatan_id' => $kecamatanId,
            'totalLokasi' => $totalLokasi,
            'lokasiTerpelihara' => $lokasiTerpelihara,
            'lokasiBelumTerpelihara' => $totalLokasi - $lokasiTerpelihara,
            'persenLokasi' => $persenLokasi,
            'totalLuasan' => $totalLuasan,
            'luasanTerpelihara' => $luasanTerpelihara,
            'luasanBelumTerpelihara' => max(0, $totalLuasan - $luasanTerpelihara),
            'persenLuasan' => $persenLuasan,
            'rtrwLuasan' => $rtrwLuasan,
            'persenRtrw' => $persenRtrw,
            'rekapPerKategori' => $rekapPerKategori,
            'rekapPerKecamatan' => $rekapPerKecamatan,
            'belumTerpelihara' => $forPdf ? $belumTerpelihara : $belumTerpelihara->take(30),
            'chartLabels' => $chart['labels'],
            'chartTerpelihara' => $chart['terpelihara'],
            'chartBelum' => $chart['belum'],
            'daftarKategori' => collect(Taman::KATEGORI),
            'daftarKecamatan' => Kecamatan::query()->orderBy('nama')->get(['id', 'nama']),
            'daftarFreshDays' => self::FRESH_DAY_OPTIONS,
        ];
    }

    private static function resolveFreshDays(Request $request): int
    {
        $default = (int) config('simtaman.ar.maintenance_fresh_days', 60);
        $freshDays = (int) $request->input('fresh_days', $default);

        if (! in_array($freshDays, self::FRESH_DAY_OPTIONS, true)) {
            return $default;
        }

        return $freshDays;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private static function buildRekapGroup(Collection $rows, string $groupKey): Collection
    {
        return $rows
            ->groupBy(fn (array $row) => (string) ($row[$groupKey] ?: '—'))
            ->map(function (Collection $group, string $label) {
                $totalLokasi = $group->count();
                $lokasiTerpelihara = $group->where('is_terpelihara', true)->count();
                $totalLuasan = (int) $group->sum('luasan');
                $luasanTerpelihara = (int) $group->where('is_terpelihara', true)->sum('luasan');

                return [
                    'label' => $label,
                    'total_lokasi' => $totalLokasi,
                    'lokasi_terpelihara' => $lokasiTerpelihara,
                    'lokasi_belum' => $totalLokasi - $lokasiTerpelihara,
                    'persen_lokasi' => $totalLokasi > 0
                        ? round(($lokasiTerpelihara / $totalLokasi) * 100, 1)
                        : 0.0,
                    'total_luasan' => $totalLuasan,
                    'luasan_terpelihara' => $luasanTerpelihara,
                    'persen_luasan' => $totalLuasan > 0
                        ? round(($luasanTerpelihara / $totalLuasan) * 100, 1)
                        : 0.0,
                ];
            })
            ->sortByDesc('total_luasan')
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rekapPerKategori
     * @return array{labels: list<string>, terpelihara: list<int>, belum: list<int>}
     */
    private static function buildKategoriChart(Collection $rekapPerKategori): array
    {
        return [
            'labels' => $rekapPerKategori->pluck('label')->all(),
            'terpelihara' => $rekapPerKategori->pluck('lokasi_terpelihara')->map(fn ($v) => (int) $v)->all(),
            'belum' => $rekapPerKategori->pluck('lokasi_belum')->map(fn ($v) => (int) $v)->all(),
        ];
    }
}
