<?php

namespace App\Support\Evaluasi;

use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\PemeliharaanTaman;
use App\Models\SurveyKepuasan;
use App\Models\TimPelaksana;
use App\Models\User;
use App\Support\JadwalLayananQuery;
use App\Support\OperatorWilayahScope;
use App\Support\ReportPeriod;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class KinerjaTimReportBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(Request $request, ?User $user, bool $forPdf = false): array
    {
        [$from, $to, $labelPeriode, $bulan, $tahun, $mode, $dari, $sampai] = ReportPeriod::resolve($request);
        $timFilter = (string) $request->input('tim', '');
        $scope = app(OperatorWilayahScope::class);

        $teams = self::resolveTeams($user);
        if ($timFilter !== '') {
            $teams = $teams->where('nama', $timFilter)->values();
        }

        $pemeliharaanEntries = $scope->scopePemeliharaan(
            PemeliharaanTaman::query()->whereBetween('tanggal', [$from, $to]),
            $user,
        )->get();

        $permohonanEntries = $scope->scopePemangkasan(
            self::permohonanInPeriodQuery($from, $to)->with(['progres', 'taman']),
            $user,
        )->get();

        $progresEntries = PemangkasanProgres::query()
            ->with('pemangkasan')
            ->whereBetween('tanggal', [$from, $to])
            ->whereHas('pemangkasan', fn (Builder $query) => $scope->scopePemangkasan($query, $user))
            ->get();

        $surveyByTeam = self::buildSurveyByTeam($from, $to);

        $rekapPerTim = self::buildRekapPerTim(
            $teams,
            $pemeliharaanEntries,
            $permohonanEntries,
            $progresEntries,
            $surveyByTeam,
            $from,
            $to,
        );

        $chart = self::buildTeamChart($rekapPerTim);
        $totals = self::buildTotals($rekapPerTim);

        return [
            'mode' => $mode,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dari' => $dari,
            'sampai' => $sampai,
            'labelPeriode' => $labelPeriode,
            'tim' => $timFilter,
            'totalKegiatan' => $totals['total_kegiatan'],
            'totalPemeliharaan' => $totals['pemeliharaan'],
            'totalPermohonan' => $totals['permohonan'],
            'totalPersonil' => $totals['personil'],
            'rataTepatWaktu' => $totals['rata_tepat_waktu'],
            'rekapPerTim' => $forPdf ? $rekapPerTim : $rekapPerTim,
            'chartLabels' => $chart['labels'],
            'chartPemeliharaan' => $chart['pemeliharaan'],
            'chartPermohonan' => $chart['permohonan'],
            'daftarTim' => self::daftarTimNames($user),
            'daftarBulan' => ReportPeriod::daftarBulan(),
            'daftarTahun' => ReportPeriod::daftarTahun(5),
        ];
    }

    /**
     * @return Collection<int, TimPelaksana>
     */
    private static function resolveTeams(?User $user): Collection
    {
        $scope = app(OperatorWilayahScope::class);
        $query = TimPelaksana::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('nama');

        if ($user?->requiresWilayahScope() && $scope->restrictsWilayah($user)) {
            $names = $scope->teamNames($user);

            if ($names === []) {
                return collect();
            }

            $query->whereIn('nama', $names);
        }

        $teams = $query->get();

        if ($teams->isNotEmpty()) {
            return $teams;
        }

        return collect(PemeliharaanTaman::timNames())->map(fn (string $nama) => new TimPelaksana([
            'nama' => $nama,
            'nama_pengawas' => null,
            'memiliki_wilayah_kerja' => false,
            'aktif' => true,
        ]));
    }

    /**
     * @return list<string>
     */
    private static function daftarTimNames(?User $user): array
    {
        return self::resolveTeams($user)->pluck('nama')->all();
    }

    private static function permohonanInPeriodQuery(Carbon $from, Carbon $to): Builder
    {
        return Pemangkasan::query()->where(function (Builder $query) use ($from, $to) {
            JadwalLayananQuery::overlapsPeriod($query, $from, $to);
            $query->orWhereBetween('tanggal_penyelesaian', [$from->toDateString(), $to->toDateString()]);
            $query->orWhereHas(
                'progres',
                fn (Builder $progres) => $progres->whereBetween('tanggal', [$from, $to]),
            );
        });
    }

    /**
     * @return array<string, array{jumlah: int, total_rating: int}>
     */
    private static function buildSurveyByTeam(Carbon $from, Carbon $to): array
    {
        $kelurahanToTeam = [];

        TimPelaksana::query()
            ->where('memiliki_wilayah_kerja', true)
            ->with('kelurahans:id')
            ->get()
            ->each(function (TimPelaksana $team) use (&$kelurahanToTeam) {
                foreach ($team->kelurahans as $kelurahan) {
                    $kelurahanToTeam[$kelurahan->id] = $team->nama;
                }
            });

        if ($kelurahanToTeam === []) {
            return [];
        }

        $stats = [];

        SurveyKepuasan::query()
            ->with('taman:id,kelurahan_id')
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('taman_id')
            ->get()
            ->each(function (SurveyKepuasan $survey) use (&$stats, $kelurahanToTeam) {
                $kelurahanId = $survey->taman?->kelurahan_id;

                if ($kelurahanId === null || ! isset($kelurahanToTeam[$kelurahanId])) {
                    return;
                }

                $team = $kelurahanToTeam[$kelurahanId];
                $stats[$team]['jumlah'] = ($stats[$team]['jumlah'] ?? 0) + 1;
                $stats[$team]['total_rating'] = ($stats[$team]['total_rating'] ?? 0) + (int) $survey->rating;
            });

        return $stats;
    }

    /**
     * @param  Collection<int, TimPelaksana>  $teams
     * @param  Collection<int, PemeliharaanTaman>  $pemeliharaanEntries
     * @param  Collection<int, Pemangkasan>  $permohonanEntries
     * @param  Collection<int, PemangkasanProgres>  $progresEntries
     * @param  array<string, array{jumlah: int, total_rating: int}>  $surveyByTeam
     * @return Collection<int, array<string, mixed>>
     */
    private static function buildRekapPerTim(
        Collection $teams,
        Collection $pemeliharaanEntries,
        Collection $permohonanEntries,
        Collection $progresEntries,
        array $surveyByTeam,
        Carbon $from,
        Carbon $to,
    ): Collection {
        $deadlineCutoff = $to->toDateString();

        return $teams->map(function (TimPelaksana $team) use (
            $pemeliharaanEntries,
            $permohonanEntries,
            $progresEntries,
            $surveyByTeam,
            $deadlineCutoff,
        ) {
            $tim = $team->nama;

            $pemeliharaan = $pemeliharaanEntries->where('tim', $tim);
            $permohonan = $permohonanEntries->filter(
                fn (Pemangkasan $entry) => in_array($tim, self::teamsFromPermohonan($entry), true),
            );

            $personilPemeliharaan = (int) $pemeliharaan->sum('jumlah_personil');
            $personilPermohonan = (int) $progresEntries
                ->filter(function (PemangkasanProgres $progres) use ($tim) {
                    return in_array($tim, self::teamsFromPermohonan($progres->pemangkasan), true);
                })
                ->sum('jumlah_personil');

            $totalPersonil = $personilPemeliharaan + $personilPermohonan;
            $totalKegiatan = $pemeliharaan->count() + $permohonan->count();

            $completed = $permohonan->filter(
                fn (Pemangkasan $entry) => $entry->status === 'Selesai' && filled($entry->tanggal_penyelesaian),
            );

            $tepatWaktu = $completed->filter(function (Pemangkasan $entry) {
                $deadline = self::deadlineDate($entry);

                return $deadline !== null
                    && $entry->tanggal_penyelesaian->toDateString() <= $deadline;
            })->count();

            $selesaiTerlambat = $completed->filter(function (Pemangkasan $entry) {
                $deadline = self::deadlineDate($entry);

                return $deadline !== null
                    && $entry->tanggal_penyelesaian->toDateString() > $deadline;
            })->count();

            $belumSelesaiTerlambat = $permohonan->filter(function (Pemangkasan $entry) use ($deadlineCutoff) {
                if ($entry->status === 'Selesai') {
                    return false;
                }

                $deadline = self::deadlineDate($entry);

                return $deadline !== null && $deadline < $deadlineCutoff;
            })->count();

            $totalTerlambat = $selesaiTerlambat + $belumSelesaiTerlambat;
            $denominator = $tepatWaktu + $totalTerlambat;

            $survey = $surveyByTeam[$tim] ?? null;
            $surveyJumlah = $survey['jumlah'] ?? 0;
            $surveyRata = $surveyJumlah > 0
                ? round($survey['total_rating'] / $surveyJumlah, 1)
                : null;

            return [
                'tim' => $tim,
                'nama_pengawas' => $team->nama_pengawas,
                'memiliki_wilayah_kerja' => (bool) $team->memiliki_wilayah_kerja,
                'jumlah_pemeliharaan' => $pemeliharaan->count(),
                'jumlah_permohonan' => $permohonan->count(),
                'total_kegiatan' => $totalKegiatan,
                'total_personil' => $totalPersonil,
                'rata_personil' => $totalKegiatan > 0
                    ? round($totalPersonil / $totalKegiatan, 1)
                    : 0.0,
                'taman_terlayani' => $pemeliharaan->pluck('taman_id')->filter()->unique()->count(),
                'permohonan_selesai' => $completed->count(),
                'permohonan_tepat_waktu' => $tepatWaktu,
                'permohonan_terlambat' => $totalTerlambat,
                'persen_tepat_waktu' => $denominator > 0
                    ? (int) round(($tepatWaktu / $denominator) * 100)
                    : null,
                'survey_rata' => $surveyRata,
                'survey_jumlah' => $surveyJumlah,
                'url_pemeliharaan' => route('admin.pemeliharaan-tamans.index', ['tim' => $tim]),
                'url_permohonan' => route('admin.pemangkasans.index', ['pelaksana' => $tim]),
            ];
        })
            ->sortByDesc('total_kegiatan')
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rekapPerTim
     * @return array{labels: list<string>, pemeliharaan: list<int>, permohonan: list<int>}
     */
    private static function buildTeamChart(Collection $rekapPerTim): array
    {
        return [
            'labels' => $rekapPerTim->pluck('tim')->all(),
            'pemeliharaan' => $rekapPerTim->pluck('jumlah_pemeliharaan')->all(),
            'permohonan' => $rekapPerTim->pluck('jumlah_permohonan')->all(),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rekapPerTim
     * @return array{total_kegiatan: int, pemeliharaan: int, permohonan: int, personil: int, rata_tepat_waktu: int|null}
     */
    private static function buildTotals(Collection $rekapPerTim): array
    {
        $tepatWaktu = (int) $rekapPerTim->sum('permohonan_tepat_waktu');
        $terlambat = (int) $rekapPerTim->sum('permohonan_terlambat');
        $denominator = $tepatWaktu + $terlambat;

        return [
            'total_kegiatan' => (int) $rekapPerTim->sum('total_kegiatan'),
            'pemeliharaan' => (int) $rekapPerTim->sum('jumlah_pemeliharaan'),
            'permohonan' => (int) $rekapPerTim->sum('jumlah_permohonan'),
            'personil' => (int) $rekapPerTim->sum('total_personil'),
            'rata_tepat_waktu' => $denominator > 0
                ? (int) round(($tepatWaktu / $denominator) * 100)
                : null,
        ];
    }

    /**
     * @return list<string>
     */
    private static function teamsFromPermohonan(Pemangkasan $permohonan): array
    {
        $teams = $permohonan->pelaksana;

        return is_array($teams) ? array_values(array_filter($teams)) : [];
    }

    private static function deadlineDate(Pemangkasan $permohonan): ?string
    {
        $deadline = $permohonan->tanggal_akhir_jadwal ?? $permohonan->tanggal_eksekusi;

        return $deadline?->toDateString();
    }
}
