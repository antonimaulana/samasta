<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RthKategori;
use App\Support\KontenBerandaCache;
use App\Support\TableSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RthKategoriController extends Controller
{
    public function index(Request $request): View
    {
        $kategoris = TableSearch::apply(
            RthKategori::query()->orderBy('urutan')->orderBy('nama'),
            $request,
            ['nama', 'ringkas']
        )->paginate(15)->withQueryString();

        return view('admin.rth_kategoris.index', compact('kategoris'));
    }

    public function create(): View
    {
        return view('admin.rth_kategoris.create');
    }

    public function store(Request $request): RedirectResponse
    {
        RthKategori::create($this->validateKategori($request));
        KontenBerandaCache::forgetAll();

        return redirect()
            ->route('admin.rth-kategoris.index')
            ->with('success', 'Kategori RTH berhasil ditambahkan.');
    }

    public function edit(RthKategori $rthKategori): View
    {
        return view('admin.rth_kategoris.edit', [
            'kategori' => $rthKategori,
        ]);
    }

    public function update(Request $request, RthKategori $rthKategori): RedirectResponse
    {
        $rthKategori->update($this->validateKategori($request));
        KontenBerandaCache::forgetAll();

        return redirect()
            ->route('admin.rth-kategoris.index')
            ->with('success', 'Kategori RTH berhasil diperbarui.');
    }

    public function destroy(RthKategori $rthKategori): RedirectResponse
    {
        $rthKategori->delete();
        KontenBerandaCache::forgetAll();

        return redirect()
            ->route('admin.rth-kategoris.index')
            ->with('success', 'Kategori RTH berhasil dihapus.');
    }

    private function validateKategori(Request $request): array
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'luas' => ['required', 'integer', 'min:0'],
            'lokasi' => ['required', 'integer', 'min:0'],
            'icon' => ['required', 'string', 'max:10'],
            'ringkas' => ['nullable', 'string'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $validated['urutan'] = $validated['urutan'] ?? 0;
        $validated['is_published'] = $request->boolean('is_published');

        return $validated;
    }
}
