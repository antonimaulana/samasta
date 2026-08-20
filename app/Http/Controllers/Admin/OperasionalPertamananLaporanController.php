<?php



namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Controller;

use App\Models\Pemangkasan;

use App\Support\PdfExport;

use App\Support\TableSearch;

use Carbon\Carbon;

use Illuminate\Http\Request;

use Illuminate\Http\Response;

use Illuminate\View\View;



class OperasionalPertamananLaporanController extends Controller

{

    public function __construct()

    {

        $this->middleware(function ($request, $next) {

            $this->authorize('viewAny', Pemangkasan::class);



            return $next($request);

        });

    }



    public function index(Request $request): View

    {

        $data = $this->buildReport($request);



        return view('admin.operasional_pertamanan_laporan.index', $data);

    }



    public function exportPdf(Request $request): Response

    {

        PdfExport::ensureGdLoaded();



        $data = $this->buildReport($request, forPdf: true);



        $html = view('admin.operasional_pertamanan_laporan.pdf', $data)->render();



        $slug = str($data['labelPeriode'])->slug('-')->limit(40, '');

        $filename = 'laporan-operasional-pertamanan-'.$slug.'.pdf';



        return PdfExport::download($html, $filename, 'landscape');

    }



    /**

     * @return array<string, mixed>

     */

    private function buildReport(Request $request, bool $forPdf = false): array

    {

        $periode = $this->resolvePeriode($request);

        extract($periode);



        $search = trim((string) $request->input('search', ''));



        $searchColumns = [

            'lokasi_pohon',

            'pelaksana',

            'jenis_layanan',

            'status',

            'asal',

            'penanggungjawab',

            'kontak_permohonan',

            'kategori',

        ];



        $baseQuery = Pemangkasan::query()

            ->whereBetween('tanggal_eksekusi', [$awal->toDateString(), $akhir->toDateString()]);



        if ($jenisLayanan !== '') {

            $baseQuery->where('jenis_layanan', $jenisLayanan);

        }



        $filteredQuery = clone $baseQuery;

        if ($search !== '') {

            TableSearch::apply($filteredQuery, $request, $searchColumns);

        }



        $totalLayanan = (clone $filteredQuery)->count();



        $totalsPerJenis = collect(Pemangkasan::JENIS_LAYANAN)

            ->mapWithKeys(fn (string $jenis) => [

                $jenis => (clone $filteredQuery)->where('jenis_layanan', $jenis)->count(),

            ]);



        $totalSelesai = (clone $filteredQuery)->where('status', 'Selesai')->count();

        $totalDiproses = (clone $filteredQuery)->where('status', 'Diproses')->count();

        $totalRencana = (clone $filteredQuery)->where('status', 'Rencana')->count();



        $ringkasanPerJenis = collect(Pemangkasan::JENIS_LAYANAN)

            ->when($jenisLayanan !== '', fn ($c) => $c->filter(fn ($j) => $j === $jenisLayanan))

            ->map(function (string $jenis) use ($filteredQuery) {

                $query = (clone $filteredQuery)->where('jenis_layanan', $jenis);



                return [

                    'jenis' => $jenis,

                    'rencana' => (clone $query)->where('status', 'Rencana')->count(),

                    'diproses' => (clone $query)->where('status', 'Diproses')->count(),

                    'selesai' => (clone $query)->where('status', 'Selesai')->count(),

                    'total' => (clone $query)->count(),

                ];

            })

            ->when($search !== '' || $jenisLayanan !== '', fn ($c) => $c->filter(fn (array $row) => $row['total'] > 0))

            ->values();



        $chartLabels = [];

        $chartDatasets = [];



        $jenisChart = collect(Pemangkasan::JENIS_LAYANAN)

            ->when($jenisLayanan !== '', fn ($c) => $c->filter(fn ($j) => $j === $jenisLayanan));



        if ($mode === 'bulan') {

            for ($day = 1; $day <= $awal->daysInMonth; $day++) {

                $chartLabels[] = (string) $day;

            }

        } else {

            $cursor = $awal->copy();

            while ($cursor->lte($akhir)) {

                $chartLabels[] = $cursor->format('d/m');

                $cursor->addDay();

            }

        }



        foreach ($jenisChart as $jenis) {

            $data = [];



            if ($mode === 'bulan') {

                for ($day = 1; $day <= $awal->daysInMonth; $day++) {

                    $date = Carbon::create($tahun, $bulan, $day)->toDateString();

                    $data[] = $this->countOperasional($date, $jenis, $jenisLayanan);

                }

            } else {

                $cursor = $awal->copy();

                while ($cursor->lte($akhir)) {

                    $data[] = $this->countOperasional($cursor->toDateString(), $jenis, $jenisLayanan);

                    $cursor->addDay();

                }

            }



            [$backgroundColor, $borderColor] = Pemangkasan::chartColors($jenis);



            $chartDatasets[] = [

                'label' => $jenis,

                'data' => $data,

                'backgroundColor' => $backgroundColor,

                'borderColor' => $borderColor,

            ];

        }



        $daftarQuery = clone $filteredQuery;

        $daftarLayanan = $daftarQuery

            ->latest('tanggal_eksekusi')

            ->when(! $forPdf, fn ($q) => $q->limit(20))

            ->get();



        $riwayatQuery = clone $filteredQuery;

        $riwayatSelesai = $riwayatQuery

            ->where('status', 'Selesai')

            ->latest('tanggal_eksekusi')

            ->when(! $forPdf, fn ($q) => $q->limit(10))

            ->get();



        return [

            'mode' => $mode,

            'bulan' => $bulan,

            'tahun' => $tahun,

            'dari' => $dari,

            'sampai' => $sampai,

            'jenisLayanan' => $jenisLayanan,

            'labelPeriode' => $labelPeriode,

            'totalLayanan' => $totalLayanan,

            'totalsPerJenis' => $totalsPerJenis,

            'totalSelesai' => $totalSelesai,

            'totalDiproses' => $totalDiproses,

            'totalRencana' => $totalRencana,

            'ringkasanPerJenis' => $ringkasanPerJenis,

            'chartLabels' => $chartLabels,

            'chartDatasets' => $chartDatasets,

            'daftarLayanan' => $daftarLayanan,

            'riwayatSelesai' => $riwayatSelesai,

            'daftarBulan' => $this->daftarBulan(),

            'daftarTahun' => range(now()->year, now()->year - 5),

            'search' => $search,

        ];

    }



