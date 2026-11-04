<?php

namespace App\Support\Evaluasi;

use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\PemeliharaanTaman;
use App\Models\Petugas;
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
    /** Jumlah tim pelaksana standar (Wilayah 1–4, Armada, Nursery) untuk agregasi skor evaluasi. */
    public const JUMLAH_TIM_PENILAIAN = 6;

    /**
     * @return array<string, mixed>
     */
    public static function build(Request $request, ?User $user, bool $forPdf = false): array
    {
        [$from, $to, $labelPeriode, $bulan, $tahun, $mode, $dari, $sampai] = ReportPeriod::resolve($request);
        $timFilter = (string) $request->input('tim', '');
        $scope = app(OperatorWilayahScope::class);

        $allTeams = self::resolveTeams($user);
        $teams = $timFilter !== ''
            ? $allTeams->where('nama', $timFilter)->values()
            : $allTeams;

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

        $petugasAktifByTeam = self::buildPetugasAktifCountByTeam(
            $pemeliharaanEntries,
            $progresEntries,
        );
        $petugasRosterByTeam = self::buildPetugasRosterCountForAllActiveTeams();

        $rekapPerTim = self::buildRekapPerTim(
            $teams,
            $pemeliharaanEntries,
            $permohonanEntries,
            $progresEntries,
            $surveyByTeam,
            $from,
            $to,
            $petugasAktifByTeam,
            $petugasRosterByTeam,
        );

        $chart = self::buildTeamChart($rekapPerTim);
        $skorPartisipasi = self::computeSkorPartisipasi($petugasAktifByTeam, $petugasRosterByTeam);
        $totals = self::buildTotals($rekapPerTim, $skorPartisipasi);
        $rekapPetugasAktif = self::buildRekapPetugasAktif(
            $pemeliharaanEntries,
            $progresEntries,
            $timFilter,
        );

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
            'skorPartisipasi' => $totals['skor_partisipasi'],
            'rataTepatWaktu' => $totals['rata_tepat_waktu'],
            'rekapPerTim' => $forPdf ? $rekapPerTim : $rekapPerTim,
            'rekapPetugasAktif' => $forPdf ? $rekapPetugasAktif : $rekapPetugasAktif,
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
        array $petugasAktifByTeam,
        array $petugasRosterByTeam,
    ): Collection {
        $deadlineCutoff = $to->toDateString();

        return $teams->map(function (TimPelaksana $team) use (
            $pemeliharaanEntries,
            $permohonanEntries,
            $progresEntries,
            $surveyByTeam,
            $deadlineCutoff,
            $petugasAktifByTeam,
            $petugasRosterByTeam,
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

            $petugasAktif = (int) ($petugasAktifByTeam[$tim] ?? 0);
            $petugasRoster = (int) ($petugasRosterByTeam[$tim] ?? 0);
            $persenPartisipasi = $petugasRoster > 0
                ? (int) round(($petugasAktif / $petugasRoster) * 100)
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
                'petugas_aktif' => $petugasAktif,
                'petugas_roster' => $petugasRoster,
                'persen_partisipasi' => $persenPartisipasi,
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
     * @param  Collection<int, PemeliharaanTaman>  $pemeliharaanEntries
     * @param  Collection<int, PemangkasanProgres>  $progresEntries
     * @return array<string, int> nama tim => jumlah petugas unik tercatat
     */
    private static function buildPetugasAktifCountByTeam(
        Collection $pemeliharaanEntries,
        Collection $progresEntries,
    ): array {
        $pemeliharaanEntries->loadMissing(['petugas']);
        $progresEntries->loadMissing(['petugas.timPelaksana']);

        /** @var array<string, array<int, true>> $uniqueIds */
        $uniqueIds = [];

        foreach ($pemeliharaanEntries as $entry) {
            $tim = $entry->tim;
            foreach ($entry->petugas as $petugas) {
                $uniqueIds[$tim][$petugas->id] = true;
            }
        }

        foreach ($progresEntries as $entry) {
            foreach ($entry->petugas as $petugas) {
                $timNama = $petugas->timPelaksana?->nama;
                if ($timNama === null || $timNama === '') {
                    continue;
                }
                $uniqueIds[$timNama][$petugas->id] = true;
            }
        }

        $counts = [];
        foreach ($uniqueIds as $tim => $ids) {
            $counts[$tim] = count($ids);
        }

        return $counts;
    }

    /**
     * @return array<string, int> nama tim => jumlah petugas aktif di roster
     */
    private static function buildPetugasRosterCountForAllActiveTeams(): array
    {
        return TimPelaksana::query()
            ->where('aktif', true)
            ->withCount('petugasAktif as roster_count')
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn (TimPelaksana $team) => [$team->nama => (int) $team->roster_count])
            ->all();
    }

    /**
     * Skor evaluasi: rata-rata persentase partisipasi petugas per tim (aktif ÷ roster), dibagi {@see JUMLAH_TIM_PENILAIAN}.
     *
     * @param  array<string, int>  $petugasAktifByTeam
     * @param  array<string, int>  $petugasRosterByTeam
     */
    public static function computeSkorPartisipasi(array $petugasAktifByTeam, array $petugasRosterByTeam): ?int
    {
        $teamNames = PemeliharaanTaman::timNames();
        $namesForScore = array_slice($teamNames, 0, self::JUMLAH_TIM_PENILAIAN);
        if ($namesForScore === []) {
            return null;
        }

        $sumPersen = 0.0;
        $hasAnyRoster = false;

        foreach ($namesForScore as $nama) {
            $roster = (int) ($petugasRosterByTeam[$nama] ?? 0);
            if ($roster > 0) {
                $hasAnyRoster = true;
            }
            $aktif = (int) ($petugasAktifByTeam[$nama] ?? 0);
            $sumPersen += $roster > 0 ? ($aktif / $roster) * 100 : 0.0;
        }

        if (! $hasAnyRoster) {
            return null;
        }

        $divisor = self::JUMLAH_TIM_PENILAIAN;

        return (int) round($sumPersen / $divisor);
    }

    /**
     * @param  Collection<int, PemeliharaanTaman>  $pemeliharaanEntries
     * @param  Collection<int, PemangkasanProgres>  $progresEntries
     * @return Collection<int, array<string, mixed>>
     */
    private static function buildRekapPetugasAktif(
        Collection $pemeliharaanEntries,
        Collection $progresEntries,
        string $timFilter,
        int $limit = 15,
    ): Collection {
        $pemeliharaanEntries->loadMissing(['petugas.timPelaksana']);
        $progresEntries->loadMissing(['petugas.timPelaksana']);

        /** @var array<int, array<string, mixed>> $stats */
        $stats = [];

        $register = function (Petugas $petugas, string $type) use (&$stats, $timFilter): void {
            $timNama = $petugas->timPelaksana?->nama;
            if ($timFilter !== '' && $timNama !== $timFilter) {
                return;
            }

            $id = $petugas->id;
            if (! isset($stats[$id])) {
                $stats[$id] = [
                    'petugas_id' => $id,
                    'nama' => $petugas->nama,
                    'jabatan' => $petugas->jabatan,
                    'tim' => $timNama ?? '—',
                    'jumlah_pemeliharaan' => 0,
                    'jumlah_permohonan' => 0,
                    'total_kegiatan' => 0,
                ];
            }

            $stats[$id]['jumlah_'.$type]++;
            $stats[$id]['total_kegiatan']++;
        };

        foreach ($pemeliharaanEntries as $entry) {
            foreach ($entry->petugas as $petugas) {
                $register($petugas, 'pemeliharaan');
            }
        }

        foreach ($progresEntries as $entry) {
            foreach ($entry->petugas as $petugas) {
                $register($petugas, 'permohonan');
            }
        }

        return collect($stats)
            ->sortByDesc('total_kegiatan')
            ->take($limit)
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rekapPerTim
     * @return array{
     *     total_kegiatan: int,
     *     pemeliharaan: int,
     *     permohonan: int,
     *     personil: int,
     *     skor_partisipasi: int|null,
     *     rata_tepat_waktu: int|null
     * }
     */
    private static function buildTotals(Collection $rekapPerTim, ?int $skorPartisipasi): array
    {
        $tepatWaktu = (int) $rekapPerTim->sum('permohonan_tepat_waktu');
        $terlambat = (int) $rekapPerTim->sum('permohonan_terlambat');
        $denominator = $tepatWaktu + $terlambat;

        return [
            'total_kegiatan' => (int) $rekapPerTim->sum('total_kegiatan'),
            'pemeliharaan' => (int) $rekapPerTim->sum('jumlah_pemeliharaan'),
            'permohonan' => (int) $rekapPerTim->sum('jumlah_permohonan'),
            'personil' => (int) $rekapPerTim->sum('total_personil'),
            'skor_partisipasi' => $skorPartisipasi,
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
