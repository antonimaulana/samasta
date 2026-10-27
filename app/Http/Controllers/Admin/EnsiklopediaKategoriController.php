<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnsiklopediaKategori;
use App\Support\TableSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnsiklopediaKategoriController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.manage');
    }

    public function index(Request $request): RedirectResponse
    {
        $query = $request->query();
        if (isset($query['search'])) {
            $query['kategori_search'] = $query['search'];
            unset($query['search']);
        }
        $query['tab'] = 'kategori';

        return redirect()->route('admin.ensiklopedia-pengaturan.index', $query);
    }

    public function create(): View
    {
        return view('admin.ensiklopedia_kategoris.create');
    }

    public function store(Request $request): RedirectResponse
    {
        EnsiklopediaKategori::create($this->validateKategori($request));

        return redirect()
            ->route('admin.ensiklopedia-pengaturan.index', ['tab' => 'kategori'])
            ->with('success', 'Kategori ensiklopedia berhasil ditambahkan.');
    }

    public function edit(EnsiklopediaKategori $ensiklopediaKategori): View
    {
        return view('admin.ensiklopedia_kategoris.edit', [
            'kategori' => $ensiklopediaKategori,
        ]);
    }

    public function update(Request $request, EnsiklopediaKategori $ensiklopediaKategori): RedirectResponse
    {
        $ensiklopediaKategori->update($this->validateKategori($request, $ensiklopediaKategori));

        return redirect()
            ->route('admin.ensiklopedia-pengaturan.index', ['tab' => 'kategori'])
            ->with('success', 'Kategori ensiklopedia berhasil diperbarui.');
    }

    public function destroy(EnsiklopediaKategori $ensiklopediaKategori): RedirectResponse
    {
        $ensiklopediaKategori->delete();

        return redirect()
            ->route('admin.ensiklopedia-pengaturan.index', ['tab' => 'kategori'])
            ->with('success', 'Kategori ensiklopedia berhasil dihapus.');
    }

    private function validateKategori(Request $request, ?EnsiklopediaKategori $kategori = null): array
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('ensiklopedia_kategoris', 'slug')->ignore($kategori?->id),
            ],
            'icon' => ['required', 'string', 'max:10'],
            'deskripsi' => ['nullable', 'string'],
            'urutan' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['nama']);
        $validated['urutan'] = $validated['urutan'] ?? 0;

        return $validated;
    }
}
