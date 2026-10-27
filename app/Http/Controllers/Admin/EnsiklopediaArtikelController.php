<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnsiklopediaArtikel;
use App\Models\EnsiklopediaKategori;
use App\Support\TableSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnsiklopediaArtikelController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.manage');
    }

    public function index(Request $request): RedirectResponse
    {
        $query = $request->query();
        if (isset($query['search'])) {
            $query['artikel_search'] = $query['search'];
            unset($query['search']);
        }
        $query['tab'] = 'artikel';

        return redirect()->route('admin.ensiklopedia-pengaturan.index', $query);
    }

    public function create(): View
    {
        $kategoris = EnsiklopediaKategori::orderBy('urutan')->orderBy('nama')->get();

        return view('admin.ensiklopedia_artikels.create', compact('kategoris'));
    }

    public function store(Request $request): RedirectResponse
    {
        EnsiklopediaArtikel::create($this->validateArtikel($request));

        return redirect()
            ->route('admin.ensiklopedia-pengaturan.index', ['tab' => 'artikel'])
            ->with('success', 'Artikel ensiklopedia berhasil ditambahkan.');
    }

    public function edit(EnsiklopediaArtikel $ensiklopediaArtikel): View
    {
        $kategoris = EnsiklopediaKategori::orderBy('urutan')->orderBy('nama')->get();

        return view('admin.ensiklopedia_artikels.edit', [
            'artikel' => $ensiklopediaArtikel,
            'kategoris' => $kategoris,
        ]);
    }

    public function update(Request $request, EnsiklopediaArtikel $ensiklopediaArtikel): RedirectResponse
    {
        $ensiklopediaArtikel->update($this->validateArtikel($request, $ensiklopediaArtikel));

        return redirect()
            ->route('admin.ensiklopedia-pengaturan.index', ['tab' => 'artikel'])
            ->with('success', 'Artikel ensiklopedia berhasil diperbarui.');
    }

    public function destroy(EnsiklopediaArtikel $ensiklopediaArtikel): RedirectResponse
    {
        $ensiklopediaArtikel->delete();

        return redirect()
            ->route('admin.ensiklopedia-pengaturan.index', ['tab' => 'artikel'])
            ->with('success', 'Artikel ensiklopedia berhasil dihapus.');
    }

    private function validateArtikel(Request $request, ?EnsiklopediaArtikel $artikel = null): array
    {
        $validated = $request->validate([
            'ensiklopedia_kategori_id' => ['required', Rule::exists('ensiklopedia_kategoris', 'id')],
            'judul' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('ensiklopedia_artikels', 'slug')->ignore($artikel?->id),
            ],
            'icon' => ['required', 'string', 'max:10'],
            'ringkas' => ['required', 'string', 'max:500'],
            'konten' => ['required', 'string'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['judul']);
        $validated['urutan'] = $validated['urutan'] ?? 0;
        $validated['is_published'] = $request->boolean('is_published');

        return $validated;
    }
}
