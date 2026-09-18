<?php

namespace App\Support\Evaluasi;

use App\Models\Kecamatan;
use App\Models\Taman;
use App\Models\User;
use App\Support\OperatorWilayahScope;
use App\Support\TamanCompleteness;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class KelengkapanDataReportBuilder
{
    /** @var list<int> */
    public const FRESH_DAY_OPTIONS = [30, 60, 90, 180];

    /**
     * @return array<string, mixed>
     */
    public static function build(Request $request, ?User $user, bool $forPdf = false): array
    {
        $kategori = (string) $request->input('kategori', '');
        $kecamatanId = (string) $request->input('kecamatan_id', '');
        $statusFilter = (string) $request->input('status_data', '');
        $mutakhirFilter = (string) $request->input('mutakhir', '');
        $freshDays = self::resolveFreshDays($request);
        $cutoff = now()->subDays($freshDays);
        $completeness = app(TamanCompleteness::class);
        $scope = app(OperatorWilayahScope::class);

        $tamans = $scope->scopeTamans(
            Taman::query()->with(['images', 'kelurahan.kecamatan']),
            $user,
        )
            ->when($kategori !== '', fn ($query) => $query->where('kategori', $kategori))
            ->when($kecamatanId !== '', fn ($query) => $query->whereHas(
                'kelurahan',
                fn ($kelurahanQuery) => $kelurahanQuery->where('kecamatan_id', $kecamatanId),
            ))
            ->orderBy('nama_taman')
            ->get();

        $rows = $tamans->map(function (Taman $taman) use ($completeness, $cutoff) {
            $score = $completeness->score($taman);
            $breakdown = $completeness->breakdown($taman);
            $missing = collect($breakdown)->where('filled', false)->values();
            /** @var Carbon|null $verifiedAt */
            $verifiedAt = $taman->data_verified_at;
            $isLengkap = $score === 100;
            $isMutakhir = $verifiedAt !== null && $verifiedAt->greaterThanOrEqualTo($cutoff);
            $mutakhirStatus = self::mutakhirStatus($verifiedAt, $cutoff);

            return [
                'taman' => $taman,
                'taman_id' => $taman->id,
                'nama' => $taman->nama_taman,
                'kategori' => $taman->kategori ?? '—',
                'wilayah' => $taman->kelurahan?->kecamatan?->nama
                    ?? $taman->kelurahan?->nama
                    ?? '—',
                'luasan' => (int) $taman->luasan,
                'score' => $score,
                'is_lengkap' => $isLengkap,
                'is_mutakhir' => $isMutakhir,
                'mutakhir_status' => $mutakhirStatus,
                'verified_at' => $verifiedAt,
                'hari_sejak_verifikasi' => $verifiedAt?->diffInDays(now()),
                'missing_count' => $missing->count(),
                'missing_labels' => $missing->pluck('label')->all(),
                'breakdown' => $breakdown,
                'taman_url' => route('admin.tamans.show', $taman),
                'edit_url' => route('admin.tamans.edit', $taman),
            ];
        });

        $rows = self::applyMutakhirFilter($rows, $mutakhirFilter);

        if (in_array($statusFilter, [Taman::STATUS_DATA_LENGKAP, Taman::STATUS_DATA_BELUM_LENGKAP], true)) {
            $rows = $rows->filter(
                fn (array $row) => $statusFilter === Taman::STATUS_DATA_LENGKAP
                    ? $row['is_lengkap']
                    : ! $row['is_lengkap'],
            )->values();
        }

        $totalLokasi = $rows->count();
        $lokasiLengkap = $rows->where('is_lengkap', true)->count();
        $lokasiMutakhir = $rows->where('is_mutakhir', true)->count();
        $belumVerifikasi = $rows->where('mutakhir_status', 'belum_verifikasi')->count();
        $kedaluwarsa = $rows->where('mutakhir_status', 'kedaluwarsa')->count();
        $totalLuasan = (int) $rows->sum('luasan');
        $luasanLengkap = (int) $rows->where('is_lengkap', true)->sum('luasan');
        $luasanMutakhir = (int) $rows->where('is_mutakhir', true)->sum('luasan');
        $rataScore = $rows->count() > 0
            ? (int) round($rows->avg('score'))
            : 0;

        $perluPerhatian = $rows
            ->filter(fn (array $row) => ! $row['is_lengkap'] || ! $row['is_mutakhir'])
            ->sortBy([
                fn (array $row) => $row['score'],
                fn (array $row) => $row['verified_at']?->timestamp ?? 0,
            ])
            ->values();

        return [
            'freshDays' => $freshDays,
            'cutoffDate' => $cutoff,
            'labelSnapshot' => 'Snapshot per '.now()->timezone(config('app.timezone'))->translatedFormat('d F Y'),
            'labelKriteria' => "Mutakhir = diverifikasi dalam {$freshDays} hari terakhir",
            'kategori' => $kategori,
            'kecamatan_id' => $kecamatanId,
            'statusFilter' => $statusFilter,
            'mutakhirFilter' => $mutakhirFilter,
            'totalLokasi' => $totalLokasi,
            'lokasiLengkap' => $lokasiLengkap,
            'lokasiBelumLengkap' => $totalLokasi - $lokasiLengkap,
            'persenLengkap' => $totalLokasi > 0
                ? round(($lokasiLengkap / $totalLokasi) * 100, 1)
                : 0.0,
            'lokasiMutakhir' => $lokasiMutakhir,
            'lokasiKedaluwarsa' => $kedaluwarsa,
            'lokasiBelumVerifikasi' => $belumVerifikasi,
            'persenMutakhir' => $totalLokasi > 0
                ? round(($lokasiMutakhir / $totalLokasi) * 100, 1)
                : 0.0,
            'totalLuasan' => $totalLuasan,
            'luasanLengkap' => $luasanLengkap,
            'luasanMutakhir' => $luasanMutakhir,
            'persenLuasanLengkap' => $totalLuasan > 0
                ? round(($luasanLengkap / $totalLuasan) * 100, 1)
                : 0.0,
            'rataScore' => $rataScore,
            'rekapPerKategori' => self::buildRekapGroup($rows),
            'rekapPerKecamatan' => self::buildRekapGroup($rows, 'wilayah'),
            'fieldGaps' => self::buildFieldGaps($rows),
            'perluPerhatian' => $forPdf ? $perluPerhatian : $perluPerhatian->take(30),
            'chartLabels' => self::buildRekapGroup($rows)->pluck('label')->all(),
            'chartLengkap' => self::buildRekapGroup($rows)->pluck('lokasi_lengkap')->map(fn ($v) => (int) $v)->all(),
            'chartBelum' => self::buildRekapGroup($rows)->pluck('lokasi_belum')->map(fn ($v) => (int) $v)->all(),
            'daftarKategori' => collect(Taman::KATEGORI),
            'daftarKecamatan' => Kecamatan::query()->orderBy('nama')->get(['id', 'nama']),
            'daftarFreshDays' => self::FRESH_DAY_OPTIONS,
            'daftarStatusData' => [
                Taman::STATUS_DATA_LENGKAP => 'Lengkap',
                Taman::STATUS_DATA_BELUM_LENGKAP => 'Belum Lengkap',
            ],
            'daftarMutakhir' => [
                'mutakhir' => 'Mutakhir',
                'kedaluwarsa' => 'Kedaluwarsa',
                'belum_verifikasi' => 'Belum Diverifikasi',
            ],
        ];
    }

    private static function resolveFreshDays(Request $request): int
    {
        $default = (int) config('simtaman.ar.data_fresh_days', 90);
        $freshDays = (int) $request->input('fresh_days', $default);

        if (! in_array($freshDays, self::FRESH_DAY_OPTIONS, true)) {
            return $default;
        }

        return $freshDays;
    }

    private static function mutakhirStatus(?Carbon $verifiedAt, Carbon $cutoff): string
    {
        if ($verifiedAt === null) {
            return 'belum_verifikasi';
        }

        return $verifiedAt->greaterThanOrEqualTo($cutoff) ? 'mutakhir' : 'kedaluwarsa';
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private static function applyMutakhirFilter(Collection $rows, string $mutakhirFilter): Collection
    {
        if (! in_array($mutakhirFilter, ['mutakhir', 'kedaluwarsa', 'belum_verifikasi'], true)) {
            return $rows;
        }

        return $rows
            ->filter(fn (array $row) => $row['mutakhir_status'] === $mutakhirFilter)
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private static function buildRekapGroup(Collection $rows, string $groupKey = 'kategori'): Collection
    {
        return $rows
            ->groupBy(fn (array $row) => (string) ($row[$groupKey] ?: '—'))
            ->map(function (Collection $group, string $label) {
                $totalLokasi = $group->count();
                $lokasiLengkap = $group->where('is_lengkap', true)->count();
                $lokasiMutakhir = $group->where('is_mutakhir', true)->count();

                return [
                    'label' => $label,
                    'total_lokasi' => $totalLokasi,
                    'lokasi_lengkap' => $lokasiLengkap,
                    'lokasi_belum' => $totalLokasi - $lokasiLengkap,
                    'lokasi_mutakhir' => $lokasiMutakhir,
                    'persen_lengkap' => $totalLokasi > 0
                        ? round(($lokasiLengkap / $totalLokasi) * 100, 1)
                        : 0.0,
                    'persen_mutakhir' => $totalLokasi > 0
                        ? round(($lokasiMutakhir / $totalLokasi) * 100, 1)
                        : 0.0,
                    'rata_score' => $totalLokasi > 0
                        ? (int) round($group->avg('score'))
                        : 0,
                    'total_luasan' => (int) $group->sum('luasan'),
                ];
            })
            ->sortByDesc('total_lokasi')
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array{label: string, missing: int, persen: float}>
     */
    private static function buildFieldGaps(Collection $rows): Collection
    {
        if ($rows->isEmpty()) {
            return collect();
        }

        $total = $rows->count();
        $counts = [];

        foreach ($rows as $row) {
            foreach ($row['breakdown'] as $check) {
                if ($check['filled']) {
                    continue;
                }

                $counts[$check['label']] = ($counts[$check['label']] ?? 0) + 1;
            }
        }

        return collect($counts)
            ->map(fn (int $missing, string $label) => [
                'label' => $label,
                'missing' => $missing,
                'persen' => round(($missing / $total) * 100, 1),
            ])
            ->sortByDesc('missing')
            ->values()
            ->take(10);
    }
}
