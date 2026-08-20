<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bibit;
use App\Models\BibitMasuk;
use App\Support\TableSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BibitMasukController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(BibitMasuk::class, 'bibit_masuk');
    }

    public function index(Request $request): View
    {
        $bibitMasuks = TableSearch::apply(
            BibitMasuk::with('bibit')->latest('tanggal_masuk')->latest(),
            $request,
            ['sumber', 'bibit.nama_tanaman', 'bibit.nama_ilmiah']
        )->paginate(10)->withQueryString();

        return view('admin.bibit_masuks.index', compact('bibitMasuks'));
    }

    public function create(): View
    {
        $bibits = Bibit::orderBy('nama_tanaman')->get();

        return view('admin.bibit_masuks.create', compact('bibits'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bibit_id' => ['required', 'exists:bibits,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal_masuk' => ['required', 'date'],
            'sumber' => ['required', Rule::in(Bibit::SUMBER)],
            'status_siap_tanam' => ['nullable', 'boolean'],
            'foto' => ['required', 'image', 'max:4096'],
        ], [], [
            'bibit_id' => 'bibit',
            'tanggal_masuk' => 'tanggal masuk',
            'status_siap_tanam' => 'status siap tanam',
            'foto' => 'foto',
        ]);

        $validated['sisa_stok'] = $validated['jumlah'];
        $validated['status_siap_tanam'] = $request->boolean('status_siap_tanam');
        $validated['foto'] = $request->file('foto')->store('bibit-masuk', 'public');

        DB::transaction(function () use ($validated) {
            BibitMasuk::create($validated);

            Bibit::lockForUpdate()
                ->findOrFail($validated['bibit_id'])
                ->increment('stok_tersedia', $validated['jumlah']);
        });

        return redirect()
            ->route('admin.bibit-masuks.index')
            ->with('success', 'Stok masuk berhasil dicatat dan stok bibit telah diperbarui.');
    }

    public function show(BibitMasuk $bibitMasuk): View
    {
        $bibitMasuk->load('bibit');

        return view('admin.bibit_masuks.show', [
            'masuk' => $bibitMasuk,
        ]);
    }

    public function edit(BibitMasuk $bibitMasuk): View
    {
        $bibitMasuk->load('bibit');

        return view('admin.bibit_masuks.edit', [
            'masuk' => $bibitMasuk,
        ]);
    }

    public function update(Request $request, BibitMasuk $bibitMasuk): RedirectResponse
    {
        $terpakai = $bibitMasuk->jumlah - $bibitMasuk->sisa_stok;

        $validated = $request->validate([
            'jumlah' => ['required', 'integer', 'min:'.max(1, $terpakai)],
            'tanggal_masuk' => ['required', 'date'],
            'sumber' => ['required', Rule::in(Bibit::SUMBER)],
            'status_siap_tanam' => ['nullable', 'boolean'],
            'foto' => ['nullable', 'image', 'max:4096'],
        ], [], [
            'tanggal_masuk' => 'tanggal masuk',
            'status_siap_tanam' => 'status siap tanam',
            'foto' => 'foto',
        ]);

        if ($validated['jumlah'] < $terpakai) {
            throw ValidationException::withMessages([
                'jumlah' => "Jumlah tidak boleh kurang dari {$terpakai} karena sebagian stok batch ini sudah dikeluarkan.",
            ]);
        }

        $validated['status_siap_tanam'] = $request->boolean('status_siap_tanam');

        if ($request->hasFile('foto')) {
            if ($bibitMasuk->foto) {
                Storage::disk('public')->delete($bibitMasuk->foto);
            }

            $validated['foto'] = $request->file('foto')->store('bibit-masuk', 'public');
        }

        DB::transaction(function () use ($bibitMasuk, $validated) {
            $bibit = Bibit::lockForUpdate()->findOrFail($bibitMasuk->bibit_id);
            $diff = $validated['jumlah'] - $bibitMasuk->jumlah;

            if ($diff > 0) {
                $bibitMasuk->sisa_stok += $diff;
                $bibit->increment('stok_tersedia', $diff);
            } elseif ($diff < 0) {
                $reduction = abs($diff);

                if ($reduction > $bibitMasuk->sisa_stok) {
                    throw ValidationException::withMessages([
                        'jumlah' => 'Pengurangan jumlah melebihi sisa stok batch yang tersedia.',
                    ]);
                }

                $bibitMasuk->sisa_stok -= $reduction;
                $bibit->decrement('stok_tersedia', $reduction);
            }

            $bibitMasuk->update($validated);
        });

        return redirect()
            ->route('admin.bibit-masuks.index')
            ->with('success', 'Data stok masuk berhasil diperbarui.');
    }

    public function destroy(BibitMasuk $bibitMasuk): RedirectResponse
    {
        if ($bibitMasuk->sisa_stok < $bibitMasuk->jumlah) {
            return redirect()
                ->back()
                ->withErrors([
                    'delete' => 'Stok masuk tidak dapat dihapus karena sebagian batch sudah dikeluarkan. Hapus transaksi keluar terkait terlebih dahulu.',
                ]);
        }

        DB::transaction(function () use ($bibitMasuk) {
            $bibit = Bibit::lockForUpdate()->findOrFail($bibitMasuk->bibit_id);

            if ($bibitMasuk->sisa_stok > 0) {
                $bibit->decrement('stok_tersedia', $bibitMasuk->sisa_stok);
            }

            if ($bibitMasuk->foto) {
                Storage::disk('public')->delete($bibitMasuk->foto);
            }

            $bibitMasuk->delete();
        });

        return redirect()
            ->back(fallback: route('admin.bibit-masuks.index'))
            ->with('success', 'Data stok masuk berhasil dihapus.');
    }
}
