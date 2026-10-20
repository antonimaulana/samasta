<?php

namespace App\Support\Evaluasi;

use App\Models\User;
use App\Support\ReportPeriod;
use Illuminate\Http\Request;

class EvaluasiOverviewBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(Request $request, User $user): array
    {
        $rentang = ReportPeriod::normalizedRentang($request);
        [$from, $to, $labelPeriode, $bulan, $tahun] = array_slice(ReportPeriod::resolve($request), 0, 5);
        $query = ReportPeriod::queryParams($request);

        $pemeliharaan = PemeliharaanPerTamanReportBuilder::build($request, $user);
        $armada = ArmadaUtilisasiReportBuilder::build($request, $user);
        $kinerjaTim = KinerjaTimReportBuilder::build($request, $user);
        $operasional = OperasionalPermohonanReportBuilder::build($request, $user);
        $masukan = MasukanMasyarakatReportBuilder::build($request, $user);
        $rth = RthTerpeliharaReportBuilder::build($request, $user);
        $kelengkapan = KelengkapanDataReportBuilder::build($request, $user);

        $armadaUtil = $armada['totalArmada'] > 0
            ? (int) round(($armada['armadaTerpakai'] / $armada['totalArmada']) * 100)
            : null;

        $aspects = [
            self::aspect(
                id: 'pemeliharaan',
                title: 'Pemeliharaan per Taman',
                icon: '🌳',
                accent: 'green',
                score: (int) $pemeliharaan['coveragePercent'],
                insight: self::insightPemeliharaan($pemeliharaan),
                metrics: [
                    ['label' => 'Kegiatan', 'value' => number_format($pemeliharaan['totalKegiatan'])],
                    ['label' => 'Taman terlayani', 'value' => $pemeliharaan['totalTamanTerlayani'].' / '.$pemeliharaan['totalTamanInScope']],
                    ['label' => 'Cakupan', 'value' => $pemeliharaan['coveragePercent'].'%'],
                    ['label' => 'Personil', 'value' => number_format($pemeliharaan['totalPersonil'])],
                ],
                detailRoute: 'admin.evaluasi.pemeliharaan.index',
                exportRoute: 'admin.evaluasi.pemeliharaan.export-pdf',
                query: $query,
                periodScoped: true,
            ),
            self::aspect(
                id: 'armada',
                title: 'Utilisasi Armada',
                icon: '🚛',
                accent: 'blue',
                score: $armadaUtil,
                insight: self::insightArmada($armada, $armadaUtil),
                metrics: [
                    ['label' => 'Penugasan', 'value' => number_format($armada['totalPenugasan'])],
                    ['label' => 'Armada aktif', 'value' => $armada['armadaTerpakai'].' / '.$armada['totalArmada']],
                    ['label' => 'Pemeliharaan', 'value' => number_format($armada['totalPemeliharaan'])],
                    ['label' => 'Permohonan', 'value' => number_format($armada['totalPermohonan'])],
                ],
                detailRoute: 'admin.evaluasi.armada.index',
                exportRoute: 'admin.evaluasi.armada.export-pdf',
                query: $query,
                periodScoped: true,
            ),
            self::aspect(
                id: 'kinerja-tim',
                title: 'Kinerja Tim',
                icon: '👷',
                accent: 'violet',
                score: $kinerjaTim['rataTepatWaktu'],
                insight: self::insightKinerja($kinerjaTim),
                metrics: [
                    ['label' => 'Total kegiatan', 'value' => number_format($kinerjaTim['totalKegiatan'])],
                    ['label' => 'Pemeliharaan', 'value' => number_format($kinerjaTim['totalPemeliharaan'])],
                    ['label' => 'Permohonan', 'value' => number_format($kinerjaTim['totalPermohonan'])],
                    ['label' => 'Tepat waktu', 'value' => $kinerjaTim['rataTepatWaktu'] !== null ? $kinerjaTim['rataTepatWaktu'].'%' : '—'],
                ],
                detailRoute: 'admin.evaluasi.kinerja-tim.index',
                exportRoute: 'admin.evaluasi.kinerja-tim.export-pdf',
                query: $query,
                periodScoped: true,
            ),
            self::aspect(
                id: 'operasional-permohonan',
                title: 'Operasional Permohonan',
                icon: '📋',
                accent: 'orange',
                score: (int) $operasional['persenSelesai'],
                insight: self::insightOperasional($operasional),
                metrics: [
                    ['label' => 'Permohonan', 'value' => number_format($operasional['totalPermohonan'])],
                    ['label' => 'Selesai', 'value' => number_format($operasional['totalSelesai'])],
                    ['label' => 'Diproses', 'value' => number_format($operasional['totalDiproses'])],
                    ['label' => 'Dokumentasi', 'value' => $operasional['persenDokumentasiLengkap'].'%'],
                ],
                detailRoute: 'admin.evaluasi.operasional-permohonan.index',
                exportRoute: 'admin.evaluasi.operasional-permohonan.export-pdf',
                query: $query,
                periodScoped: true,
            ),
            self::aspect(
                id: 'rth-terpelihara',
                title: 'RTH Terpelihara',
                icon: '🌿',
                accent: 'teal',
                score: (int) round($rth['persenLokasi']),
                insight: self::insightRth($rth),
                metrics: [
                    ['label' => 'Lokasi terpelihara', 'value' => $rth['lokasiTerpelihara'].' / '.$rth['totalLokasi']],
                    ['label' => 'Persen lokasi', 'value' => $rth['persenLokasi'].'%'],
                    ['label' => 'Luasan terpelihara', 'value' => number_format($rth['luasanTerpelihara']).' m²'],
                    ['label' => 'Belum terpelihara', 'value' => number_format($rth['lokasiBelumTerpelihara'])],
                ],
                detailRoute: 'admin.evaluasi.rth-terpelihara.index',
                exportRoute: 'admin.evaluasi.rth-terpelihara.export-pdf',
                query: $query,
                periodScoped: false,
                footnote: 'Snapshot — berdasarkan pemeliharaan rutin '.$rth['freshDays'].' hari terakhir.',
            ),
            self::aspect(
                id: 'kelengkapan-data',
                title: 'Kelengkapan Data',
                icon: '📊',
                accent: 'indigo',
                score: (int) round($kelengkapan['persenLengkap']),
                insight: self::insightKelengkapan($kelengkapan),
                metrics: [
                    ['label' => 'Data lengkap', 'value' => $kelengkapan['lokasiLengkap'].' / '.$kelengkapan['totalLokasi']],
                    ['label' => 'Skor rata-rata', 'value' => $kelengkapan['rataScore'].'%'],
                    ['label' => 'Mutakhir', 'value' => $kelengkapan['persenMutakhir'].'%'],
                    ['label' => 'Perlu perhatian', 'value' => number_format($kelengkapan['lokasiBelumLengkap'] + $kelengkapan['lokasiKedaluwarsa'])],
                ],
                detailRoute: 'admin.evaluasi.kelengkapan-data.index',
                exportRoute: 'admin.evaluasi.kelengkapan-data.export-pdf',
                query: $query,
                periodScoped: false,
                footnote: 'Snapshot profil RTH — tidak tergantung rentang waktu di atas.',
            ),
            self::aspect(
                id: 'masukan-masyarakat',
                title: 'Masukan Masyarakat',
                icon: '💬',
                accent: 'rose',
                score: $masukan['persenSelesai'],
                insight: self::insightMasukan($masukan),
                metrics: [
                    ['label' => 'Total masukan', 'value' => number_format($masukan['totalMasukan'])],
                    ['label' => 'Aduan selesai', 'value' => $masukan['persenSelesai'].'%'],
                    ['label' => 'Aduan terbuka', 'value' => number_format($masukan['aduanOpen'])],
                    ['label' => 'Kepuasan (≥4)', 'value' => $masukan['persenPuas'] !== null ? $masukan['persenPuas'].'%' : '—'],
                ],
                detailRoute: 'admin.evaluasi.masukan-masyarakat.index',
                exportRoute: 'admin.evaluasi.masukan-masyarakat.export-pdf',
                query: $query,
                periodScoped: true,
            ),
        ];

        $scores = collect($aspects)
            ->pluck('score')
            ->filter(fn ($score) => $score !== null)
            ->map(fn ($score) => (int) $score);

        $indeksKinerja = $scores->isNotEmpty() ? (int) round($scores->avg()) : null;

        return [
            'rentang' => $rentang,
            'rentangOptions' => ReportPeriod::rentangOptions(),
            'bulan' => $bulan,
            'tahun' => $tahun,
            'daftarBulan' => ReportPeriod::daftarBulan(),
            'daftarTahun' => ReportPeriod::daftarTahun(5),
            'labelPeriode' => $labelPeriode,
            'dari' => $from->toDateString(),
            'sampai' => $to->toDateString(),
            'indeksKinerja' => $indeksKinerja,
            'aspects' => $aspects,
            'highlights' => self::buildHighlights($aspects, $indeksKinerja),
            'showRapKonsolidasi' => (bool) config('simtaman.features.rap_konsolidasi', false),
            'rapRoute' => route('admin.evaluasi.rap-konsolidasi.index', $query),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function aspect(
        string $id,
        string $title,
        string $icon,
        string $accent,
        ?int $score,
        string $insight,
        array $metrics,
        string $detailRoute,
        string $exportRoute,
        array $query,
        bool $periodScoped,
        ?string $footnote = null,
    ): array {
        return [
            'id' => $id,
            'title' => $title,
            'icon' => $icon,
            'accent' => $accent,
            'score' => $score,
            'scoreLabel' => $score !== null ? $score.'%' : '—',
            'tone' => self::scoreTone($score),
            'insight' => $insight,
            'metrics' => $metrics,
            'detailUrl' => route($detailRoute, $query),
            'exportUrl' => route($exportRoute, $query),
            'periodScoped' => $periodScoped,
            'footnote' => $footnote,
        ];
    }

    private static function scoreTone(?int $score): string
    {
        if ($score === null) {
            return 'neutral';
        }

        if ($score >= 75) {
            return 'good';
        }

        if ($score >= 50) {
            return 'warn';
        }

        return 'risk';
    }

    /**
     * @param  array<string, mixed>  $p
     */
    private static function insightPemeliharaan(array $p): string
    {
        if ($p['totalTamanInScope'] === 0) {
            return 'Belum ada taman dalam cakupan wilayah untuk dinilai.';
        }

        if ($p['coveragePercent'] >= 80) {
            return 'Cakupan pemeliharaan rutin kuat — sebagian besar taman tercatat dalam periode ini.';
        }

        if ($p['tamanBelumDipelihara']->count() > 0) {
            return $p['tamanBelumDipelihara']->count().' taman belum tercatat pemeliharaannya — prioritaskan kunjungan lapangan.';
        }

        return 'Tingkatkan frekuensi kunjungan agar cakupan mendekati 100%.';
    }

    /**
     * @param  array<string, mixed>  $a
     */
    private static function insightArmada(array $a, ?int $util): string
    {
        if ($a['totalArmada'] === 0) {
            return 'Inventaris armada belum diisi — data utilisasi belum bisa dinilai.';
        }

        if ($util !== null && $util >= 70) {
            return 'Armada banyak dimanfaatkan untuk pemeliharaan dan permohonan.';
        }

        if ($a['armadaIdle'] > 0) {
            return $a['armadaIdle'].' unit belum tercatat penugasan — cek koordinasi jadwal tim Armada.';
        }

        return 'Pantau distribusi sopir dan beban penugasan per unit.';
    }

    /**
     * @param  array<string, mixed>  $k
     */
    private static function insightKinerja(array $k): string
    {
        if ($k['totalKegiatan'] === 0) {
            return 'Belum ada kegiatan tim tercatat pada periode ini.';
        }

        if ($k['rataTepatWaktu'] !== null && $k['rataTepatWaktu'] >= 80) {
            return 'Tim umumnya menyelesaikan pekerjaan sesuai jadwal yang direncanakan.';
        }

        return 'Bandingkan beban antar tim dan identifikasi tim dengan progres permohonan tertinggi.';
    }

    /**
     * @param  array<string, mixed>  $o
     */
    private static function insightOperasional(array $o): string
    {
        if ($o['totalPermohonan'] === 0) {
            return 'Tidak ada permohonan operasional pada periode ini.';
        }

        if ($o['persenSelesai'] >= 70) {
            return 'Mayoritas permohonan selesai — lanjutkan dokumentasi foto progres.';
        }

        if ($o['totalDiproses'] > 0) {
            return $o['totalDiproses'].' permohonan masih diproses — pastikan update harian di lapangan.';
        }

        return 'Percepat penyelesaian permohonan rencana yang sudah jatuh tempo.';
    }

    /**
     * @param  array<string, mixed>  $r
     */
    private static function insightRth(array $r): string
    {
        if ($r['totalLokasi'] === 0) {
            return 'Belum ada data lokasi RTH.';
        }

        if ($r['persenLokasi'] >= 85) {
            return 'Sebagian besar lokasi RTH memiliki jejak pemeliharaan rutin recent.';
        }

        return $r['lokasiBelumTerpelihara'].' lokasi belum terpelihara dalam '.$r['freshDays'].' hari — risiko penurunan kualitas taman.';
    }

    /**
     * @param  array<string, mixed>  $k
     */
    private static function insightKelengkapan(array $k): string
    {
        if ($k['totalLokasi'] === 0) {
            return 'Belum ada profil taman untuk dinilai.';
        }

        if ($k['persenLengkap'] >= 90) {
            return 'Profil taman mayoritas lengkap — fokus pada verifikasi berkala.';
        }

        return 'Lengkapi field profil yang masih kosong agar peta dan laporan publik akurat.';
    }

    /**
     * @param  array<string, mixed>  $m
     */
    private static function insightMasukan(array $m): string
    {
        if ($m['totalMasukan'] === 0) {
            return 'Belum ada aduan atau survei pada periode ini.';
        }

        if ($m['aduanOverdue'] > 0) {
            return $m['aduanOverdue'].' aduan melewati batas tinjau — respons cepat meningkatkan kepercayaan warga.';
        }

        if ($m['persenSelesai'] >= 75) {
            return 'Tindak lanjut aduan mayoritas selesai dalam periode ini.';
        }

        return 'Tingkatkan penyelesaian aduan terbuka dan dokumentasi tindak lanjut.';
    }

    /**
     * @param  list<array<string, mixed>>  $aspects
     * @return list<string>
     */
    private static function buildHighlights(array $aspects, ?int $indeks): array
    {
        $lines = [];

        if ($indeks !== null) {
            $lines[] = $indeks >= 75
                ? 'Indeks gabungan '.$indeks.'% — kinerja operasional dan data dalam kondisi baik.'
                : 'Indeks gabungan '.$indeks.'% — ada aspek yang perlu intervensi segera.';
        }

        $risk = collect($aspects)
            ->filter(fn (array $a) => $a['tone'] === 'risk')
            ->pluck('title');

        if ($risk->isNotEmpty()) {
            $lines[] = 'Perhatian utama: '.$risk->implode(', ').'.';
        }

        if ($lines === []) {
            $lines[] = 'Gunakan kartu di bawah untuk melihat indikator per aspek evaluasi.';
        }

        return $lines;
    }
}
