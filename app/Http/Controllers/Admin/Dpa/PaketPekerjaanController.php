<?php

namespace App\Http\Controllers\Admin\Dpa;

use App\Http\Controllers\Controller;
use App\Models\Dpa;
use App\Models\DpaPaketDokumen;
use App\Models\DpaPaketItemBelanja;
use App\Models\DpaPaketOutput;
use App\Models\DpaPaketPekerjaan;
use App\Models\DpaPaketProgres;
use App\Models\DpaPenyedia;
use App\Support\DpaDocumentFields;
use App\Support\DpaDocumentGenerator;
use App\Support\DpaMonitoring;
use App\Support\PaketPekerjaanCsvImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaketPekerjaanController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.manage');
    }

    public function create(Dpa $dpa): View
    {
        return view('admin.dpa.paket_pekerjaans.create', [
            'dpa' => $dpa->load('tahunAnggaran'),
            'penyedias' => DpaPenyedia::query()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request, Dpa $dpa): RedirectResponse
    {
        $validated = $this->validatePaket($request);
        $validated['dpa_id'] = $dpa->id;
        $validated['tahap'] = DpaMonitoring::TAHAP_PENGADAAN;

        $paketPekerjaan = DpaPaketPekerjaan::create($validated);

        return redirect()
            ->route('admin.dpa.paket-pekerjaans.show', $paketPekerjaan)
            ->with('success', 'Paket pekerjaan berhasil ditambahkan.');
    }

    public function show(Request $request, DpaPaketPekerjaan $paketPekerjaan): View
    {
        $paketPekerjaan->load(['dpa.tahunAnggaran', 'penyedia']);

        $activeTahap = $request->input('tahap', $paketPekerjaan->tahap);

        if (! in_array($activeTahap, DpaMonitoring::TAHAP, true)) {
            $activeTahap = $paketPekerjaan->tahap;
        }

        $dokumens = $paketPekerjaan->dokumens()
            ->where('tahap', $activeTahap)
            ->get()
            ->keyBy('kode_dokumen');

        $dokumenDefinitions = DpaMonitoring::dokumenForTahap($activeTahap);

        $hpsItems = $activeTahap === DpaMonitoring::TAHAP_PENGADAAN
            ? $paketPekerjaan->hpsItems()->get()
            : collect();

        $progresItems = $activeTahap === DpaMonitoring::TAHAP_KONTRAK
            ? $paketPekerjaan->progresKontraks()->get()
            : collect();

        $spkItems = $activeTahap === DpaMonitoring::TAHAP_KONTRAK
            ? $paketPekerjaan->spkItems()->get()
            : collect();

        $outputItems = $activeTahap === DpaMonitoring::TAHAP_SELESAI
            ? $paketPekerjaan->outputs()->get()
            : collect();

        return view('admin.dpa.paket_pekerjaans.show', [
            'paketPekerjaan' => $paketPekerjaan,
            'activeTahap' => $activeTahap,
            'tahapOptions' => DpaMonitoring::TAHAP,
            'dokumens' => $dokumens,
            'dokumenDefinitions' => $dokumenDefinitions,
            'hpsItems' => $hpsItems,
            'progresItems' => $progresItems,
            'spkItems' => $spkItems,
            'outputItems' => $outputItems,
        ]);
    }

    public function edit(DpaPaketPekerjaan $paketPekerjaan): View
    {
        return view('admin.dpa.paket_pekerjaans.edit', [
            'paketPekerjaan' => $paketPekerjaan->load('dpa.tahunAnggaran'),
            'penyedias' => DpaPenyedia::query()->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, DpaPaketPekerjaan $paketPekerjaan): RedirectResponse
    {
        $paketPekerjaan->update($this->validatePaket($request));

        return redirect()
            ->route('admin.dpa.paket-pekerjaans.show', $paketPekerjaan)
            ->with('success', 'Paket pekerjaan berhasil diperbarui.');
    }

    public function destroy(DpaPaketPekerjaan $paketPekerjaan): RedirectResponse
    {
        $dpaId = $paketPekerjaan->dpa_id;

        $paketPekerjaan->load(['dokumens', 'progresKontraks', 'outputs']);

        foreach ($paketPekerjaan->dokumens as $dokumen) {
            $this->deletePublicFile($dokumen->file_path);
        }

        foreach ($paketPekerjaan->progresKontraks as $progres) {
            $this->deletePublicFile($progres->dokumentasi_path);
        }

        foreach ($paketPekerjaan->outputs as $output) {
            $this->deletePublicFile($output->file_path);
        }

        $paketPekerjaan->delete();

        return redirect()
            ->route('admin.dpa.dpas.show', $dpaId)
            ->with('success', 'Paket pekerjaan berhasil dihapus.');
    }

    public function importForm(Dpa $dpa): View
    {
        return view('admin.dpa.paket_pekerjaans.import', [
            'dpa' => $dpa->load('tahunAnggaran'),
        ]);
    }

    public function importTemplate(PaketPekerjaanCsvImporter $importer): StreamedResponse
    {
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
        }, 'template-import-paket-pekerjaan.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importStore(Request $request, Dpa $dpa, PaketPekerjaanCsvImporter $importer): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:4096', 'mimes:csv,txt'],
        ], [
            'file.mimes' => 'File harus berformat CSV (.csv). Simpan ulang dari Excel sebagai CSV UTF-8.',
        ]);

        $path = $validated['file']->getRealPath();

        if (! is_string($path)) {
            return back()->withErrors(['file' => 'File CSV tidak valid.']);
        }

        $result = $importer->import($path, $dpa->id);

        if ($result['imported'] === 0 && $result['skipped'] === 0 && $result['errors'] !== []) {
            return back()->withErrors(['file' => $result['errors'][0]]);
        }

        if ($result['imported'] > 0) {
            return redirect()
                ->route('admin.dpa.dpas.show', $dpa)
                ->with('success', number_format($result['imported']).' paket pekerjaan berhasil diimport.')
                ->with('import_result', $result);
        }

        return redirect()
            ->route('admin.dpa.paket-pekerjaans.import', $dpa)
            ->with('import_result', $result)
            ->with('warning', 'Tidak ada paket pekerjaan yang berhasil diimport. Periksa format file dan detail error.');
    }

    public function updateTahap(Request $request, DpaPaketPekerjaan $paketPekerjaan): RedirectResponse
    {
        $validated = $request->validate([
            'tahap' => ['required', 'string', Rule::in(DpaMonitoring::TAHAP)],
        ]);

        $paketPekerjaan->update(['tahap' => $validated['tahap']]);

        return redirect()
            ->route('admin.dpa.paket-pekerjaans.show', [
                'paket_pekerjaan' => $paketPekerjaan,
                'tahap' => $validated['tahap'],
            ])
            ->with('success', 'Tahap paket pekerjaan berhasil diperbarui.');
    }

    public function generateDokumen(Request $request, DpaPaketPekerjaan $paketPekerjaan, DpaDocumentGenerator $generator): RedirectResponse
    {
        $kode = (string) $request->input('kode_dokumen', '');
        $mergedFields = DpaDocumentFields::mergeDefaults(
            $paketPekerjaan,
            $kode,
            $request->input('fields', []),
        );
        $request->merge(['fields' => $mergedFields]);

        $validated = $request->validate(array_merge([
            'tahap' => ['required', 'string', Rule::in(DpaMonitoring::TAHAP)],
            'kode_dokumen' => ['required', 'string'],
            'fields' => ['required', 'array'],
        ], DpaDocumentFields::validationRules($kode)), [], DpaDocumentFields::validationAttributes($kode));

        $allowedKodes = collect(DpaMonitoring::dokumenForTahap($validated['tahap']))->pluck('kode')->all();

        if (! in_array($validated['kode_dokumen'], $allowedKodes, true)) {
            throw ValidationException::withMessages([
                'kode_dokumen' => 'Kode dokumen tidak valid untuk tahap ini.',
            ]);
        }

        if (DpaDocumentFields::usesItemTable($validated['kode_dokumen'])) {
            $itemCount = $validated['kode_dokumen'] === 'hps'
                ? $paketPekerjaan->hpsItems()->count()
                : $paketPekerjaan->spkItems()->count();

            if ($itemCount === 0) {
                throw ValidationException::withMessages([
                    'fields' => 'Tambahkan item rinci '.strtoupper($validated['kode_dokumen']).' terlebih dahulu.',
                ]);
            }
        }

        $existing = $paketPekerjaan->dokumens()
            ->where('kode_dokumen', $validated['kode_dokumen'])
            ->first();

        if ($existing !== null) {
            $this->deletePublicFile($existing->file_path);
        }

        $filePath = $generator->generate(
            $paketPekerjaan,
            $validated['tahap'],
            $validated['kode_dokumen'],
            $validated['fields'],
        );

        DpaPaketDokumen::updateOrCreate(
            [
                'dpa_paket_pekerjaan_id' => $paketPekerjaan->id,
                'kode_dokumen' => $validated['kode_dokumen'],
            ],
            [
                'tahap' => $validated['tahap'],
                'file_path' => $filePath,
                'input_data' => $validated['fields'],
                'generated_at' => now(),
            ],
        );

        return redirect()
            ->route('admin.dpa.paket-pekerjaans.show', [
                'paket_pekerjaan' => $paketPekerjaan,
                'tahap' => $validated['tahap'],
            ])
            ->with('success', 'Dokumen PDF berhasil digenerate.');
    }

    public function previewDokumen(Request $request, DpaPaketPekerjaan $paketPekerjaan, DpaDocumentGenerator $generator): Response
    {
        $kode = (string) $request->query('kode_dokumen');

        $dokumen = $paketPekerjaan->dokumens()->where('kode_dokumen', $kode)->first();

        if ($dokumen === null || ! is_array($dokumen->input_data)) {
            abort(404);
        }

        $binary = $generator->renderPreview($paketPekerjaan, $kode, $dokumen->input_data);
        $filename = $kode.'-'.$paketPekerjaan->id.'.pdf';

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function storeDokumen(Request $request, DpaPaketPekerjaan $paketPekerjaan): RedirectResponse
    {
        $validated = $request->validate([
            'tahap' => ['required', 'string', Rule::in(DpaMonitoring::TAHAP)],
            'kode_dokumen' => ['required', 'string'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $allowedKodes = collect(DpaMonitoring::dokumenForTahap($validated['tahap']))->pluck('kode')->all();

        if (! in_array($validated['kode_dokumen'], $allowedKodes, true)) {
            throw ValidationException::withMessages([
                'kode_dokumen' => 'Kode dokumen tidak valid untuk tahap ini.',
            ]);
        }

        $existing = $paketPekerjaan->dokumens()
            ->where('kode_dokumen', $validated['kode_dokumen'])
            ->first();

        if ($existing !== null) {
            $this->deletePublicFile($existing->file_path);
        }

        $filePath = $this->storePublicFile($request->file('file'), 'dpa-documents');

        DpaPaketDokumen::updateOrCreate(
            [
                'dpa_paket_pekerjaan_id' => $paketPekerjaan->id,
                'kode_dokumen' => $validated['kode_dokumen'],
            ],
            [
                'tahap' => $validated['tahap'],
                'file_path' => $filePath,
                'generated_at' => now(),
            ],
        );

        return redirect()
            ->route('admin.dpa.paket-pekerjaans.show', [
                'paket_pekerjaan' => $paketPekerjaan,
                'tahap' => $validated['tahap'],
            ])
            ->with('success', 'Dokumen berhasil disimpan.');
    }

    public function storeHpsItem(Request $request, DpaPaketPekerjaan $paketPekerjaan): RedirectResponse
    {
        $validated = $request->validate([
            'uraian' => ['required', 'string', 'max:255'],
            'volume' => ['required', 'numeric', 'min:0'],
            'satuan' => ['nullable', 'string', 'max:50'],
            'harga_satuan' => ['required', 'integer', 'min:0'],
        ]);

        $nextUrutan = (int) $paketPekerjaan->hpsItems()->max('urutan') + 1;
        $jumlah = (int) round((float) $validated['volume'] * (int) $validated['harga_satuan']);

        DpaPaketItemBelanja::create([
            'dpa_paket_pekerjaan_id' => $paketPekerjaan->id,
            'jenis_dokumen' => 'hps',
            'urutan' => $nextUrutan,
            'uraian' => $validated['uraian'],
            'volume' => $validated['volume'],
            'satuan' => $validated['satuan'] ?? null,
            'harga_satuan' => $validated['harga_satuan'],
            'jumlah' => $jumlah,
        ]);

        return redirect()
            ->route('admin.dpa.paket-pekerjaans.show', [
                'paket_pekerjaan' => $paketPekerjaan,
                'tahap' => DpaMonitoring::TAHAP_PENGADAAN,
            ])
            ->with('success', 'Item HPS berhasil ditambahkan.');
    }

    public function destroyHpsItem(DpaPaketPekerjaan $paketPekerjaan, DpaPaketItemBelanja $itemBelanja): RedirectResponse
    {
        if ($itemBelanja->dpa_paket_pekerjaan_id !== $paketPekerjaan->id || $itemBelanja->jenis_dokumen !== 'hps') {
            abort(404);
        }

        $itemBelanja->delete();

        return redirect()
            ->route('admin.dpa.paket-pekerjaans.show', [
                'paket_pekerjaan' => $paketPekerjaan,
                'tahap' => DpaMonitoring::TAHAP_PENGADAAN,
            ])
            ->with('success', 'Item HPS berhasil dihapus.');
    }

    public function storeSpkItem(Request $request, DpaPaketPekerjaan $paketPekerjaan): RedirectResponse
    {
        $validated = $request->validate([
            'uraian' => ['required', 'string', 'max:255'],
            'volume' => ['required', 'numeric', 'min:0'],
            'satuan' => ['nullable', 'string', 'max:50'],
            'harga_satuan' => ['required', 'integer', 'min:0'],
        ]);

        $nextUrutan = (int) $paketPekerjaan->spkItems()->max('urutan') + 1;
        $jumlah = (int) round((float) $validated['volume'] * (int) $validated['harga_satuan']);

        DpaPaketItemBelanja::create([
            'dpa_paket_pekerjaan_id' => $paketPekerjaan->id,
            'jenis_dokumen' => 'spk',
            'urutan' => $nextUrutan,
            'uraian' => $validated['uraian'],
            'volume' => $validated['volume'],
            'satuan' => $validated['satuan'] ?? null,
            'harga_satuan' => $validated['harga_satuan'],
            'jumlah' => $jumlah,
        ]);

        return redirect()
            ->route('admin.dpa.paket-pekerjaans.show', [
                'paket_pekerjaan' => $paketPekerjaan,
                'tahap' => DpaMonitoring::TAHAP_KONTRAK,
            ])
            ->with('success', 'Item SPK berhasil ditambahkan.');
    }

    public function destroySpkItem(DpaPaketPekerjaan $paketPekerjaan, DpaPaketItemBelanja $itemBelanja): RedirectResponse
    {
        if ($itemBelanja->dpa_paket_pekerjaan_id !== $paketPekerjaan->id || $itemBelanja->jenis_dokumen !== 'spk') {
            abort(404);
        }

        $itemBelanja->delete();

        return redirect()
            ->route('admin.dpa.paket-pekerjaans.show', [
                'paket_pekerjaan' => $paketPekerjaan,
                'tahap' => DpaMonitoring::TAHAP_KONTRAK,
            ])
            ->with('success', 'Item SPK berhasil dihapus.');
    }

    public function storeProgres(Request $request, DpaPaketPekerjaan $paketPekerjaan): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'persentase' => ['required', 'integer', 'min:0', 'max:100'],
            'keterangan' => ['nullable', 'string'],
            'dokumentasi' => ['nullable', 'file', 'max:5120'],
        ]);

        $dokumentasiPath = null;

        if ($request->hasFile('dokumentasi')) {
            $dokumentasiPath = $this->storePublicFile($request->file('dokumentasi'), 'dpa-documents');
        }

        DpaPaketProgres::create([
            'dpa_paket_pekerjaan_id' => $paketPekerjaan->id,
            'tanggal' => $validated['tanggal'],
            'persentase' => $validated['persentase'],
            'keterangan' => $validated['keterangan'] ?? null,
            'dokumentasi_path' => $dokumentasiPath,
        ]);

        return redirect()
            ->route('admin.dpa.paket-pekerjaans.show', [
                'paket_pekerjaan' => $paketPekerjaan,
                'tahap' => DpaMonitoring::TAHAP_KONTRAK,
            ])
            ->with('success', 'Progres kontrak berhasil ditambahkan.');
    }

    public function storeOutput(Request $request, DpaPaketPekerjaan $paketPekerjaan): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        $filePath = null;

        if ($request->hasFile('file')) {
            $filePath = $this->storePublicFile($request->file('file'), 'dpa-documents');
        }

        DpaPaketOutput::create([
            'dpa_paket_pekerjaan_id' => $paketPekerjaan->id,
            'judul' => $validated['judul'],
            'keterangan' => $validated['keterangan'] ?? null,
            'file_path' => $filePath,
        ]);

        return redirect()
            ->route('admin.dpa.paket-pekerjaans.show', [
                'paket_pekerjaan' => $paketPekerjaan,
                'tahap' => DpaMonitoring::TAHAP_SELESAI,
            ])
            ->with('success', 'Output pekerjaan berhasil ditambahkan.');
    }

    private function validatePaket(Request $request): array
    {
        $validated = $request->validate([
            'dpa_penyedia_id' => ['nullable', 'exists:dpa_penyedias,id'],
            'nomor_rekening' => ['nullable', 'string', 'max:100'],
            'nama_rekening' => ['nullable', 'string', 'max:255'],
            'nama_paket' => ['required', 'string', 'max:255'],
            'pagu_anggaran' => ['required', 'integer', 'min:0'],
            'rincian_item_belanja' => ['nullable', 'string'],
            'kode_rup' => ['nullable', 'string', 'max:100'],
            'jenis_pengadaan' => ['nullable', 'string', 'max:100'],
            'metode_pemilihan' => ['nullable', 'string', 'max:100'],
            'anggaran_kas' => ['nullable', 'integer', 'min:0'],
            'masa_pelaksanaan' => ['nullable', 'string', 'max:255'],
        ]);

        if (! array_key_exists('dpa_penyedia_id', $validated) || blank($validated['dpa_penyedia_id'])) {
            $validated['dpa_penyedia_id'] = null;
        }

        return $validated;
    }

    private function storePublicFile(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'public');

        return 'storage/'.$path;
    }

    private function deletePublicFile(?string $filePath): void
    {
        if (blank($filePath) || ! str_starts_with($filePath, 'storage/')) {
            return;
        }

        Storage::disk('public')->delete(substr($filePath, strlen('storage/')));
    }
}
