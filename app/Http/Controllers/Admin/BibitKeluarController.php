<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bibit;
use App\Models\BibitKeluar;
use App\Models\BibitMasuk;
use App\Models\Taman;
use App\Support\TableSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BibitKeluarController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(BibitKeluar::class, 'bibit_keluar');
    }

    public function index(Request $request): View
    {
        $bibitKeluars = TableSearch::apply(
            BibitKeluar::with(['bibit', 'taman'])->latest('tanggal_keluar')->latest(),
            $request,
            ['peruntukan', 'bibit.nama_tanaman', 'bibit.nama_ilmiah', 'taman.nama_taman']
        )->paginate(10)->withQueryString();

        return view('admin.bibit_keluars.index', compact('bibitKeluars'));
    }

    public function create(): View
    {
        $bibits = Bibit::where('stok_tersedia', '>', 0)->orderBy('nama_tanaman')->get();
        $tamans = Taman::orderBy('nama_taman')->get();

        return view('admin.bibit_keluars.create', compact('bibits', 'tamans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bibit_id' => ['required', 'exists:bibits,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal_keluar' => ['required', 'date'],
            'peruntukan' => ['required', Rule::in(BibitKeluar::PERUNTUKAN)],
            'taman_id' => ['required', 'exists:tamans,id'],
            'foto' => ['required', 'image', 'max:4096'],
        ], [], [
            'bibit_id' => 'bibit',
            'tanggal_keluar' => 'tanggal keluar',
            'peruntukan' => 'peruntukan',
            'taman_id' => 'lokasi',
            'foto' => 'foto',
        ]);

        $validated['foto'] = $request->file('foto')->store('bibit-keluar', 'public');

        DB::transaction(function () use ($validated) {
            $bibit = Bibit::lockForUpdate()->findOrFail($validated['bibit_id']);

            if ($validated['jumlah'] > $bibit->stok_tersedia) {
                throw ValidationException::withMessages([
                    'jumlah' => "Jumlah keluar ({$validated['jumlah']}) melebihi stok tersedia ({$bibit->stok_tersedia}) untuk {$bibit->nama_tanaman}.",
                ]);
            }

            $this->kurangiStokMasuk($bibit, $validated['jumlah']);

            BibitKeluar::create($validated);

            $bibit->decrement('stok_tersedia', $validated['jumlah']);
        });

        return redirect()
            ->route('admin.bibit-keluars.index')
            ->with('success', 'Stok keluar berhasil dicatat dan stok bibit telah diperbarui.');
    }

    public function show(BibitKeluar $bibitKeluar): View
    {
        $bibitKeluar->load(['bibit', 'taman']);

        return view('admin.bibit_keluars.show', [
            'keluar' => $bibitKeluar,
        ]);
    }

    public function edit(BibitKeluar $bibitKeluar): View
    {
        $bibitKeluar->load('bibit');
        $tamans = Taman::orderBy('nama_taman')->get();

        return view('admin.bibit_keluars.edit', [
            'keluar' => $bibitKeluar,
            'tamans' => $tamans,
        ]);
    }

    public function update(Request $request, BibitKeluar $bibitKeluar): RedirectResponse
    {
        $validated = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal_keluar' => ['required', 'date'],
            'peruntukan' => ['required', Rule::in(BibitKeluar::PERUNTUKAN)],
            'taman_id' => ['required', 'exists:tamans,id'],
            'foto' => ['nullable', 'image', 'max:4096'],
        ], [], [
            'tanggal_keluar' => 'tanggal keluar',
            'peruntukan' => 'peruntukan',
            'taman_id' => 'lokasi',
            'foto' => 'foto',
        ]);

        if ($request->hasFile('foto')) {
            if ($bibitKeluar->foto) {
                Storage::disk('public')->delete($bibitKeluar->foto);
            }

            $validated['foto'] = $request->file('foto')->store('bibit-keluar', 'public');
        }

        DB::transaction(function () use ($bibitKeluar, $validated) {
            $bibit = Bibit::lockForUpdate()->findOrFail($bibitKeluar->bibit_id);
            $diff = $validated['jumlah'] - $bibitKeluar->jumlah;

            if ($diff > 0) {
                if ($diff > $bibit->stok_tersedia) {
                    throw ValidationException::withMessages([
                        'jumlah' => "Penambahan jumlah ({$diff}) melebihi stok tersedia ({$bibit->stok_tersedia}).",
                    ]);
                }

                $this->kurangiStokMasuk($bibit, $diff);
                $bibit->decrement('stok_tersedia', $diff);
            } elseif ($diff < 0) {
                $this->kembalikanStokMasuk($bibit, abs($diff));
                $bibit->increment('stok_tersedia', abs($diff));
            }

            $bibitKeluar->update($validated);
        });

        return redirect()
            ->route('admin.bibit-keluars.index')
            ->with('success', 'Data stok keluar berhasil diperbarui.');
    }

    public function destroy(BibitKeluar $bibitKeluar): RedirectResponse
    {
        DB::transaction(function () use ($bibitKeluar) {
            $bibit = Bibit::lockForUpdate()->findOrFail($bibitKeluar->bibit_id);

            $this->kembalikanStokMasuk($bibit, $bibitKeluar->jumlah);
            $bibit->increment('stok_tersedia', $bibitKeluar->jumlah);

            if ($bibitKeluar->foto) {
                Storage::disk('public')->delete($bibitKeluar->foto);
            }

            $bibitKeluar->delete();
        });

        return redirect()
            ->back(fallback: route('admin.bibit-keluars.index'))
            ->with('success', 'Data stok keluar berhasil dihapus dan stok bibit telah dikembalikan.');
    }

    private function kurangiStokMasuk(Bibit $bibit, int $jumlah): void
    {
        $sisa = $jumlah;

        $masuks = BibitMasuk::where('bibit_id', $bibit->id)
            ->where('sisa_stok', '>', 0)
            ->orderBy('tanggal_masuk')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($masuks as $masuk) {
            if ($sisa <= 0) {
                break;
            }

            $potong = min($sisa, $masuk->sisa_stok);
            $masuk->decrement('sisa_stok', $potong);
            $sisa -= $potong;
        }

        if ($sisa > 0) {
            throw ValidationException::withMessages([
                'jumlah' => 'Stok batch tidak mencukupi untuk pengeluaran ini.',
            ]);
        }
    }

    private function kembalikanStokMasuk(Bibit $bibit, int $jumlah): void
    {
        $sisa = $jumlah;

        $masuks = BibitMasuk::where('bibit_id', $bibit->id)
            ->whereColumn('sisa_stok', '<', 'jumlah')
            ->orderByDesc('tanggal_masuk')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->get();

        foreach ($masuks as $masuk) {
            if ($sisa <= 0) {
                break;
            }

            $kapasitas = $masuk->jumlah - $masuk->sisa_stok;
            $tambah = min($sisa, $kapasitas);
            $masuk->increment('sisa_stok', $tambah);
            $sisa -= $tambah;
        }

        if ($sisa > 0) {
            $masuk = BibitMasuk::where('bibit_id', $bibit->id)
                ->orderByDesc('tanggal_masuk')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($masuk) {
                $masuk->increment('sisa_stok', $sisa);
                $masuk->increment('jumlah', $sisa);
            }
        }
    }
}
