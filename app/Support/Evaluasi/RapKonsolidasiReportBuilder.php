<?php

namespace App\Support\Evaluasi;

use App\Models\AduanMasyarakat;
use App\Models\User;
use App\Support\ReportPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RapKonsolidasiReportBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(Request $request, ?User $user, bool $forPdf = false): array
    {
        [$from, $to, $labelPeriode, $bulan, $tahun, $mode, $dari, $sampai] = ReportPeriod::resolve($request);

        $snapshotRequest = Request::create('/', 'GET', $request->only(['kategori', 'kecamatan_id']));

        $kelengkapan = KelengkapanDataReportBuilder::build($snapshotRequest, $user);
        $rthTerpelihara = RthTerpeliharaReportBuilder::build($snapshotRequest, $user);
        $masukan = MasukanMasyarakatReportBuilder::build($request, $user);
        $pemeliharaan = PemeliharaanPerTamanReportBuilder::build($request, $user);
        $armada = ArmadaUtilisasiReportBuilder::build($request, $user);
        $kinerjaTim = KinerjaTimReportBuilder::build($request, $user);
        $operasional = OperasionalPermohonanReportBuilder::build($request, $user);

        $armadaUtilisasi = $armada['totalArmada'] > 0
            ? (int) round(($armada['armadaTerpakai'] / $armada['totalArmada']) * 100)
            : null;

        $pillars = self::buildPillars(
            $request,
            $kelengkapan,
            $rthTerpelihara,
            $masukan,
            $pemeliharaan,
            $armada,
            $kinerjaTim,
            $operasional,
            $armadaUtilisasi,
        );

        $chart = self::buildChartMetrics($pillars);
        $prioritas = self::buildPrioritas($rthTerpelihara, $kelengkapan, $masukan, $forPdf);

        $scored = collect($pillars)
            ->flatMap(fn (array $pillar) => $pillar['indicators'])
            ->pluck('value')
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => (float) str_replace('%', '', (string) $value));

        $indeksKinerja = $scored->isNotEmpty()
            ? (int) round($scored->avg())
            : null;

        return [
            'mode' => $mode,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dari' => $dari,
            'sampai' => $sampai,
            'labelPeriode' => $labelPeriode,
            'labelSnapshot' => now()->timezone(config('app.timezone'))->translatedFormat('d F Y H:i'),
            'kategori' => (string) $request->input('kategori', ''),
            'kecamatan_id' => (string) $request->input('kecamatan_id', ''),
            'indeksKinerja' => $indeksKinerja,
            'pillars' => $pillars,
            'prioritas' => $prioritas,
            'chartLabels' => $chart['labels'],
            'chartValues' => $chart['values'],
            'chartColors' => $chart['colors'],
            'ringkasanOperasional' => [
                'totalKegiatanPemeliharaan' => $pemeliharaan['totalKegiatan'],
                'coveragePemeliharaan' => $pemeliharaan['coveragePercent'],
                'totalPermohonan' => $operasional['totalPermohonan'],
                'persenSelesaiPermohonan' => $operasional['persenSelesai'],
                'persenTepatWaktuPermohonan' => $operasional['persenTepatWaktu'],
                'totalPenugasanArmada' => $armada['totalPenugasan'],
                'armadaUtilisasi' => $armadaUtilisasi,
                'rataTepatWaktuTim' => $kinerjaTim['rataTepatWaktu'],
            ],
            'ringkasanBasisData' => [
                'totalLokasi' => $kelengkapan['totalLokasi'],
                'persenLengkap' => $kelengkapan['persenLengkap'],
                'persenMutakhir' => $kelengkapan['persenMutakhir'],
                'persenRthTerpelihara' => $rthTerpelihara['persenLokasi'],
                'persenLuasanTerpelihara' => $rthTerpelihara['persenLuasan'],
            ],
            'ringkasanPartisipasi' => [
                'totalMasukan' => $masukan['totalMasukan'],
                'persenSelesaiAduan' => $masukan['persenSelesai'],
                'aduanOverdue' => $masukan['aduanOverdue'],
                'surveyAverage' => $masukan['surveyAverage'],
                'persenPuas' => $masukan['persenPuas'],
            ],
            'daftarBulan' => ReportPeriod::daftarBulan(),
            'daftarTahun' => ReportPeriod::daftarTahun(5),
            'daftarKategori' => $kelengkapan['daftarKategori'],
            'daftarKecamatan' => $kelengkapan['daftarKecamatan'],
        ];
    }

    /**
     * @param  array<string, mixed>  $kelengkapan
     * @param  array<string, mixed>  $rthTerpelihara
     * @param  array<string, mixed>  $masukan
     * @param  array<string, mixed>  $pemeliharaan
     * @param  array<string, mixed>  $armada
     * @param  array<string, mixed>  $kinerjaTim
     * @param  array<string, mixed>  $operasional
     * @return list<array{pillar: string, description: string, indicators: list<array<string, mixed>>}>
     */
    private static function buildPillars(
        Request $request,
        array $kelengkapan,
        array $rthTerpelihara,
        array $masukan,
        array $pemeliharaan,
        array $armada,
        array $kinerjaTim,
        array $operasional,
        ?int $armadaUtilisasi,
    ): array {
        $snapshotQuery = $request->only(['kategori', 'kecamatan_id']);
        $periodQuery = $request->query();

        return [
            [
                'pillar' => 'Basis Data RTH',
                'description' => 'Kelengkapan dan kemutakhiran profil taman sesuai standar basis data RTH',
                'indicators' => [
                    self::indicator(
                        'Kelengkapan Profil',
                        $kelengkapan['persenLengkap'],
                        number_format($kelengkapan['lokasiLengkap']).' / '.number_format($kelengkapan['totalLokasi']).' lokasi',
                        route('admin.evaluasi.kelengkapan-data.index', $snapshotQuery),
                    ),
                    self::indicator(
                        'Data Mutakhir',
                        $kelengkapan['persenMutakhir'],
                        'Verifikasi ≤ '.$kelengkapan['freshDays'].' hari',
                        route('admin.evaluasi.kelengkapan-data.index', $snapshotQuery),
                    ),
                    self::indicator(
                        'RTH Terpelihara (Lokasi)',
                        $rthTerpelihara['persenLokasi'],
                        number_format($rthTerpelihara['lokasiTerpelihara']).' / '.number_format($rthTerpelihara['totalLokasi']).' lokasi',
                        route('admin.evaluasi.rth-terpelihara.index', $snapshotQuery),
                    ),
                    self::indicator(
                        'RTH Terpelihara (Luasan)',
                        $rthTerpelihara['persenLuasan'],
                        number_format($rthTerpelihara['luasanTerpelihara'], 0, ',', '.').' m² terpelihara',
                        route('admin.evaluasi.rth-terpelihara.index', $snapshotQuery),
                    ),
                ],
            ],
            [
                'pillar' => 'Monitoring Operasional',
                'description' => 'Kinerja pemeliharaan rutin, permohonan layanan, armada, dan tim pelaksana',
                'indicators' => [
                    self::indicator(
                        'Cakupan Pemeliharaan',
                        $pemeliharaan['coveragePercent'],
                        number_format($pemeliharaan['totalTamanTerlayani']).' taman terlayani periode',
                        route('admin.evaluasi.pemeliharaan.index', $periodQuery),
                    ),
                    self::indicator(
                        'Permohonan Selesai',
                        $operasional['persenSelesai'],
                        number_format($operasional['totalSelesai']).' / '.number_format($operasional['totalPermohonan']).' permohonan',
                        route('admin.evaluasi.operasional-permohonan.index', $periodQuery),
                    ),
                    self::indicator(
                        'SLA Permohonan',
                        $operasional['persenTepatWaktu'],
                        $operasional['rataHariPenyelesaian'] !== null
                            ? 'Rata. '.$operasional['rataHariPenyelesaian'].' hari penyelesaian'
                            : 'Belum ada permohonan selesai',
                        route('admin.evaluasi.operasional-permohonan.index', $periodQuery),
                    ),
                    self::indicator(
                        'Utilisasi Armada',
                        $armadaUtilisasi,
                        number_format($armada['armadaTerpakai']).' / '.number_format($armada['totalArmada']).' armada aktif',
                        route('admin.evaluasi.armada.index', $periodQuery),
                    ),
                    self::indicator(
                        'Kinerja Tim (Tepat Waktu)',
                        $kinerjaTim['rataTepatWaktu'],
                        number_format($kinerjaTim['totalKegiatan']).' kegiatan tim',
                        route('admin.evaluasi.kinerja-tim.index', $periodQuery),
                    ),
                ],
            ],
            [
                'pillar' => 'Partisipasi Masyarakat',
                'description' => 'Respons aduan dan tingkat kepuasan survey masyarakat',
                'indicators' => [
                    self::indicator(
                        'Penyelesaian Aduan',
                        $masukan['persenSelesai'],
                        number_format($masukan['aduanSelesai']).' / '.number_format($masukan['totalAduan']).' aduan periode',
                        route('admin.evaluasi.masukan-masyarakat.index', $periodQuery),
                    ),
                    self::indicator(
                        'Aduan Ditanggapi',
                        $masukan['persenDitanggapi'],
                        number_format($masukan['aduanOverdue']).' aduan terlambat ditinjau',
                        route('admin.evaluasi.masukan-masyarakat.index', $periodQuery),
                    ),
                    self::indicator(
                        'Kepuasan Survey',
                        $masukan['persenPuas'],
                        $masukan['surveyAverage'] !== null
                            ? 'Rata-rata '.$masukan['surveyAverage'].' / 5 ('.number_format($masukan['totalSurvey']).' responden)'
                            : 'Belum ada survey periode',
                        route('admin.evaluasi.masukan-masyarakat.index', $periodQuery),
                    ),
                ],
            ],
        ];
    }

    /**
     * @return array{label: string, value: string|null, detail: string, url: string, level: string, numeric: float|null}
     */
    private static function indicator(string $label, mixed $percent, string $detail, string $url): array
    {
        $numeric = is_numeric($percent) ? (float) $percent : null;

        return [
            'label' => $label,
            'value' => $numeric !== null ? number_format($numeric, $numeric == (int) $numeric ? 0 : 1).'%' : '—',
            'detail' => $detail,
            'url' => $url,
            'level' => self::statusLevel($numeric),
            'numeric' => $numeric,
        ];
    }

    private static function statusLevel(?float $percent): string
    {
        if ($percent === null) {
            return 'neutral';
        }

        if ($percent >= 80) {
            return 'good';
        }

        if ($percent >= 60) {
            return 'warn';
        }

        return 'bad';
    }

    /**
     * @param  list<array{pillar: string, description: string, indicators: list<array<string, mixed>>}>  $pillars
     * @return array{labels: list<string>, values: list<float>, colors: list<string>}
     */
    private static function buildChartMetrics(array $pillars): array
    {
        $indicators = collect($pillars)
            ->flatMap(fn (array $pillar) => $pillar['indicators'])
            ->filter(fn (array $indicator) => $indicator['numeric'] !== null)
            ->values();

        $colorMap = [
            'good' => 'rgba(16, 185, 129, 0.8)',
            'warn' => 'rgba(245, 158, 11, 0.8)',
            'bad' => 'rgba(239, 68, 68, 0.8)',
            'neutral' => 'rgba(148, 163, 184, 0.8)',
        ];

        return [
            'labels' => $indicators->pluck('label')->all(),
            'values' => $indicators->pluck('numeric')->map(fn ($v) => (float) $v)->all(),
            'colors' => $indicators->pluck('level')->map(fn (string $level) => $colorMap[$level])->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $rthTerpelihara
     * @param  array<string, mixed>  $kelengkapan
     * @param  array<string, mixed>  $masukan
     * @return Collection<int, array<string, mixed>>
     */
    private static function buildPrioritas(array $rthTerpelihara, array $kelengkapan, array $masukan, bool $forPdf): Collection
    {
        $items = collect();

        foreach (($rthTerpelihara['belumTerpelihara'] ?? collect())->take(5) as $row) {
            $items->push([
                'prioritas' => 'RTH Belum Terpelihara',
                'label' => $row['nama'],
                'detail' => $row['latest_maintenance']
                    ? 'Pemeliharaan terakhir '.$row['hari_sejak'].' hari lalu'
                    : 'Belum pernah dipelihara',
                'url' => $row['url'],
                'level' => 'bad',
            ]);
        }

        foreach (($kelengkapan['perluPerhatian'] ?? collect())->take(5) as $row) {
            $items->push([
                'prioritas' => 'Profil Data',
                'label' => $row['nama'],
                'detail' => 'Skor '.$row['score'].'% · '.($row['is_mutakhir'] ? 'mutakhir' : 'perlu verifikasi'),
                'url' => $row['edit_url'],
                'level' => $row['is_lengkap'] ? 'warn' : 'bad',
            ]);
        }

        /** @var Collection<int, AduanMasyarakat> $openAduans */
        $openAduans = $masukan['openAduans'] ?? collect();
        foreach ($openAduans->filter(
            fn (AduanMasyarakat $aduan) => $aduan->status === 'Baru'
                && $aduan->created_at->lte(now()->subDays($masukan['unreviewedDays'])),
        )->take(5) as $aduan) {
            $items->push([
                'prioritas' => 'Aduan Terlambat',
                'label' => $aduan->nomor_aduan,
                'detail' => $aduan->jenis_aduan.' · '.$aduan->created_at->diffInDays(now()).' hari',
                'url' => route('admin.aduan-masyarakats.show', $aduan),
                'level' => 'bad',
            ]);
        }

        return $forPdf ? $items : $items->take(15);
    }
}
