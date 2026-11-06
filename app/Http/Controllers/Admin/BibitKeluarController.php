<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bibit;
use App\Models\BibitKeluar;
use App\Models\BibitKeluarBatch;
use App\Models\PemeliharaanTaman;
use App\Models\Taman;
use App\Support\BibitKeluarStock;
use App\Support\BibitStokBuktiFotoStorage;
use App\Support\OperasionalPelaksanaanTime;
use App\Support\TableSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BibitKeluarController extends Controller
{
    public function __construct(
        private BibitKeluarStock $stock,
        private BibitStokBuktiFotoStorage $fotoStorage,
    ) {
        $this->authorizeResource(BibitKeluarBatch::class, 'bibit_keluar');
    }

    public function index(Request $request): View
    {
        $query = BibitKeluarBatch::query()
            ->with(['taman'])
            ->withCount('items')
            ->withSum('items', 'jumlah')
            ->latest('tanggal_keluar')
            ->latest('id');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($request, $like) {
                TableSearch::apply($q, $request, ['peruntukan', 'lokasi', 'taman.nama_taman']);
                $q->orWhereHas('items', fn ($items) => $items->whereHas('bibit', fn ($bibit) => $bibit
                    ->where('nama_tanaman', 'like', $like)
                    ->orWhere('nama_ilmiah', 'like', $like)
                    ->orWhere('jenis', 'like', $like)));
            });
        }

        $batches = $query->paginate(10)->withQueryString();

        return view('admin.bibit_keluars.index', compact('batches'));
    }

    public function create(): View
    {
        $bibits = Bibit::query()
            ->where('stok_tersedia', '>', 0)
            ->with(['masuks' => fn ($q) => $q->where('sisa_stok', '>', 0)])
            ->orderBy('nama_tanaman')
            ->get();
        $tamans = Taman::orderBy('nama_taman')->get();

        return view('admin.bibit_keluars.create', compact('bibits', 'tamans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBatchRequest($request, requireFotos: true);
        $items = $this->normalizeItems($validated['items']);
        $header = $this->normalizeLokasiFields($request, $validated);
        unset($header['items'], $header['lokasi_luar'], $header['fotos'], $header['dokumen_pendukung']);
        $header['tanggal_keluar'] = OperasionalPelaksanaanTime::normalizeInput($validated['tanggal_keluar']);

        if ($request->hasFile('dokumen_pendukung')) {
            $header['dokumen_pendukung'] = $request->file('dokumen_pendukung')->store('bibit-keluar-dokumen', 'public');
        }

        DB::transaction(function () use ($request, $header, $items) {
            $this->stock->assertStokCukup($items);

            $batch = BibitKeluarBatch::create($header);

            $this->fotoStorage->storeMany($batch, $request->file('fotos', []), 'bibit-keluar');

            foreach ($items as $item) {
                $bibit = Bibit::lockForUpdate()->findOrFail($item['bibit_id']);
                $this->stock->kurangiStokMasuk($bibit, $item['jumlah'], $item['sumber']);
                $bibit->decrement('stok_tersedia', $item['jumlah']);

                $batch->items()->create([
                    'bibit_id' => $item['bibit_id'],
                    'jumlah' => $item['jumlah'],
                    'sumber' => $item['sumber'],
                ]);
            }
        });

        return redirect()
            ->route('admin.bibit-keluars.index')
            ->with('success', 'Stok keluar berhasil dicatat dan stok bibit telah diperbarui.');
    }

    public function show(BibitKeluarBatch $bibitKeluar): View
    {
        $bibitKeluar->load(['items.bibit', 'taman', 'buktiFotos']);

        return view('admin.bibit_keluars.show', [
            'batch' => $bibitKeluar,
        ]);
    }

    public function edit(BibitKeluarBatch $bibitKeluar): View
    {
        $bibitKeluar->load(['items.bibit', 'buktiFotos']);
        $bibits = Bibit::query()
            ->where(function ($q) use ($bibitKeluar) {
                $q->where('stok_tersedia', '>', 0)
                    ->orWhereIn('id', $bibitKeluar->items->pluck('bibit_id'));
            })
            ->with(['masuks' => fn ($q) => $q->where('sisa_stok', '>', 0)])
            ->orderBy('nama_tanaman')
            ->get();
        $tamans = Taman::orderBy('nama_taman')->get();

        $editStokBonus = [];
        foreach ($bibitKeluar->items as $line) {
            $sumber = $line->sumber ?? '';
            $editStokBonus[$line->bibit_id][$sumber] = ($editStokBonus[$line->bibit_id][$sumber] ?? 0) + $line->jumlah;
        }

        return view('admin.bibit_keluars.edit', [
            'batch' => $bibitKeluar,
            'bibits' => $bibits,
            'tamans' => $tamans,
            'editStokBonus' => $editStokBonus,
        ]);
    }

    public function update(Request $request, BibitKeluarBatch $bibitKeluar): RedirectResponse
    {
        $validated = $this->validateBatchRequest($request, requireFotos: false, existing: $bibitKeluar);
        $items = $this->normalizeItems($validated['items']);
        $header = $this->normalizeLokasiFields($request, $validated);
        unset($header['items'], $header['lokasi_luar'], $header['fotos'], $header['dokumen_pendukung']);

        if ($request->hasFile('dokumen_pendukung')) {
            if ($bibitKeluar->dokumen_pendukung) {
                Storage::disk('public')->delete($bibitKeluar->dokumen_pendukung);
            }

            $header['dokumen_pendukung'] = $request->file('dokumen_pendukung')->store('bibit-keluar-dokumen', 'public');
        }

        $header['tanggal_keluar'] = OperasionalPelaksanaanTime::normalizeInput($validated['tanggal_keluar']);

        DB::transaction(function () use ($request, $bibitKeluar, $header, $items) {
            $bibitKeluar->load('items');

            $bonus = [];
            foreach ($bibitKeluar->items as $line) {
                $key = $this->stock->lineKey($line->bibit_id, $line->sumber);
                $bonus[$key] = ($bonus[$key] ?? 0) + $line->jumlah;
            }

            $this->stock->assertStokCukup($items, $bonus);

            foreach ($bibitKeluar->items as $line) {
                $bibit = Bibit::lockForUpdate()->findOrFail($line->bibit_id);
                $this->stock->kembalikanStokMasuk($bibit, $line->jumlah, $line->sumber);
                $bibit->increment('stok_tersedia', $line->jumlah);
            }

            $bibitKeluar->items()->delete();

            foreach ($items as $item) {
                $bibit = Bibit::lockForUpdate()->findOrFail($item['bibit_id']);
                $this->stock->kurangiStokMasuk($bibit, $item['jumlah'], $item['sumber']);
                $bibit->decrement('stok_tersedia', $item['jumlah']);

                $bibitKeluar->items()->create([
                    'bibit_id' => $item['bibit_id'],
                    'jumlah' => $item['jumlah'],
                    'sumber' => $item['sumber'],
                ]);
            }

            $bibitKeluar->update($header);

            if ($request->hasFile('fotos')) {
                $this->fotoStorage->storeMany($bibitKeluar, $request->file('fotos', []), 'bibit-keluar');
            }
        });

        return redirect()
            ->route('admin.bibit-keluars.show', $bibitKeluar)
            ->with('success', 'Data stok keluar berhasil diperbarui.');
    }

    public function destroy(BibitKeluarBatch $bibitKeluar): RedirectResponse
    {
        DB::transaction(function () use ($bibitKeluar) {
            $bibitKeluar->load('items');

            foreach ($bibitKeluar->items as $line) {
                $bibit = Bibit::lockForUpdate()->findOrFail($line->bibit_id);
                $this->stock->kembalikanStokMasuk($bibit, $line->jumlah, $line->sumber);
                $bibit->increment('stok_tersedia', $line->jumlah);
            }

            if ($bibitKeluar->dokumen_pendukung) {
                Storage::disk('public')->delete($bibitKeluar->dokumen_pendukung);
            }

            $this->fotoStorage->deleteAll($bibitKeluar);

            $bibitKeluar->delete();
        });

        return redirect()
            ->back(fallback: route('admin.bibit-keluars.index'))
            ->with('success', 'Data stok keluar berhasil dihapus dan stok bibit telah dikembalikan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateBatchRequest(Request $request, bool $requireFotos, ?BibitKeluarBatch $existing = null): array
    {
        $fotoRules = ['required', 'array', 'min:1'];
        if (! $requireFotos && $existing?->hasFotoBukti()) {
            $fotoRules = ['nullable', 'array'];
        }

        $peruntukan = (string) $request->input('peruntukan');
        $dokumenRules = $peruntukan === BibitKeluarBatch::PERUNTUKAN_PERMINTAAN
            ? ($requireFotos || ! $existing?->dokumen_pendukung ? ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'] : ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'])
            : ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'];

        $rules = array_merge([
            'tanggal_keluar' => ['required', 'string'],
            'peruntukan' => ['required', Rule::in(BibitKeluarBatch::PERUNTUKAN)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.bibit_id' => ['required', 'exists:bibits,id'],
            'items.*.sumber' => ['required', Rule::in(Bibit::SUMBER)],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'fotos' => $fotoRules,
            'fotos.*' => ['image', 'max:4096'],
            'dokumen_pendukung' => $dokumenRules,
        ], $this->lokasiValidationRules($request));

        return $request->validate($rules, [], [
            'tanggal_keluar' => 'tanggal keluar',
            'peruntukan' => 'peruntukan',
            'taman_id' => 'lokasi',
            'lokasi' => 'lokasi',
            'fotos' => 'foto bukti',
            'dokumen_pendukung' => 'dokumen pendukung',
            'items' => 'daftar bibit',
            'items.*.bibit_id' => 'bibit',
            'items.*.sumber' => 'sumber stok',
            'items.*.jumlah' => 'jumlah keluar',
        ]);
    }

    /**
     * @param  array<int, array{bibit_id?: mixed, jumlah?: mixed}>  $items
     * @return list<array{bibit_id: int, jumlah: int, sumber: string}>
     */
    private function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (! filled($item['bibit_id'] ?? null)) {
                continue;
            }

            $normalized[] = [
                'bibit_id' => (int) $item['bibit_id'],
                'sumber' => (string) $item['sumber'],
                'jumlah' => (int) $item['jumlah'],
            ];
        }

        if ($normalized === []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'items' => 'Tambahkan minimal satu jenis bibit dengan jumlah keluar.',
            ]);
        }

        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    private function lokasiValidationRules(Request $request): array
    {
        $isLokasiLuar = $request->boolean('lokasi_luar');

        $rules = [
            'lokasi_luar' => ['nullable', 'boolean'],
        ];

        if ($isLokasiLuar) {
            $rules['lokasi'] = ['required', 'string', 'max:255'];
        } else {
            $rules['taman_id'] = ['required', 'exists:tamans,id'];
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizeLokasiFields(Request $request, array $validated): array
    {
        if ($request->boolean('lokasi_luar')) {
            $validated['taman_id'] = null;
            $validated['lokasi'] = trim((string) ($validated['lokasi'] ?? ''));
        } else {
            $taman = Taman::query()->findOrFail($validated['taman_id']);
            $validated['lokasi'] = PemeliharaanTaman::lokasiLabelFromTaman($taman);
        }

        return $validated;
    }
}
