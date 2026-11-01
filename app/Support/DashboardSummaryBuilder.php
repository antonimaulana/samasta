<?php

namespace App\Support;

use App\Models\AduanMasyarakat;
use App\Models\Armada;
use App\Models\Bibit;
use App\Models\BibitMasuk;
use App\Models\PemeliharaanTaman;
use App\Models\Pemangkasan;
use App\Models\Petugas;
use App\Models\SurveyKepuasan;
use App\Models\Taman;
use App\Models\TimPelaksana;
use App\Models\User;
use Illuminate\Support\Str;

class DashboardSummaryBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(?User $user = null): array
    {
        $scope = app(OperatorWilayahScope::class);

        $tamanQuery = $scope->scopeTamans(Taman::query(), $user);
        $totalTaman = (clone $tamanQuery)->count();
        $totalLuasanTaman = (int) (clone $tamanQuery)->sum('luasan');

        $totalVarietasBibit = Bibit::count();
        $totalStokBibit = (int) Bibit::sum('stok_tersedia');
        $bibitSiapTanam = (int) BibitMasuk::where('status_siap_tanam', true)->sum('sisa_stok');

        $totalArmada = ArmadaSchema::isReady()
            ? Armada::query()->armadaInventory()->count()
            : 0;
        $armadaKondisiBaikPersen = 0;
        if (ArmadaSchema::isReady() && $totalArmada > 0) {
            $armadaBaik = Armada::query()->armadaInventory()->where('kondisi', 'Baik')->count();
            $armadaKondisiBaikPersen = (int) round(($armadaBaik / $totalArmada) * 100);
        }

        $totalPetugas = Petugas::query()->where('aktif', true)->count();
        $totalTimKerja = TimPelaksana::query()->where('aktif', true)->count();

        $layananPerJenis = collect(Pemangkasan::JENIS_LAYANAN)->map(function (string $jenis) use ($scope, $user) {
            $base = fn () => $scope->scopePemangkasan(Pemangkasan::query()->where('jenis_layanan', $jenis), $user);
            $rencana = $base()->where('status', 'Rencana')->count();
            $diproses = $base()->where('status', 'Diproses')->count();
            $selesai = $base()->where('status', 'Selesai')->count();
            $total = $rencana + $diproses + $selesai;

            return [
                'jenis' => $jenis,
                'icon' => Pemangkasan::layananIcon($jenis),
                'rencana' => $rencana,
                'diproses' => $diproses,
                'selesai' => $selesai,
                'total' => $total,
                'progress' => $total > 0 ? (int) round(($selesai / $total) * 100) : 0,
            ];
        });

        $totalLayanan = $layananPerJenis->sum('total');
        $layananAktif = $layananPerJenis->sum('rencana') + $layananPerJenis->sum('diproses');
        $layananSelesai = $layananPerJenis->sum('selesai');

        $statusGlobal = [
            'rencana' => $scope->scopePemangkasan(Pemangkasan::query()->where('status', 'Rencana'), $user)->count(),
            'diproses' => $scope->scopePemangkasan(Pemangkasan::query()->where('status', 'Diproses'), $user)->count(),
            'selesai' => $scope->scopePemangkasan(Pemangkasan::query()->where('status', 'Selesai'), $user)->count(),
        ];

        $alertService = app(OperationalAlertService::class);
        $surveySummary = SurveyKepuasan::summary(now()->startOfMonth(), now()->endOfMonth());
        $jadwalCounts = $this->jadwalPermohonanCounts($user);
        $pemeliharaanJadwalCounts = $this->jadwalPemeliharaanCounts($user);
        $aduanBaru = $scope->scopeAduan(AduanMasyarakat::query()->where('status', 'Baru'), $user)->count();
        $aduanAktif = $scope->scopeAduan(
            AduanMasyarakat::query()->whereIn('status', ['Baru', 'Ditinjau', 'Diproses']),
            $user,
        )->count();
        $aduanSelesaiBulan = $scope->scopeAduan(
            AduanMasyarakat::query()
                ->where('status', 'Selesai')
                ->where('updated_at', '>=', now()->startOfMonth()),
            $user,
        )->count();

        $layananSelesaiPersen = $totalLayanan > 0
            ? (int) round(($layananSelesai / $totalLayanan) * 100)
            : 0;

        $operationalAlerts = $alertService->active();
        $operationalAlertsTotal = $alertService->totalCount();

        return [
            'generated_at' => now(),
            'total_taman' => $totalTaman,
            'total_luasan_taman' => $totalLuasanTaman,
            'total_varietas_bibit' => $totalVarietasBibit,
            'total_stok_bibit' => $totalStokBibit,
            'bibit_siap_tanam' => $bibitSiapTanam,
            'bibit_siap_persen' => $totalStokBibit > 0 ? (int) round(($bibitSiapTanam / $totalStokBibit) * 100) : 0,
            'total_armada' => $totalArmada,
            'armada_kondisi_baik_persen' => $armadaKondisiBaikPersen,
            'total_petugas' => $totalPetugas,
            'total_tim_kerja' => $totalTimKerja,
            'layanan_per_jenis' => $layananPerJenis,
            'total_layanan' => $totalLayanan,
            'layanan_aktif' => $layananAktif,
            'layanan_selesai' => $layananSelesai,
            'layanan_selesai_persen' => $layananSelesaiPersen,
            'status_global' => $statusGlobal,
            'operationalAlerts' => $operationalAlerts,
            'operationalAlertsTotal' => $operationalAlertsTotal,
            'survey_summary' => $surveySummary,
            'jadwal_counts' => $jadwalCounts,
            'pemeliharaan_jadwal_counts' => $pemeliharaanJadwalCounts,
            'pemeliharaan_hari_ini' => $this->pemeliharaanHariIni($user),
            'aduan_baru' => $aduanBaru,
            'aduan_aktif' => $aduanAktif,
            'aduan_selesai_bulan' => $aduanSelesaiBulan,
            'executive_status' => $this->resolveExecutiveStatus(
                $jadwalCounts['terlambat'] + $pemeliharaanJadwalCounts['terlambat'],
                $aduanBaru,
                $operationalAlerts,
            ),
            'operator_inbox' => $this->operatorInbox($user),
        ];
    }

    /**
     * @return array{aduan_baru: \Illuminate\Support\Collection, jadwal_hari_ini: \Illuminate\Support\Collection}
     */
    public function operatorInbox(?User $user = null): array
    {
        $scope = app(OperatorWilayahScope::class);

        return [
            'aduan_baru' => $scope->scopeAduan(
                AduanMasyarakat::query()->where('status', 'Baru')->latest(),
                $user,
            )
                ->limit(5)
                ->get(['id', 'nomor_aduan', 'lokasi', 'jenis_aduan', 'created_at']),
            'jadwal_hari_ini' => $scope->scopePemangkasan(JadwalLayananQuery::hariIni(), $user)
                ->orderBy('tanggal_eksekusi')
                ->limit(5)
                ->get(['id', 'jenis_layanan', 'lokasi_pohon', 'status', 'tanggal_eksekusi']),
        ];
    }

    /**
     * @return array{hari_ini: int, besok: int, diproses: int, terlambat: int}
     */
    private function jadwalPermohonanCounts(?User $user): array
    {
        $scope = app(OperatorWilayahScope::class);

        return [
            'hari_ini' => $scope->scopePemangkasan(JadwalLayananQuery::hariIni(), $user)->count(),
            'besok' => $scope->scopePemangkasan(JadwalLayananQuery::besok(), $user)->count(),
            'diproses' => $scope->scopePemangkasan(JadwalLayananQuery::diproses(), $user)->count(),
            'terlambat' => $scope->scopePemangkasan(JadwalLayananQuery::terlambat(), $user)->count(),
        ];
    }

    /**
     * @return array{hari_ini: int, besok: int, diproses: int, terlambat: int}
     */
    private function jadwalPemeliharaanCounts(?User $user): array
    {
        $scope = app(OperatorWilayahScope::class);

        return [
            'hari_ini' => $scope->scopePemeliharaan(JadwalPemeliharaanQuery::hariIni(), $user)->count(),
            'besok' => $scope->scopePemeliharaan(JadwalPemeliharaanQuery::besok(), $user)->count(),
            'diproses' => $scope->scopePemeliharaan(JadwalPemeliharaanQuery::diproses(), $user)->count(),
            'terlambat' => $scope->scopePemeliharaan(JadwalPemeliharaanQuery::terlambat(), $user)->count(),
        ];
    }

    /**
     * Input pelaksanaan pemeliharaan rutin yang tercatat pada hari ini.
     *
     * @return list<array{id: int, tim: string, tim_short: string, lokasi: string, progres: ?string, waktu: string, url: string}>
     */
    private function pemeliharaanHariIni(?User $user): array
    {
        $scope = app(OperatorWilayahScope::class);

        return $scope->scopePemeliharaan(
            PemeliharaanTaman::query()
                ->with('taman')
                ->whereDate('tanggal', today())
                ->orderByDesc('tanggal'),
            $user,
        )
            ->limit(15)
            ->get()
            ->map(function (PemeliharaanTaman $entry) {
                return [
                    'id' => $entry->id,
                    'tim' => (string) $entry->tim,
                    'tim_short' => Str::replaceFirst('Tim ', '', (string) $entry->tim),
                    'lokasi' => Str::limit($entry->lokasiLabel(), 48),
                    'progres' => $entry->lapanganRiwayatProgresLabel() ?? $entry->progressSummaryLabel(),
                    'waktu' => OperasionalPelaksanaanTime::display($entry->tanggal),
                    'url' => route('admin.pemeliharaan-tamans.export-pdf-operasional', $entry),
                ];
            })
            ->all();
    }

    /**
     * @param  list<array{severity: string}>  $alerts
     * @return array{label: string, tone: string, description: string}
     */
    public function resolveExecutiveStatus(int $terlambat, int $aduanBaru, array $alerts): array
    {
        $hasDangerAlert = collect($alerts)->contains(fn (array $alert) => $alert['severity'] === 'danger');

        if ($terlambat > 0 || $aduanBaru > 0 || $hasDangerAlert) {
            return [
                'label' => 'Perlu Tindakan',
                'tone' => 'danger',
                'description' => 'Terdapat jadwal terlambat, aduan baru, atau isu operasional kritis.',
            ];
        }

        if ($alerts !== []) {
            return [
                'label' => 'Waspada',
                'tone' => 'warning',
                'description' => 'Operasional berjalan, namun ada beberapa hal yang perlu dipantau.',
            ];
        }

        return [
            'label' => 'Operasional Baik',
            'tone' => 'success',
            'description' => 'Seluruh indikator utama dalam kondisi baik.',
        ];
    }
}
