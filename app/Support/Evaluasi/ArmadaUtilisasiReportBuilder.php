<?php

namespace App\Support\Evaluasi;

use App\Models\AlatSaranaOperasional;
use App\Models\PemangkasanProgresArmada;
use App\Models\PemeliharaanTamanArmada;
use App\Models\User;
use App\Support\ReportPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ArmadaUtilisasiReportBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(Request $request, ?User $user, bool $forPdf = false): array
    {
        [$from, $to, $labelPeriode, $bulan, $tahun, $mode, $dari, $sampai] = ReportPeriod::resolve($request);

        $armadaMaster = AlatSaranaOperasional::query()
            ->armadaInventory()
            ->orderBy('nama')
            ->get();

        $pemeliharaanRows = PemeliharaanTamanArmada::query()
            ->with(['pemeliharaanTaman.taman', 'alatSarana'])
            ->whereHas('pemeliharaanTaman', fn ($q) => $q->whereBetween('tanggal', [$from, $to]))
            ->get();

        $permohonanRows = PemangkasanProgresArmada::query()
            ->with(['progres.pemangkasan.taman', 'alatSarana'])
            ->whereHas('progres', fn ($q) => $q->whereBetween('tanggal', [$from, $to]))
            ->get();

        $rekapPerArmada = self::buildRekapPerArmada($armadaMaster, $pemeliharaanRows, $permohonanRows);
        $rekapSopir = self::buildRekapSopir($pemeliharaanRows, $permohonanRows);
        $chart = self::buildDailyChart($pemeliharaanRows, $permohonanRows, $from, $to, $mode, $bulan, $tahun);

        $totalPenugasan = $pemeliharaanRows->count() + $permohonanRows->count();
        $armadaTerpakai = $rekapPerArmada->where('total_penugasan', '>', 0)->count();
        $armadaIdle = $rekapPerArmada->where('total_penugasan', 0)->count();

        return [
            'mode' => $mode,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dari' => $dari,
            'sampai' => $sampai,
            'labelPeriode' => $labelPeriode,
            'totalPenugasan' => $totalPenugasan,
            'totalPemeliharaan' => $pemeliharaanRows->count(),
            'totalPermohonan' => $permohonanRows->count(),
            'armadaTerpakai' => $armadaTerpakai,
            'armadaIdle' => $armadaIdle,
            'totalArmada' => $armadaMaster->count(),
            'rekapPerArmada' => $forPdf ? $rekapPerArmada : $rekapPerArmada,
            'rekapSopir' => $forPdf ? $rekapSopir : $rekapSopir->take(15),
            'chartLabels' => $chart['labels'],
            'chartPemeliharaan' => $chart['pemeliharaan'],
            'chartPermohonan' => $chart['permohonan'],
            'daftarBulan' => ReportPeriod::daftarBulan(),
            'daftarTahun' => ReportPeriod::daftarTahun(5),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, AlatSaranaOperasional>  $armadaMaster
     * @param  \Illuminate\Support\Collection<int, PemeliharaanTamanArmada>  $pemeliharaanRows
     * @param  \Illuminate\Support\Collection<int, PemangkasanProgresArmada>  $permohonanRows
     * @return Collection<int, array<string, mixed>>
     */
    private static function buildRekapPerArmada(
        Collection $armadaMaster,
        Collection $pemeliharaanRows,
        Collection $permohonanRows,
    ): Collection {
        $pemeliharaanByArmada = $pemeliharaanRows->groupBy('alat_sarana_operasional_id');
        $permohonanByArmada = $permohonanRows->groupBy('alat_sarana_operasional_id');

        return $armadaMaster->map(function (AlatSaranaOperasional $armada) use ($pemeliharaanByArmada, $permohonanByArmada) {
            $pemeliharaan = $pemeliharaanByArmada->get($armada->id, collect());
            $permohonan = $permohonanByArmada->get($armada->id, collect());

            return [
                'armada' => $armada,
                'jumlah_pemeliharaan' => $pemeliharaan->count(),
                'jumlah_permohonan' => $permohonan->count(),
                'total_penugasan' => $pemeliharaan->count() + $permohonan->count(),
                'sopir' => $pemeliharaan->pluck('sopir')
                    ->concat($permohonan->pluck('sopir'))
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values()
                    ->all(),
                'url' => route('admin.alat-sarana-operasionals.show', $armada),
            ];
        })->sortByDesc('total_penugasan')->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PemeliharaanTamanArmada>  $pemeliharaanRows
     * @param  \Illuminate\Support\Collection<int, PemangkasanProgresArmada>  $permohonanRows
     * @return Collection<int, array{sopir: string, jumlah: int}>
     */
    private static function buildRekapSopir(Collection $pemeliharaanRows, Collection $permohonanRows): Collection
    {
        return $pemeliharaanRows->pluck('sopir')
            ->concat($permohonanRows->pluck('sopir'))
            ->filter()
            ->countBy()
            ->map(fn (int $count, string $sopir) => ['sopir' => $sopir, 'jumlah' => $count])
            ->sortByDesc('jumlah')
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PemeliharaanTamanArmada>  $pemeliharaanRows
     * @param  \Illuminate\Support\Collection<int, PemangkasanProgresArmada>  $permohonanRows
     * @return array{labels: list<string>, pemeliharaan: list<int>, permohonan: list<int>}
     */
    private static function buildDailyChart(
        Collection $pemeliharaanRows,
        Collection $permohonanRows,
        \Carbon\Carbon $from,
        \Carbon\Carbon $to,
        string $mode,
        int $bulan,
        int $tahun,
    ): array {
        $labels = [];
        $pemeliharaan = [];
        $permohonan = [];

        $countForDate = function (Collection $rows, string $date, string $relation): int {
            return $rows->filter(function ($row) use ($date, $relation) {
                $tanggal = $row->{$relation}?->tanggal;

                return $tanggal?->toDateString() === $date;
            })->count();
        };

        if ($mode === 'bulan') {
            $daysInMonth = $from->daysInMonth;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $labels[] = (string) $day;
                $date = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);
                $pemeliharaan[] = $countForDate($pemeliharaanRows, $date, 'pemeliharaanTaman');
                $permohonan[] = $countForDate($permohonanRows, $date, 'progres');
            }
        } else {
            $cursor = $from->copy()->startOfDay();
            $end = $to->copy()->startOfDay();
            while ($cursor->lte($end)) {
                $labels[] = $cursor->format('d/m');
                $date = $cursor->toDateString();
                $pemeliharaan[] = $countForDate($pemeliharaanRows, $date, 'pemeliharaanTaman');
                $permohonan[] = $countForDate($permohonanRows, $date, 'progres');
                $cursor->addDay();
            }
        }

        return compact('labels', 'pemeliharaan', 'permohonan');
    }
}
