<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bibit;
use App\Models\BibitKeluar;
use App\Models\BibitMasuk;
use App\Support\BibitCsvImporter;
use App\Support\TableSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BibitController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Bibit::class, 'bibit');
    }

    public function index(Request $request): View
    {
        $bibits = TableSearch::apply(
            Bibit::with(['masuks' => fn ($query) => $query->where('sisa_stok', '>', 0)])->latest(),
            $request,
            ['nama_tanaman', 'nama_ilmiah', 'jenis']
        )
            ->when($request->input('alert') === 'low_stock', fn ($q) => $q->where(
                'stok_tersedia',
                '<',
                config('alerts.bibit.minimum_stock')
            ))
            ->paginate(10)->withQueryString();

        $recentMasuks = BibitMasuk::query()
            ->with('bibit')
            ->latest('tanggal_masuk')
            ->latest()
            ->limit(5)
            ->get();

        $recentKeluars = BibitKeluar::query()
            ->with(['bibit', 'taman'])
            ->latest('tanggal_keluar')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.bibits.index', compact('bibits', 'recentMasuks', 'recentKeluars'));
    }

    public function importForm(): View
    {
        $this->authorize('import', Bibit::class);

        return view('admin.bibits.import');
    }

    public function importTemplate(BibitCsvImporter $importer): StreamedResponse
    {
        $this->authorize('import', Bibit::class);

        return response()->streamDownload(function () use ($importer) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fprintf($handle, 'sep=%s'.PHP_EOL, $importer->templateDelimiter());

            foreach ($importer->templateRows() as $row) {
                fputcsv($handle, $row, $importer->templateDelimiter());
            }

            fclose($handle);
        }, 'template-import-bibit.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request, BibitCsvImporter $importer): RedirectResponse
    {
        $this->authorize('import', Bibit::class);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:4096', 'mimes:csv,txt'],
        ], [], [
            'file' => 'file CSV',
        ]);

        $path = $validated['file']->getRealPath();

        if (! is_string($path) || $path === '') {
            return back()->withErrors(['file' => 'File CSV tidak dapat dibaca.']);
        }

        $result = $importer->import($path);

        if ($result['imported'] === 0 && $result['skipped'] === 0 && $result['errors'] !== []) {
            return back()->withErrors(['file' => $result['errors'][0]]);
        }

        if ($result['imported'] > 0) {
            return redirect()
                ->route('admin.bibits.index')
                ->with('success', number_format($result['imported']).' baris bibit berhasil diimport (data bibit + stok masuk awal).')
                ->with('import_result', $result);
        }

        return redirect()
            ->route('admin.bibits.import')
            ->with('import_result', $result)
            ->with('warning', 'Tidak ada bibit yang berhasil diimport. Periksa format file dan detail error.');
    }

    public function create(): View
    {
        return view('admin.bibits.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Bibit::create($this->validateBibit($request) + ['stok_tersedia' => 0]);

        return redirect()
            ->route('admin.bibits.index')
            ->with('success', 'Data bibit berhasil ditambahkan. Tambahkan stok melalui tombol Stok Masuk.');
    }

    public function show(Bibit $bibit): View
    {
        $bibit->load([
            'masuks' => fn ($query) => $query->latest('tanggal_masuk')->latest(),
            'keluars' => fn ($query) => $query->with('taman')->latest('tanggal_keluar')->latest(),
        ]);

        $totalMasuk = (int) $bibit->masuks->sum('jumlah');
        $totalKeluar = (int) $bibit->keluars->sum('jumlah');
        $stokSumber = $bibit->stokPerSumber();
        $siap = $bibit->stokSiapTanam();
        $belum = $bibit->stokBelumSiap();

        return view('admin.bibits.show', compact(
            'bibit',
            'totalMasuk',
            'totalKeluar',
            'stokSumber',
            'siap',
            'belum',
        ));
    }

    public function edit(Bibit $bibit): View
    {
        return view('admin.bibits.edit', compact('bibit'));
    }

    public function update(Request $request, Bibit $bibit): RedirectResponse
    {
        $bibit->update($this->validateBibit($request));

        return redirect()
            ->route('admin.bibits.index')
            ->with('success', 'Data bibit berhasil diperbarui.');
    }

    public function destroy(Bibit $bibit): RedirectResponse
    {
        $bibit->delete();

        return redirect()
            ->route('admin.bibits.index')
            ->with('success', 'Data bibit berhasil dihapus.');
    }

    private function validateBibit(Request $request): array
    {
        return $request->validate([
            'nama_tanaman' => ['required', 'string', 'max:255'],
            'nama_ilmiah' => ['nullable', 'string', 'max:150'],
            'jenis' => ['required', Rule::in(Bibit::JENIS)],
        ], [], [
            'nama_tanaman' => 'nama tanaman',
            'nama_ilmiah' => 'nama ilmiah',
            'jenis' => 'jenis',
        ]);
    }
}
