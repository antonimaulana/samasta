<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bibit;
use App\Models\BibitMasuk;
use App\Models\BibitMasukBatch;
use App\Support\BibitStokBuktiFotoStorage;
use App\Support\OperasionalPelaksanaanTime;
use App\Support\TableSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BibitMasukController extends Controller
{
    public function __construct(
        private BibitStokBuktiFotoStorage $fotoStorage,
    ) {
        $this->authorizeResource(BibitMasukBatch::class, 'bibit_masuk');
    }

    public function index(Request $request): View
    {
        $query = BibitMasukBatch::query()
            ->with(['items:id,bibit_masuk_batch_id,jumlah,sisa_stok'])
            ->withCount('items')
            ->withSum('items', 'jumlah')
            ->latest('tanggal_masuk')
            ->latest('id');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->whereHas('items', fn ($items) => $items->whereHas('bibit', fn ($bibit) => $bibit
                    ->where('nama_tanaman', 'like', $like)
                    ->orWhere('nama_ilmiah', 'like', $like)
                    ->orWhere('jenis', 'like', $like)))
                    ->orWhereHas('items', fn ($items) => $items->where('sumber', 'like', $like));
            });
        }

        $batches = $query->paginate(10)->withQueryString();

        return view('admin.bibit_masuks.index', compact('batches'));
    }

    public function create(): View
    {
        $bibits = Bibit::orderBy('nama_tanaman')->get();

        return view('admin.bibit_masuks.create', compact('bibits'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBatchRequest($request, requireFotos: true);
        $items = $this->normalizeItems($validated['items']);
        $header = [
            'tanggal_masuk' => OperasionalPelaksanaanTime::normalizeInput($validated['tanggal_masuk']),
            'sumber' => $validated['sumber'],
            'status_siap_tanam' => $request->boolean('status_siap_tanam'),
        ];
        $lineMeta = [
            'sumber' => $validated['sumber'],
            'status_siap_tanam' => $request->boolean('status_siap_tanam'),
        ];

        DB::transaction(function () use ($request, $header, $items, $lineMeta) {
            $batch = BibitMasukBatch::create($header);

            foreach ($items as $item) {
                BibitMasuk::create([
                    'bibit_masuk_batch_id' => $batch->id,
                    'bibit_id' => $item['bibit_id'],
                    'jumlah' => $item['jumlah'],
                    'sisa_stok' => $item['jumlah'],
                    'sumber' => $lineMeta['sumber'],
                    'status_siap_tanam' => $lineMeta['status_siap_tanam'],
                ]);

                Bibit::lockForUpdate()
                    ->findOrFail($item['bibit_id'])
                    ->increment('stok_tersedia', $item['jumlah']);
            }

            $this->fotoStorage->storeMany(
                $batch,
                $request->file('fotos', []),
                'bibit-masuk',
            );
        });

        return redirect()
            ->route('admin.bibit-masuks.index')
            ->with('success', 'Stok masuk berhasil dicatat dan stok bibit telah diperbarui.');
    }

    public function show(BibitMasukBatch $bibitMasuk): View
    {
        $bibitMasuk->load(['items.bibit', 'buktiFotos']);

        return view('admin.bibit_masuks.show', [
            'batch' => $bibitMasuk,
        ]);
    }

    public function edit(BibitMasukBatch $bibitMasuk): View
    {
        $bibitMasuk->load(['items.bibit', 'buktiFotos']);
        $bibits = Bibit::query()
            ->where(function ($q) use ($bibitMasuk) {
                $q->where('stok_tersedia', '>=', 0)
                    ->orWhereIn('id', $bibitMasuk->items->pluck('bibit_id'));
            })
            ->orderBy('nama_tanaman')
            ->get();

        return view('admin.bibit_masuks.edit', [
            'batch' => $bibitMasuk,
            'bibits' => $bibits,
        ]);
    }

    public function update(Request $request, BibitMasukBatch $bibitMasuk): RedirectResponse
    {
        $validated = $this->validateBatchRequest($request, requireFotos: false, existing: $bibitMasuk);
        $items = $this->normalizeItems($validated['items']);
        $header = [
            'tanggal_masuk' => OperasionalPelaksanaanTime::normalizeInput($validated['tanggal_masuk']),
            'sumber' => $validated['sumber'],
            'status_siap_tanam' => $request->boolean('status_siap_tanam'),
        ];
        $lineMeta = [
            'sumber' => $validated['sumber'],
            'status_siap_tanam' => $request->boolean('status_siap_tanam'),
        ];

        DB::transaction(function () use ($request, $bibitMasuk, $header, $items, $lineMeta) {
            $bibitMasuk->load('items');

            foreach ($bibitMasuk->items as $line) {
                $terpakai = $line->jumlah - $line->sisa_stok;
                if ($terpakai > 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Tidak dapat mengubah daftar bibit karena sebagian batch sudah dikeluarkan.',
                    ]);
                }

                $bibit = Bibit::lockForUpdate()->findOrFail($line->bibit_id);
                $bibit->decrement('stok_tersedia', $line->sisa_stok);
            }

            $bibitMasuk->items()->delete();

            foreach ($items as $item) {
                BibitMasuk::create([
                    'bibit_masuk_batch_id' => $bibitMasuk->id,
                    'bibit_id' => $item['bibit_id'],
                    'jumlah' => $item['jumlah'],
                    'sisa_stok' => $item['jumlah'],
                    'sumber' => $lineMeta['sumber'],
                    'status_siap_tanam' => $lineMeta['status_siap_tanam'],
                ]);

                Bibit::lockForUpdate()
                    ->findOrFail($item['bibit_id'])
                    ->increment('stok_tersedia', $item['jumlah']);
            }

            $bibitMasuk->update($header);

            if ($request->hasFile('fotos')) {
                $this->fotoStorage->storeMany($bibitMasuk, $request->file('fotos', []), 'bibit-masuk');
            }
        });

        return redirect()
            ->route('admin.bibit-masuks.show', $bibitMasuk)
            ->with('success', 'Data stok masuk berhasil diperbarui.');
    }

    public function destroy(BibitMasukBatch $bibitMasuk): RedirectResponse
    {
        foreach ($bibitMasuk->items as $line) {
            if ($line->sisa_stok < $line->jumlah) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'delete' => 'Stok masuk tidak dapat dihapus karena sebagian batch sudah dikeluarkan.',
                    ]);
            }
        }

        DB::transaction(function () use ($bibitMasuk) {
            $bibitMasuk->load('items');

            foreach ($bibitMasuk->items as $line) {
                $bibit = Bibit::lockForUpdate()->findOrFail($line->bibit_id);

                if ($line->sisa_stok > 0) {
                    $bibit->decrement('stok_tersedia', $line->sisa_stok);
                }
            }

            $this->fotoStorage->deleteAll($bibitMasuk);
            $bibitMasuk->delete();
        });

        return redirect()
            ->back(fallback: route('admin.bibit-masuks.index'))
            ->with('success', 'Data stok masuk berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateBatchRequest(Request $request, bool $requireFotos, ?BibitMasukBatch $existing = null): array
    {
        $fotoRules = ['required', 'array', 'min:1'];
        if (! $requireFotos && $existing?->hasFotoBukti()) {
            $fotoRules = ['nullable', 'array'];
        }

        return $request->validate([
            'tanggal_masuk' => ['required', 'string'],
            'sumber' => ['required', Rule::in(Bibit::SUMBER)],
            'status_siap_tanam' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.bibit_id' => ['required', 'distinct', 'exists:bibits,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'fotos' => $fotoRules,
            'fotos.*' => ['image', 'max:4096'],
        ], [], [
            'tanggal_masuk' => 'tanggal masuk',
            'sumber' => 'sumber',
            'items' => 'daftar bibit',
            'items.*.bibit_id' => 'bibit',
            'items.*.jumlah' => 'jumlah masuk',
            'fotos' => 'foto bukti',
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return list<array{bibit_id: int, jumlah: int}>
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
                'jumlah' => (int) $item['jumlah'],
            ];
        }

        if ($normalized === []) {
            throw ValidationException::withMessages([
                'items' => 'Tambahkan minimal satu jenis bibit dengan jumlah masuk.',
            ]);
        }

        return $normalized;
    }
}
