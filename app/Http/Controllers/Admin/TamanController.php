<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTamanRequest;
use App\Http\Requests\Admin\UpdateTamanRequest;
use App\Models\Kelurahan;
use App\Models\PemeliharaanTaman;
use App\Models\Taman;
use App\Models\TamanImage;
use App\Support\OperationalAlertService;
use App\Support\OperatorWilayahScope;
use App\Support\TamanTableSort;
use App\Support\TableSearch;
use App\Support\KelurahanResolver;
use App\Support\TamanCsvImporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TamanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Taman::class, 'taman');
    }

    public function index(Request $request): View
    {
        $scope = app(OperatorWilayahScope::class);

        $query = TableSearch::apply(
            $scope->scopeTamans(Taman::with(['images', 'kelurahan.kecamatan']), $request->user()),
            $request,
            ['nama_taman', 'alamat', 'kategori', 'deskripsi', 'kontraktor', 'konsultan_perencana']
        )
            ->when($request->input('alert') === 'incomplete', fn ($q) => app(OperationalAlertService::class)->filterIncompleteTamans($q))
            ->when(
                in_array($request->input('status_data'), [Taman::STATUS_DATA_LENGKAP, Taman::STATUS_DATA_BELUM_LENGKAP], true),
                fn ($q) => $q->where('status_data', $request->input('status_data'))
            );

        $sortState = TamanTableSort::apply($query, $request);

        $tamans = $query->paginate(10)->withQueryString();

        return view('admin.tamans.index', compact('tamans', 'sortState'));
    }

    public function importForm(): View
    {
        $this->authorize('import', Taman::class);

        return view('admin.tamans.import');
    }

    public function resolveWilayah(Request $request): JsonResponse
    {
        $this->authorize('create', Taman::class);

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $kelurahan = KelurahanResolver::findByCoordinates(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
        );

        return response()->json([
            'resolved' => $kelurahan !== null,
            'kelurahan_id' => $kelurahan?->id,
            'label' => $kelurahan?->loadMissing('kecamatan')->labelWithKecamatan(),
            'message' => $kelurahan
                ? 'Wilayah terdeteksi dari koordinat.'
                : 'Wilayah tidak dapat dideteksi. Pilih kelurahan secara manual.',
        ]);
    }

    public function importTemplate(TamanCsvImporter $importer): StreamedResponse
    {
        $this->authorize('import', Taman::class);

        return response()->streamDownload(function () use ($importer) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fprintf($handle, 'sep=%s'.PHP_EOL, $importer->templateDelimiter());

            foreach ($importer->templateRows() as $row) {
                fputcsv($handle, $row, $importer->templateDelimiter());
            }

            fclose($handle);
        }, 'template-import-taman.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request, TamanCsvImporter $importer): RedirectResponse
    {
        $this->authorize('import', Taman::class);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:4096', 'mimes:csv,txt'],
        ], [
            'file.mimes' => 'File harus berformat CSV (.csv). Simpan ulang dari Excel sebagai CSV UTF-8.',
        ]);

        $path = $validated['file']->getRealPath();

        if (! is_string($path)) {
            return back()->withErrors(['file' => 'File CSV tidak valid.']);
        }

        $result = $importer->import($path);

        if ($result['imported'] === 0 && ($result['updated'] ?? 0) === 0 && $result['skipped'] === 0 && $result['errors'] !== []) {
            return back()
                ->with('import_result', $result)
                ->with('warning', $result['errors'][0]);
        }

        $processed = ($result['imported'] ?? 0) + ($result['updated'] ?? 0);

        if ($processed > 0) {
            $message = [];
            if (($result['imported'] ?? 0) > 0) {
                $message[] = number_format($result['imported']).' taman baru';
            }
            if (($result['updated'] ?? 0) > 0) {
                $message[] = number_format($result['updated']).' taman diperbarui';
            }

            return redirect()
                ->route('admin.tamans.index')
                ->with('success', implode(', ', $message).' berhasil diproses dari CSV.')
                ->with('import_result', $result);
        }

        return redirect()
            ->route('admin.tamans.import')
            ->with('import_result', $result)
            ->with('warning', 'Tidak ada taman yang berhasil diimport. Periksa format file dan detail error.');
    }

    public function create(): View
    {
        return view('admin.tamans.create', $this->formData());
    }

    public function store(StoreTamanRequest $request): RedirectResponse
    {
        $validated = $request->validatedTamanPayload();

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('tamans', 'public');
        }

        $taman = Taman::create($validated);

        $this->storeGalleryImages($taman, $request->file('fotos', []));
        $taman->refresh()->load('images', 'kelurahan');
        $taman->syncStatusData();

        return redirect()
            ->route('admin.tamans.index')
            ->with('success', 'Data taman berhasil ditambahkan.');
    }

    public function show(Taman $taman): View
    {
        $taman->load(['images', 'kelurahan.kecamatan']);

        $kinerjas = app(OperatorWilayahScope::class)->scopePemeliharaan(
            PemeliharaanTaman::query()->where('taman_id', $taman->id),
            auth()->user(),
        )
            ->orderByDesc('tanggal')
            ->paginate(10);

        return view('admin.tamans.show', compact('taman', 'kinerjas'));
    }

    public function edit(Taman $taman): View
    {
        $taman->load(['images', 'kelurahan.kecamatan']);

        return view('admin.tamans.edit', [
            'taman' => $taman,
            ...$this->formData($taman),
        ]);
    }

    public function update(UpdateTamanRequest $request, Taman $taman): RedirectResponse
    {
        $validated = $request->validatedTamanPayload();

        if ($request->hasFile('foto')) {
            if ($taman->foto) {
                Storage::disk('public')->delete($taman->foto);
            }

            $validated['foto'] = $request->file('foto')->store('tamans', 'public');
        }

        $taman->update($validated);

        $this->deleteGalleryImages($taman, $request->input('hapus_fotos', []));
        $this->storeGalleryImages($taman, $request->file('fotos', []));
        $taman->refresh()->load('images', 'kelurahan');
        $taman->syncStatusData();

        return redirect()
            ->route('admin.tamans.index')
            ->with('success', 'Data taman berhasil diperbarui.');
    }

    public function destroy(Taman $taman): RedirectResponse
    {
        $taman->load('images');

        if ($taman->foto) {
            Storage::disk('public')->delete($taman->foto);
        }

        foreach ($taman->images as $image) {
            Storage::disk('public')->delete($image->path_foto);
        }

        $taman->delete();

        return redirect()
            ->route('admin.tamans.index')
            ->with('success', 'Data taman berhasil dihapus.');
    }

    public function destroyImage(Taman $taman, TamanImage $image): RedirectResponse
    {
        $this->authorize('deleteImage', $taman);

        if ($image->taman_id !== $taman->id) {
            abort(404);
        }

        Storage::disk('public')->delete($image->path_foto);
        $image->delete();

        $taman->refresh()->load('images', 'kelurahan');
        $taman->syncStatusData();

        return redirect()
            ->route('admin.tamans.show', $taman)
            ->with('success', 'Foto galeri berhasil dihapus.');
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile|null>  $files
     */
    private function storeGalleryImages(Taman $taman, array $files): void
    {
        foreach ($files as $file) {
            if (! $file) {
                continue;
            }

            $path = $file->store('taman_galeri', 'public');

            $taman->images()->create([
                'path_foto' => $path,
            ]);
        }
    }

    /**
     * @param  array<int, int|string>  $imageIds
     */
    private function deleteGalleryImages(Taman $taman, array $imageIds): void
    {
        if ($imageIds === []) {
            return;
        }

        $images = $taman->images()->whereIn('id', $imageIds)->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->path_foto);
            $image->delete();
        }
    }

    /**
     * @return array{kelurahanOptions: list<array{value: int, label: string, search: string}>}
     */
    private function formData(?Taman $taman = null): array
    {
        $selectedKelurahan = old('kelurahan_id', $taman?->kelurahan_id);
        $selectedLabel = '';

        if ($selectedKelurahan) {
            $kelurahan = Kelurahan::with('kecamatan')->find($selectedKelurahan);
            $selectedLabel = $kelurahan?->labelWithKecamatan() ?? '';
        }

        $kelurahanOptions = Kelurahan::query()
            ->with('kecamatan')
            ->join('kecamatans', 'kecamatans.id', '=', 'kelurahans.kecamatan_id')
            ->orderBy('kecamatans.nama')
            ->orderBy('kelurahans.nama')
            ->select('kelurahans.*')
            ->get()
            ->map(fn (Kelurahan $kelurahan) => [
                'value' => $kelurahan->id,
                'label' => $kelurahan->labelWithKecamatan(),
                'search' => mb_strtolower($kelurahan->kecamatan->nama.' '.$kelurahan->nama),
            ])
            ->all();

        return [
            'kelurahanOptions' => $kelurahanOptions,
            'selectedKelurahanLabel' => $selectedLabel,
        ];
    }
}
