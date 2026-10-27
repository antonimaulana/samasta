<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnsiklopediaArtikel;
use App\Models\EnsiklopediaKategori;
use App\Models\EnsiklopediaPortalSetting;
use App\Support\EnsiklopediaPortalConfig;
use App\Support\TableSearch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnsiklopediaPengaturanController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.manage');
    }

    public function index(Request $request): View
    {
        $tab = in_array($request->query('tab'), ['kategori', 'artikel', 'portal'], true)
            ? $request->query('tab')
            : 'kategori';

        $kategoris = TableSearch::apply(
            EnsiklopediaKategori::withCount('artikels')->orderBy('urutan')->orderBy('nama'),
            $request,
            ['nama', 'slug', 'deskripsi'],
            'kategori_search'
        )->paginate(15, ['*'], 'kategori_page')->withQueryString();

        $artikels = EnsiklopediaArtikel::with('kategori')
            ->when($request->filled('kategori'), function ($query) use ($request) {
                $query->where('ensiklopedia_kategori_id', $request->integer('kategori'));
            })
            ->tap(fn ($q) => TableSearch::apply($q, $request, [
                'judul',
                'slug',
                'ringkas',
                'konten',
                'kategori.nama',
            ], 'artikel_search'))
            ->orderBy('urutan')
            ->orderBy('judul')
            ->paginate(15, ['*'], 'artikel_page')
            ->withQueryString();

        $kategorisFilter = EnsiklopediaKategori::orderBy('nama')->get();

        $setting = EnsiklopediaPortalSetting::query()->firstOrCreate([]);
        $interaktif = array_replace_recursive(
            EnsiklopediaPortalConfig::interaktifDefaults(),
            $setting->interaktif_config ?? []
        );

        return view('admin.ensiklopedia.pengaturan', [
            'tab' => $tab,
            'kategoris' => $kategoris,
            'artikels' => $artikels,
            'kategorisFilter' => $kategorisFilter,
            'setting' => $setting,
            'interaktif' => $interaktif,
        ]);
    }
}
