<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bibit;
use App\Models\BibitKeluar;
use App\Models\BibitMasuk;
use App\Support\PdfExport;
use App\Support\TableSearch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BibitLaporanController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->authorize('viewAny', Bibit::class);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $data = $this->buildReport($request);

        return view('admin.bibit_laporan.index', $data);
    }

    public function exportPdf(Request $request): Response
    {
        PdfExport::ensureGdLoaded();

        $data = $this->buildReport($request, forPdf: true);

        $html = view('admin.bibit_laporan.pdf', $data)->render();

        $slug = str($data['labelPeriode'])->slug('-')->limit(40, '');
        $filename = 'laporan-bibit-'.$slug.'.pdf';

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

        $totalMasuk = (int) BibitMasuk::whereBetween('tanggal_masuk', [$awal, $akhir])->sum('jumlah');
        $totalKeluar = (int) BibitKeluar::whereBetween('tanggal_keluar', [$awal, $akhir])->sum('jumlah');
        $mutasiBersih = $totalMasuk - $totalKeluar;
        $jumlahTransaksi = BibitMasuk::whereBetween('tanggal_masuk', [$awal, $akhir])->count()
            + BibitKeluar::whereBetween('tanggal_keluar', [$awal, $akhir])->count();

        $masukPerBibit = BibitMasuk::whereBetween('tanggal_masuk', [$awal, $akhir])
            ->selectRaw('bibit_id, SUM(jumlah) as total')
            ->groupBy('bibit_id')
            ->pluck('total', 'bibit_id');

        $keluarPerBibit = BibitKeluar::whereBetween('tanggal_keluar', [$awal, $akhir])
            ->selectRaw('bibit_id, SUM(jumlah) as total')
            ->groupBy('bibit_id')
            ->pluck('total', 'bibit_id');

        $bibitIds = $masukPerBibit->keys()->merge($keluarPerBibit->keys())->unique();

        $mutasiPerBibit = Bibit::whereIn('id', $bibitIds)
            ->orderBy('nama_tanaman')
            ->get()
            ->map(function (Bibit $bibit) use ($masukPerBibit, $keluarPerBibit) {
                $masuk = (int) ($masukPerBibit[$bibit->id] ?? 0);
                $keluar = (int) ($keluarPerBibit[$bibit->id] ?? 0);

                return [
                    'bibit' => $bibit,
                    'masuk' => $masuk,
                    'keluar' => $keluar,
                    'mutasi' => $masuk - $keluar,
                    'stok_sekarang' => $bibit->stok_tersedia,
                ];
            });

        if ($search !== '') {
            $mutasiPerBibit = $mutasiPerBibit->filter(function (array $row) use ($search) {
                return TableSearch::matches($row['bibit']->nama_tanaman, $search)
                    || TableSearch::matches($row['bibit']->jenis, $search);
            })->values();
        }

        $chartLabels = [];
        $chartMasuk = [];
        $chartKeluar = [];

        if ($mode === 'bulan') {
            $daysInMonth = $awal->daysInMonth;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($tahun, $bulan, $day);
                $chartLabels[] = (string) $day;
                $chartMasuk[] = (int) BibitMasuk::whereDate('tanggal_masuk', $date)->sum('jumlah');
                $chartKeluar[] = (int) BibitKeluar::whereDate('tanggal_keluar', $date)->sum('jumlah');
            }
        } else {
            $cursor = $awal->copy();
            while ($cursor->lte($akhir)) {
                $chartLabels[] = $cursor->format('d/m');
                $chartMasuk[] = (int) BibitMasuk::whereDate('tanggal_masuk', $cursor)->sum('jumlah');
                $chartKeluar[] = (int) BibitKeluar::whereDate('tanggal_keluar', $cursor)->sum('jumlah');
                $cursor->addDay();
            }
        }

        $riwayatMasukQuery = BibitMasuk::with('bibit')
            ->whereBetween('tanggal_masuk', [$awal, $akhir]);
        TableSearch::apply($riwayatMasukQuery, $request, ['sumber', 'bibit.nama_tanaman', 'bibit.nama_ilmiah']);
        $riwayatMasuk = $riwayatMasukQuery
            ->latest('tanggal_masuk')
            ->when(! $forPdf, fn ($q) => $q->limit(10))
            ->get();

        $riwayatKeluarQuery = BibitKeluar::with(['bibit', 'taman'])
            ->whereBetween('tanggal_keluar', [$awal, $akhir]);
        TableSearch::apply($riwayatKeluarQuery, $request, ['peruntukan', 'bibit.nama_tanaman', 'bibit.nama_ilmiah', 'taman.nama_taman']);
        $riwayatKeluar = $riwayatKeluarQuery
            ->latest('tanggal_keluar')
            ->when(! $forPdf, fn ($q) => $q->limit(10))
            ->get();

        return [
            'mode' => $mode,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dari' => $dari,
            'sampai' => $sampai,
            'labelPeriode' => $labelPeriode,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'mutasiBersih' => $mutasiBersih,
            'jumlahTransaksi' => $jumlahTransaksi,
            'mutasiPerBibit' => $mutasiPerBibit,
            'chartLabels' => $chartLabels,
            'chartMasuk' => $chartMasuk,
            'chartKeluar' => $chartKeluar,
            'riwayatMasuk' => $riwayatMasuk,
            'riwayatKeluar' => $riwayatKeluar,
            'daftarBulan' => $this->daftarBulan(),
            'daftarTahun' => range(now()->year, now()->year - 5),
        ];
    }

    /**
     * @return array{mode: string, bulan: int, tahun: int, dari: string, sampai: string, awal: \Carbon\Carbon, akhir: \Carbon\Carbon, labelPeriode: string}
     */
    private function resolvePeriode(Request $request): array
    {
        $mode = $request->input('mode', 'bulan');
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);
        $dari = $request->input('dari');
        $sampai = $request->input('sampai');

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

        return compact('mode', 'bulan', 'tahun', 'dari', 'sampai', 'awal', 'akhir', 'labelPeriode');
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
