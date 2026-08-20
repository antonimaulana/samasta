<?php

namespace App\Http\Controllers;

use App\Models\EnsiklopediaArtikel;
use App\Models\EnsiklopediaKategori;
use App\Support\EnsiklopediaInteraktif;
use Illuminate\View\View;

class EnsiklopediaController extends Controller
{
    public function index(): View
    {
        $kategori = EnsiklopediaKategori::with('artikelsPublished')
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get()
            ->filter(fn (EnsiklopediaKategori $kat) => $kat->artikelsPublished->isNotEmpty());

        $totalArtikel = EnsiklopediaArtikel::where('is_published', true)->count();

        $highlightArtikel = EnsiklopediaArtikel::with('kategori')
            ->where('is_published', true)
            ->inRandomOrder()
            ->first();

        $musimInfo = EnsiklopediaInteraktif::musimInfo();
        $slugsMusim = EnsiklopediaInteraktif::artikelSlugsMusim();

        $artikelMusiman = EnsiklopediaArtikel::with('kategori')
            ->where('is_published', true)
            ->whereIn('slug', $slugsMusim)
            ->get()
            ->sortBy(fn (EnsiklopediaArtikel $artikel) => array_search($artikel->slug, $slugsMusim, true))
            ->values();

        return view('ensiklopedia.index', compact(
            'kategori',
            'totalArtikel',
            'highlightArtikel',
            'musimInfo',
            'artikelMusiman',
        ));
    }

    public function quiz(): View
    {
        $pertanyaan = EnsiklopediaInteraktif::kuis();

        return view('ensiklopedia.quiz', [
            'pertanyaan' => $pertanyaan,
            'totalPertanyaan' => count($pertanyaan),
        ]);
    }

    public function show(string $slug): View
    {
        $artikel = EnsiklopediaArtikel::with('kategori')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $artikelTerkaits = EnsiklopediaArtikel::with('kategori')
            ->where('ensiklopedia_kategori_id', $artikel->ensiklopedia_kategori_id)
            ->where('id', '!=', $artikel->id)
            ->where('is_published', true)
            ->orderBy('urutan')
            ->orderBy('judul')
            ->take(3)
            ->get();

        $paragraf = array_values(array_filter(
            array_map('trim', explode("\n\n", $artikel->konten)),
            fn (string $p) => $p !== ''
        ));

        return view('ensiklopedia.show', compact('artikel', 'artikelTerkaits', 'paragraf'));
    }
}
