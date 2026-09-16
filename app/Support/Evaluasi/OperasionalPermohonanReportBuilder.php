<?php

namespace App\Support\Evaluasi;

use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\PemeliharaanTaman;
use App\Models\User;
use App\Support\JadwalLayananQuery;
use App\Support\OperatorWilayahScope;
use App\Support\ReportPeriod;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OperasionalPermohonanReportBuilder
{
    private const DOKUMENTASI_LENGKAP_THRESHOLD = 50;

    /**
     * @return array<string, mixed>
     */
    public static function build(Request $request, ?User $user, bool $forPdf = false): array
    {
        [$from, $to, $labelPeriode, $bulan, $tahun, $mode, $dari, $sampai] = ReportPeriod::resolve($request);
        $jenisLayanan = (string) $request->input('jenis_layanan', '');
        $status = (string) $request->input('status', '');
        $pelaksana = (string) $request->input('pelaksana', '');
        $scope = app(OperatorWilayahScope::class);

        $entries = $scope->scopePemangkasan(
            self::permohonanInPeriodQuery($from, $to)
                ->with(['progres', 'taman'])
                ->when($jenisLayanan !== '', fn (Builder $query) => $query->where('jenis_layanan', $jenisLayanan))
                ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
                ->when($pelaksana !== '', fn (Builder $query) => $query->whereJsonContains('pelaksana', $pelaksana))
                ->orderByDesc('tanggal_eksekusi'),
            $user,
        )->get();

        $rekapPerJenis = self::buildRekapPerJenis($entries, $to);
        $chart = self::buildDailyChart($entries, $from, $to, $mode, $bulan, $tahun, $jenisLayanan);
        $detailRows = self::buildDetailRows($entries, $to);

        $completed = $entries->where('status', 'Selesai')->whereNotNull('tanggal_penyelesaian');
        $slaDays = $completed
            ->map(fn (Pemangkasan $entry) => self::durasiPenyelesaian($entry))
            ->filter(fn (?int $days) => $days !== null);

        $tepatWaktu = self::countTepatWaktu($completed);
        $terlambat = self::countTerlambat($entries, $to);
        $slaDenominator = $tepatWaktu + $terlambat;

        $dokumentasiLengkap = $entries->filter(
            fn (Pemangkasan $entry) => self::dokumentasiPercent($entry) >= self::DOKUMENTASI_LENGKAP_THRESHOLD,
        )->count();

        return [
            'mode' => $mode,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dari' => $dari,
            'sampai' => $sampai,
            'labelPeriode' => $labelPeriode,
            'jenisLayanan' => $jenisLayanan,
            'status' => $status,
            'pelaksana' => $pelaksana,
            'totalPermohonan' => $entries->count(),
            'totalSelesai' => $entries->where('status', 'Selesai')->count(),
            'totalDiproses' => $entries->where('status', 'Diproses')->count(),
            'totalRencana' => $entries->where('status', 'Rencana')->count(),
            'persenSelesai' => $entries->count() > 0
                ? (int) round(($entries->where('status', 'Selesai')->count() / $entries->count()) * 100)
                : 0,
            'rataHariPenyelesaian' => $slaDays->isNotEmpty()
                ? round($slaDays->avg(), 1)
                : null,
            'persenTepatWaktu' => $slaDenominator > 0
                ? (int) round(($tepatWaktu / $slaDenominator) * 100)
                : null,
            'persenDokumentasiLengkap' => $entries->count() > 0
                ? (int) round(($dokumentasiLengkap / $entries->count()) * 100)
                : 0,
            'rekapPerJenis' => $rekapPerJenis,
            'detailRows' => $forPdf ? $detailRows : $detailRows->take(50),
            'chartLabels' => $chart['labels'],
            'chartDatasets' => $chart['datasets'],
            'daftarJenisLayanan' => Pemangkasan::JENIS_LAYANAN,
            'daftarStatus' => Pemangkasan::STATUS,
            'daftarPelaksana' => PemeliharaanTaman::timNames(),
            'daftarBulan' => ReportPeriod::daftarBulan(),
            'daftarTahun' => ReportPeriod::daftarTahun(5),
        ];
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
     * @param  Collection<int, Pemangkasan>  $entries
     * @return Collection<int, array<string, mixed>>
     */
    private static function buildRekapPerJenis(Collection $entries, Carbon $to): Collection
    {
        return collect(Pemangkasan::JENIS_LAYANAN)
            ->map(function (string $jenis) use ($entries, $to) {
                $group = $entries->where('jenis_layanan', $jenis);
                $completed = $group->where('status', 'Selesai')->whereNotNull('tanggal_penyelesaian');
                $slaDays = $completed
                    ->map(fn (Pemangkasan $entry) => self::durasiPenyelesaian($entry))
                    ->filter(fn (?int $days) => $days !== null);
                $tepatWaktu = self::countTepatWaktu($completed);
                $terlambat = self::countTerlambat($group, $to);
                $slaDenominator = $tepatWaktu + $terlambat;
                $dokumentasiLengkap = $group->filter(
                    fn (Pemangkasan $entry) => self::dokumentasiPercent($entry) >= self::DOKUMENTASI_LENGKAP_THRESHOLD,
                )->count();

                return [
                    'jenis' => $jenis,
                    'total' => $group->count(),
                    'rencana' => $group->where('status', 'Rencana')->count(),
                    'diproses' => $group->where('status', 'Diproses')->count(),
                    'selesai' => $group->where('status', 'Selesai')->count(),
                    'rata_hari' => $slaDays->isNotEmpty() ? round($slaDays->avg(), 1) : null,
                    'persen_tepat_waktu' => $slaDenominator > 0
                        ? (int) round(($tepatWaktu / $slaDenominator) * 100)
                        : null,
                    'persen_dokumentasi' => $group->count() > 0
                        ? (int) round(($dokumentasiLengkap / $group->count()) * 100)
                        : 0,
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    /**
     * @param  Collection<int, Pemangkasan>  $entries
     * @return Collection<int, array<string, mixed>>
     */
    private static function buildDetailRows(Collection $entries, Carbon $to): Collection
    {
        return $entries->map(function (Pemangkasan $entry) use ($to) {
            $deadline = self::deadlineDate($entry);
            $durasi = self::durasiPenyelesaian($entry);
            $dokumentasi = self::dokumentasiPercent($entry);

            $slaLabel = '—';
            if ($entry->status === 'Selesai' && $entry->tanggal_penyelesaian && $deadline) {
                $slaLabel = $entry->tanggal_penyelesaian->toDateString() <= $deadline
                    ? 'Tepat waktu'
                    : 'Terlambat';
            } elseif ($entry->status !== 'Selesai' && $deadline && $deadline < $to->toDateString()) {
                $slaLabel = 'Terlambat';
            }

            return [
                'entry' => $entry,
                'lokasi' => $entry->lokasiLabel(),
                'jenis' => $entry->jenis_layanan,
                'status' => $entry->status,
                'pelaksana' => $entry->pelaksanaLabel() ?: '—',
                'tanggal_permohonan' => $entry->tanggal_permohonan,
                'tanggal_penyelesaian' => $entry->tanggal_penyelesaian,
                'durasi_hari' => $durasi,
                'sla_label' => $slaLabel,
                'dokumentasi_percent' => $dokumentasi,
                'jumlah_progres' => $entry->progres->count(),
                'url' => route('admin.pemangkasans.show', $entry),
            ];
        })->values();
    }

    /**
     * @param  Collection<int, Pemangkasan>  $entries
     * @return array{labels: list<string>, datasets: list<array<string, mixed>>}
     */
    private static function buildDailyChart(
        Collection $entries,
        Carbon $from,
        Carbon $to,
        string $mode,
        int $bulan,
        int $tahun,
        string $jenisFilter,
    ): array {
        $labels = [];
        $jenisList = collect(Pemangkasan::JENIS_LAYANAN)
            ->when($jenisFilter !== '', fn (Collection $c) => $c->filter(fn (string $j) => $j === $jenisFilter));

        if ($mode === 'bulan') {
            for ($day = 1; $day <= $from->daysInMonth; $day++) {
                $labels[] = (string) $day;
            }
        } else {
            $cursor = $from->copy()->startOfDay();
            $end = $to->copy()->startOfDay();
            while ($cursor->lte($end)) {
                $labels[] = $cursor->format('d/m');
                $cursor->addDay();
            }
        }

        $datasets = $jenisList->map(function (string $jenis) use ($entries, $from, $to, $mode, $bulan, $tahun) {
            $data = [];

            if ($mode === 'bulan') {
                for ($day = 1; $day <= $from->daysInMonth; $day++) {
                    $date = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);
                    $data[] = self::countForDate($entries, $date, $jenis);
                }
            } else {
                $cursor = $from->copy()->startOfDay();
                $end = $to->copy()->startOfDay();
                while ($cursor->lte($end)) {
                    $data[] = self::countForDate($entries, $cursor->toDateString(), $jenis);
                    $cursor->addDay();
                }
            }

            [$backgroundColor, $borderColor] = Pemangkasan::chartColors($jenis);

            return [
                'label' => $jenis,
                'data' => $data,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $borderColor,
            ];
        })->values()->all();

        return compact('labels', 'datasets');
    }

    /**
     * @param  Collection<int, Pemangkasan>  $entries
     */
    private static function countForDate(Collection $entries, string $date, string $jenis): int
    {
        return $entries->filter(function (Pemangkasan $entry) use ($date, $jenis) {
            if ($entry->jenis_layanan !== $jenis) {
                return false;
            }

            $start = $entry->tanggal_eksekusi?->toDateString();
            $end = self::deadlineDate($entry) ?? $start;

            return $start !== null
                && $start <= $date
                && $end >= $date;
        })->count();
    }

    /**
     * @param  Collection<int, Pemangkasan>  $completed
     */
    private static function countTepatWaktu(Collection $completed): int
    {
        return $completed->filter(function (Pemangkasan $entry) {
            $deadline = self::deadlineDate($entry);

            return $deadline !== null
                && $entry->tanggal_penyelesaian->toDateString() <= $deadline;
        })->count();
    }

    /**
     * @param  Collection<int, Pemangkasan>  $entries
     */
    private static function countTerlambat(Collection $entries, Carbon $to): int
    {
        $deadlineCutoff = $to->toDateString();

        $selesaiTerlambat = $entries
            ->where('status', 'Selesai')
            ->filter(function (Pemangkasan $entry) {
                $deadline = self::deadlineDate($entry);

                return $deadline !== null
                    && $entry->tanggal_penyelesaian?->toDateString() > $deadline;
            })
            ->count();

        $belumSelesaiTerlambat = $entries
            ->whereIn('status', ['Rencana', 'Diproses'])
            ->filter(function (Pemangkasan $entry) use ($deadlineCutoff) {
                $deadline = self::deadlineDate($entry);

                return $deadline !== null && $deadline < $deadlineCutoff;
            })
            ->count();

        return $selesaiTerlambat + $belumSelesaiTerlambat;
    }

    private static function durasiPenyelesaian(Pemangkasan $entry): ?int
    {
        if ($entry->tanggal_penyelesaian === null || $entry->tanggal_permohonan === null) {
            return null;
        }

        return (int) $entry->tanggal_permohonan->diffInDays($entry->tanggal_penyelesaian);
    }

    private static function dokumentasiPercent(Pemangkasan $entry): int
    {
        $filled = 0;
        $total = 0;

        foreach (['foto_sebelum', 'foto_sesudah'] as $field) {
            $total++;
            if (filled($entry->{$field})) {
                $filled++;
            }
        }

        foreach ($entry->progres as $progres) {
            foreach (PemangkasanProgres::fotoFieldKeys() as $field) {
                $total++;
                if (filled($progres->{$field})) {
                    $filled++;
                }
            }
        }

        if ($total === 0) {
            return 0;
        }

        return (int) round(($filled / $total) * 100);
    }

    private static function deadlineDate(Pemangkasan $entry): ?string
    {
        $deadline = $entry->tanggal_akhir_jadwal ?? $entry->tanggal_eksekusi;

        return $deadline?->toDateString();
    }
}
