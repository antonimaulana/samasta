<?php

namespace App\Support\Evaluasi;

use App\Models\AduanMasyarakat;
use App\Models\SurveyKepuasan;
use App\Models\Taman;
use App\Models\User;
use App\Support\OperatorWilayahScope;
use App\Support\ReportPeriod;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MasukanMasyarakatReportBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(Request $request, ?User $user, bool $forPdf = false): array
    {
        [$from, $to, $labelPeriode, $bulan, $tahun, $mode, $dari, $sampai] = ReportPeriod::resolve($request);
        $jenisAduan = (string) $request->input('jenis_aduan', '');
        $status = (string) $request->input('status', '');
        $kategoriSurvey = (string) $request->input('kategori_survey', '');
        $scope = app(OperatorWilayahScope::class);
        $unreviewedDays = (int) config('alerts.aduan.unreviewed_days', 3);

        $aduans = $scope->scopeAduan(
            AduanMasyarakat::query()
                ->with(['taman.kelurahan.kecamatan'])
                ->whereBetween('created_at', [$from, $to])
                ->when($jenisAduan !== '', fn (Builder $query) => $query->where('jenis_aduan', $jenisAduan))
                ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
                ->orderByDesc('created_at'),
            $user,
        )->get();

        $surveys = self::scopeSurvey(
            SurveyKepuasan::query()
                ->with('taman')
                ->whereBetween('created_at', [$from, $to])
                ->when($kategoriSurvey !== '', fn (Builder $query) => $query->whereIn(
                    'kategori',
                    SurveyKepuasan::kategoriStorageValues($kategoriSurvey),
                ))
                ->orderByDesc('created_at'),
            $user,
        )->get();

        $openAduans = $scope->scopeAduan(
            AduanMasyarakat::query()
                ->with(['taman.kelurahan.kecamatan'])
                ->whereIn('status', ['Baru', 'Ditinjau', 'Diproses'])
                ->when($jenisAduan !== '', fn (Builder $query) => $query->where('jenis_aduan', $jenisAduan))
                ->orderBy('created_at'),
            $user,
        )->get();

        $rekapPerJenis = self::buildRekapPerJenis($aduans, $unreviewedDays);
        $rekapSurvey = self::buildRekapSurvey($surveys);
        $topTaman = self::buildTopTaman($aduans);
        $chart = self::buildDailyChart($aduans, $surveys, $from, $to, $mode, $bulan, $tahun);

        $selesai = $aduans->where('status', 'Selesai');
        $durasiSelesai = $selesai
            ->map(fn (AduanMasyarakat $aduan) => self::durasiPenyelesaian($aduan))
            ->filter(fn (?int $days) => $days !== null);

        $overdueOpen = $openAduans->filter(
            fn (AduanMasyarakat $aduan) => $aduan->status === 'Baru'
                && $aduan->created_at->lte(now()->subDays($unreviewedDays)),
        );

        $ditanggapi = $aduans->filter(
            fn (AduanMasyarakat $aduan) => $aduan->status !== 'Baru'
                || $aduan->created_at->greaterThanOrEqualTo(now()->subDays($unreviewedDays)),
        );

        $puas = $surveys->where('rating', '>=', 4);

        return [
            'mode' => $mode,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dari' => $dari,
            'sampai' => $sampai,
            'labelPeriode' => $labelPeriode,
            'jenisAduan' => $jenisAduan,
            'status' => $status,
            'kategoriSurvey' => $kategoriSurvey,
            'unreviewedDays' => $unreviewedDays,
            'totalMasukan' => $aduans->count() + $surveys->count(),
            'totalAduan' => $aduans->count(),
            'totalSurvey' => $surveys->count(),
            'aduanSelesai' => $selesai->count(),
            'aduanDitolak' => $aduans->where('status', 'Ditolak')->count(),
            'aduanOpen' => $openAduans->count(),
            'aduanOverdue' => $overdueOpen->count(),
            'persenSelesai' => $aduans->count() > 0
                ? (int) round(($selesai->count() / $aduans->count()) * 100)
                : 0,
            'persenDitanggapi' => $aduans->count() > 0
                ? (int) round(($ditanggapi->count() / $aduans->count()) * 100)
                : null,
            'rataHariPenyelesaian' => $durasiSelesai->isNotEmpty()
                ? round($durasiSelesai->avg(), 1)
                : null,
            'surveyAverage' => $surveys->count() > 0
                ? round((float) $surveys->avg('rating'), 1)
                : null,
            'persenPuas' => $surveys->count() > 0
                ? (int) round(($puas->count() / $surveys->count()) * 100)
                : null,
            'surveyDistribution' => collect(range(1, 5))->mapWithKeys(
                fn (int $star) => [$star => $surveys->where('rating', $star)->count()],
            )->all(),
            'rekapPerJenis' => $rekapPerJenis,
            'rekapSurvey' => $rekapSurvey,
            'topTaman' => $topTaman,
            'openAduans' => $forPdf ? $openAduans : $openAduans->take(25),
            'recentSurveys' => $forPdf
                ? $surveys
                : $surveys->take(15),
            'chartLabels' => $chart['labels'],
            'chartAduan' => $chart['aduan'],
            'chartSurvey' => $chart['survey'],
            'daftarJenisAduan' => AduanMasyarakat::JENIS,
            'daftarStatus' => AduanMasyarakat::STATUS,
            'daftarKategoriSurvey' => SurveyKepuasan::KATEGORI,
            'daftarBulan' => ReportPeriod::daftarBulan(),
            'daftarTahun' => ReportPeriod::daftarTahun(5),
        ];
    }

    private static function scopeSurvey(Builder $query, ?User $user): Builder
    {
        $scope = app(OperatorWilayahScope::class);
        $tamanIds = $scope->scopeTamans(Taman::query(), $user)->pluck('id');

        if ($tamanIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $builder) use ($tamanIds) {
            $builder->whereNull('taman_id')
                ->orWhereIn('taman_id', $tamanIds);
        });
    }

    private static function durasiPenyelesaian(AduanMasyarakat $aduan): ?int
    {
        if ($aduan->status !== 'Selesai' || ! $aduan->created_at || ! $aduan->updated_at) {
            return null;
        }

        return (int) $aduan->created_at->diffInDays($aduan->updated_at);
    }

    /**
     * @param  Collection<int, AduanMasyarakat>  $aduans
     * @return Collection<int, array<string, mixed>>
     */
    private static function buildRekapPerJenis(Collection $aduans, int $unreviewedDays): Collection
    {
        return collect(AduanMasyarakat::JENIS)
            ->map(function (string $jenis) use ($aduans, $unreviewedDays) {
                $group = $aduans->where('jenis_aduan', $jenis);
                $selesai = $group->where('status', 'Selesai');
                $overdue = $group->filter(
                    fn (AduanMasyarakat $aduan) => $aduan->status === 'Baru'
                        && $aduan->created_at->lte(now()->subDays($unreviewedDays)),
                );

                return [
                    'jenis' => $jenis,
                    'total' => $group->count(),
                    'selesai' => $selesai->count(),
                    'open' => $group->whereIn('status', ['Baru', 'Ditinjau', 'Diproses'])->count(),
                    'overdue' => $overdue->count(),
                    'persen_selesai' => $group->count() > 0
                        ? (int) round(($selesai->count() / $group->count()) * 100)
                        : 0,
                ];
            })
            ->filter(fn (array $row) => $row['total'] > 0)
            ->sortByDesc('total')
            ->values();
    }

    /**
     * @param  Collection<int, SurveyKepuasan>  $surveys
     * @return Collection<int, array<string, mixed>>
     */
    private static function buildRekapSurvey(Collection $surveys): Collection
    {
        return collect(SurveyKepuasan::KATEGORI)
            ->map(function (string $kategori) use ($surveys) {
                $group = $surveys->filter(
                    fn (SurveyKepuasan $survey) => in_array(
                        $survey->kategori,
                        SurveyKepuasan::kategoriStorageValues($kategori),
                        true,
                    ),
                );

                return [
                    'kategori' => $kategori,
                    'total' => $group->count(),
                    'average' => $group->count() > 0
                        ? round((float) $group->avg('rating'), 1)
                        : null,
                    'puas' => $group->where('rating', '>=', 4)->count(),
                ];
            })
            ->filter(fn (array $row) => $row['total'] > 0)
            ->sortByDesc('total')
            ->values();
    }

    /**
     * @param  Collection<int, AduanMasyarakat>  $aduans
     * @return Collection<int, array<string, mixed>>
     */
    private static function buildTopTaman(Collection $aduans): Collection
    {
        return $aduans
            ->filter(fn (AduanMasyarakat $aduan) => $aduan->taman_id !== null)
            ->groupBy('taman_id')
            ->map(function (Collection $group) {
                /** @var AduanMasyarakat $first */
                $first = $group->first();

                return [
                    'taman' => $first->taman,
                    'nama' => $first->taman?->nama_taman ?? '—',
                    'wilayah' => $first->taman?->kelurahan?->kecamatan?->nama ?? '—',
                    'total' => $group->count(),
                    'open' => $group->whereIn('status', ['Baru', 'Ditinjau', 'Diproses'])->count(),
                    'url' => $first->taman_id
                        ? route('admin.aduan-masyarakats.index', ['search' => $first->taman?->nama_taman])
                        : null,
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->take(10);
    }

    /**
     * @param  Collection<int, AduanMasyarakat>  $aduans
     * @param  Collection<int, SurveyKepuasan>  $surveys
     * @return array{labels: list<string>, aduan: list<int>, survey: list<int>}
     */
    private static function buildDailyChart(
        Collection $aduans,
        Collection $surveys,
        Carbon $from,
        Carbon $to,
        string $mode,
        int $bulan,
        int $tahun,
    ): array {
        $labels = [];
        $aduanData = [];
        $surveyData = [];

        if ($mode === 'bulan') {
            $daysInMonth = $from->daysInMonth;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $labels[] = (string) $day;
                $date = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);
                $aduanData[] = $aduans->filter(
                    fn (AduanMasyarakat $aduan) => $aduan->created_at?->toDateString() === $date,
                )->count();
                $surveyData[] = $surveys->filter(
                    fn (SurveyKepuasan $survey) => $survey->created_at?->toDateString() === $date,
                )->count();
            }
        } else {
            $cursor = $from->copy()->startOfDay();
            $end = $to->copy()->startOfDay();
            while ($cursor->lte($end)) {
                $labels[] = $cursor->format('d/m');
                $date = $cursor->toDateString();
                $aduanData[] = $aduans->filter(
                    fn (AduanMasyarakat $aduan) => $aduan->created_at?->toDateString() === $date,
                )->count();
                $surveyData[] = $surveys->filter(
                    fn (SurveyKepuasan $survey) => $survey->created_at?->toDateString() === $date,
                )->count();
                $cursor->addDay();
            }
        }

        return [
            'labels' => $labels,
            'aduan' => $aduanData,
            'survey' => $surveyData,
        ];
    }
}
