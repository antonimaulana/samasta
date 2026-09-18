<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Petugas;
use App\Models\TimPelaksana;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetugasController extends Controller
{
    public function index(TimPelaksana $timPelaksana): View
    {
        $this->authorize('managePetugas', $timPelaksana);

        $petugas = $timPelaksana->petugas()
            ->orderByDesc('aktif')
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        return view('admin.tim_pelaksanas.petugas.index', [
            'timPelaksana' => $timPelaksana,
            'petugas' => $petugas,
        ]);
    }

    public function store(Request $request, TimPelaksana $timPelaksana): RedirectResponse
    {
        $this->authorize('managePetugas', $timPelaksana);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'jabatan' => ['nullable', 'string', 'max:80'],
            'is_inti' => ['nullable', 'boolean'],
            'is_pengawas' => ['nullable', 'boolean'],
        ]);

        $timPelaksana->petugas()->create([
            'nama' => trim($validated['nama']),
            'jabatan' => filled($validated['jabatan'] ?? null) ? trim((string) $validated['jabatan']) : null,
            'is_inti' => $request->boolean('is_inti'),
            'is_pengawas' => $request->boolean('is_pengawas'),
            'aktif' => true,
            'urutan' => (int) ($timPelaksana->petugas()->max('urutan') ?? 0) + 1,
        ]);

        return back()->with('success', 'Petugas berhasil ditambahkan.');
    }

    public function update(Request $request, TimPelaksana $timPelaksana, Petugas $petugas): RedirectResponse
    {
        $this->authorize('managePetugas', $timPelaksana);
        $this->ensurePetugasBelongsToTeam($timPelaksana, $petugas);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'jabatan' => ['nullable', 'string', 'max:80'],
            'is_inti' => ['nullable', 'boolean'],
            'is_pengawas' => ['nullable', 'boolean'],
            'aktif' => ['nullable', 'boolean'],
        ]);

        $petugas->update([
            'nama' => trim($validated['nama']),
            'jabatan' => filled($validated['jabatan'] ?? null) ? trim((string) $validated['jabatan']) : null,
            'is_inti' => $request->boolean('is_inti'),
            'is_pengawas' => $request->boolean('is_pengawas'),
            'aktif' => $request->boolean('aktif', true),
        ]);

        return back()->with('success', 'Data petugas diperbarui.');
    }

    public function destroy(TimPelaksana $timPelaksana, Petugas $petugas): RedirectResponse
    {
        $this->authorize('managePetugas', $timPelaksana);
        $this->ensurePetugasBelongsToTeam($timPelaksana, $petugas);

        $petugas->delete();

        return back()->with('success', 'Petugas dihapus.');
    }

    private function ensurePetugasBelongsToTeam(TimPelaksana $timPelaksana, Petugas $petugas): void
    {
        if ($petugas->tim_pelaksana_id !== $timPelaksana->id) {
            abort(404);
        }
    }
}
