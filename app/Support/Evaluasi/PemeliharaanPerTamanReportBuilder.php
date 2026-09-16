<?php

namespace App\Support\Evaluasi;

use App\Models\PemeliharaanTaman;
use App\Models\Taman;
use App\Models\User;
use App\Support\OperatorWilayahScope;
use App\Support\ReportPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PemeliharaanPerTamanReportBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(Request $request, ?User $user, bool $forPdf = false): array
    {
        [$from, $to, $labelPeriode, $bulan, $tahun, $mode, $dari, $sampai] = ReportPeriod::resolve($request);
        $tim = (string) $request->input('tim', '');
        $kategori = (string) $request->input('kategori', '');
        $scope = app(OperatorWilayahScope::class);

        $entries = $scope->scopePemeliharaan(
            PemeliharaanTaman::query()
                ->with(['taman.kelurahan.kecamatan'])
                ->whereBetween('tanggal', [$from, $to]),
            $user,
        )
            ->when($tim !== '', fn ($query) => $query->where('tim', $tim))
            ->when($kategori !== '', fn ($query) => $query->whereHas(
                'taman',
                fn ($q) => $q->where('kategori', $kategori),
            ))
            ->orderByDesc('tanggal')
            ->get();

        $rekapPerTaman = self::buildRekapPerTaman($entries);
        $ringkasanTim = self::buildRingkasanTim($entries);
        $chart = self::buildDailyChart($entries, $from, $to, $mode, $bulan, $tahun);

        $tamansInScope = $scope->scopeTamans(Taman::query(), $user)
            ->when($kategori !== '', fn ($query) => $query->where('kategori', $kategori))
            ->orderBy('nama_taman')
            ->get(['id', 'nama_taman', 'kategori', 'kelurahan_id']);

        $tamansInScope->load('kelurahan');

        $tamanTerlayaniIds = $entries
            ->pluck('taman_id')
            ->filter()
            ->unique()
            ->values();

        $tamanBelumDipelihara = $tamansInScope
            ->reject(fn (Taman $taman) => $tamanTerlayaniIds->contains($taman->id))
            ->values();

        $totalKegiatan = $entries->count();
        $totalPersonil = (int) $entries->sum('jumlah_personil');
        $totalTamanTerlayani = $tamanTerlayaniIds->count();
        $totalTamanInScope = $tamansInScope->count();
        $coveragePercent = $totalTamanInScope > 0
            ? (int) round(($totalTamanTerlayani / $totalTamanInScope) * 100)
            : 0;

        return [
            'mode' => $mode,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dari' => $dari,
            'sampai' => $sampai,
            'labelPeriode' => $labelPeriode,
            'tim' => $tim,
            'kategori' => $kategori,
            'totalKegiatan' => $totalKegiatan,
            'totalPersonil' => $totalPersonil,
            'totalTamanTerlayani' => $totalTamanTerlayani,
            'totalTamanInScope' => $totalTamanInScope,
            'coveragePercent' => $coveragePercent,
            'rekapPerTaman' => $forPdf ? $rekapPerTaman : $rekapPerTaman->take(50),
            'ringkasanTim' => $ringkasanTim,
            'tamanBelumDipelihara' => $forPdf ? $tamanBelumDipelihara : $tamanBelumDipelihara->take(20),
            'chartLabels' => $chart['labels'],
            'chartData' => $chart['data'],
            'daftarTim' => PemeliharaanTaman::timNames(),
            'daftarKategori' => Taman::query()->distinct()->orderBy('kategori')->pluck('kategori'),
            'daftarBulan' => ReportPeriod::daftarBulan(),
            'daftarTahun' => ReportPeriod::daftarTahun(5),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PemeliharaanTaman>  $entries
     * @return Collection<int, array<string, mixed>>
     */
    private static function buildRekapPerTaman(Collection $entries): Collection
    {
        return $entries
            ->groupBy(fn (PemeliharaanTaman $entry) => $entry->taman_id
                ? 'taman:'.$entry->taman_id
                : 'lokasi:'.mb_strtolower(trim($entry->lokasi_pelaksanaan ?? '')))
            ->map(function (Collection $group) {
                /** @var PemeliharaanTaman $latest */
                $latest = $group->sortByDesc('tanggal')->first();
                $taman = $latest->taman;

                return [
                    'label' => $taman?->nama_taman ?? $latest->lokasi_pelaksanaan ?? '—',
                    'kategori' => $taman?->kategori ?? 'Lokasi luar taman',
                    'wilayah' => $taman?->kelurahan?->nama
                        ?? $taman?->kelurahan?->kecamatan?->nama
                        ?? '—',
                    'jumlah_kegiatan' => $group->count(),
                    'total_personil' => (int) $group->sum('jumlah_personil'),
                    'tim_terlibat' => $group->pluck('tim')->unique()->sort()->values()->all(),
                    'tanggal_terakhir' => $latest->tanggal,
                    'taman_id' => $latest->taman_id,
                    'url' => $latest->taman_id
                        ? route('admin.pemeliharaan-tamans.index', ['taman_id' => $latest->taman_id])
                        : null,
                ];
            })
            ->sortByDesc('jumlah_kegiatan')
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PemeliharaanTaman>  $entries
     * @return Collection<int, array{tim: string, jumlah: int, personil: int, taman: int}>
     */
    private static function buildRingkasanTim(Collection $entries): Collection
    {
        return $entries
            ->groupBy('tim')
            ->map(fn (Collection $group, string $tim) => [
                'tim' => $tim,
                'jumlah' => $group->count(),
                'personil' => (int) $group->sum('jumlah_personil'),
                'taman' => $group->pluck('taman_id')->filter()->unique()->count(),
            ])
            ->sortByDesc('jumlah')
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PemeliharaanTaman>  $entries
     * @return array{labels: list<string>, data: list<int>}
     */
    private static function buildDailyChart(
        Collection $entries,
        \Carbon\Carbon $from,
        \Carbon\Carbon $to,
        string $mode,
        int $bulan,
        int $tahun,
    ): array {
        $labels = [];
        $data = [];

        if ($mode === 'bulan') {
            $daysInMonth = $from->daysInMonth;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $labels[] = (string) $day;
                $date = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);
                $data[] = $entries->filter(fn (PemeliharaanTaman $entry) => $entry->tanggal?->toDateString() === $date)->count();
            }
        } else {
            $cursor = $from->copy()->startOfDay();
            $end = $to->copy()->startOfDay();
            while ($cursor->lte($end)) {
                $labels[] = $cursor->format('d/m');
                $date = $cursor->toDateString();
                $data[] = $entries->filter(fn (PemeliharaanTaman $entry) => $entry->tanggal?->toDateString() === $date)->count();
                $cursor->addDay();
            }
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