    /**

     * @return array{mode: string, bulan: int, tahun: int, dari: string, sampai: string, awal: \Carbon\Carbon, akhir: \Carbon\Carbon, labelPeriode: string, jenisLayanan: string}

     */

    private function resolvePeriode(Request $request): array

    {

        $mode = $request->input('mode', 'bulan');

        $bulan = (int) $request->input('bulan', now()->month);

        $tahun = (int) $request->input('tahun', now()->year);

        $dari = $request->input('dari');

        $sampai = $request->input('sampai');

        $jenisLayanan = (string) ($request->input('jenis_layanan') ?? '');



        if ($mode === 'periode' && $dari && $sampai) {

            $awal = Carbon::parse($dari)->startOfDay();

            $akhir = Carbon::parse($sampai)->endOfDay();

            $labelPeriode = $awal->format('d M Y').' — '.$akhir->format('d M Y');

        } else {

            $mode = 'bulan';

            $awal = Carbon::create($tahun, $bulan, 1)->startOfMonth();

            $akhir = $awal->copy()->endOfMonth();

            $labelPeriode = ($this->daftarBulan()[$bulan] ?? (string) $bulan).' '.$tahun;

            $dari = $awal->format('Y-m-d');

            $sampai = $akhir->format('Y-m-d');

        }



        return compact('mode', 'bulan', 'tahun', 'dari', 'sampai', 'awal', 'akhir', 'labelPeriode', 'jenisLayanan');

    }



    private function countOperasional(string $date, string $jenis, ?string $filterJenis): int

    {

        if ($filterJenis !== null && $filterJenis !== '' && $filterJenis !== $jenis) {

            return 0;

        }



        return Pemangkasan::query()

            ->whereDate('tanggal_eksekusi', $date)

            ->where('jenis_layanan', $jenis)

            ->count();

    }



    private function daftarBulan(): array

    {

        return [

            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',

            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',

            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',

        ];

    }

}

