<?php

namespace App\Http\Controllers\Admin\Dpa;

use App\Http\Controllers\Controller;
use App\Models\DpaTahunAnggaran;
use App\Support\DpaMonitoring;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TahunAnggaranController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.manage');
    }

    public function create(): View
    {
        return view('admin.dpa.tahun_anggarans.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100', 'unique:dpa_tahun_anggarans,tahun'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $tahunAnggaran = DpaTahunAnggaran::create($validated);

        return redirect()
            ->route('admin.dpa.tahun-anggarans.show', $tahunAnggaran)
            ->with('success', 'Tahun anggaran berhasil ditambahkan.');
    }

    public function show(DpaTahunAnggaran $tahunAnggaran): View
    {
        $subKegiatanCards = collect(DpaMonitoring::SUB_KEGIATAN)
            ->map(function (array $item) use ($tahunAnggaran) {
                $dpaQuery = $tahunAnggaran->dpas()->where('sub_kegiatan', $item['slug']);

                return [
                    'slug' => $item['slug'],
                    'label' => $item['label'],
                    'dpa_count' => (clone $dpaQuery)->count(),
                    'paket_count' => (clone $dpaQuery)->withCount('paketPekerjaans')->get()->sum('paket_pekerjaans_count'),
                ];
            })
            ->all();

        return view('admin.dpa.tahun_anggarans.show', [
            'tahunAnggaran' => $tahunAnggaran,
            'subKegiatanCards' => $subKegiatanCards,
        ]);
    }

    public function kelola(Request $request, DpaTahunAnggaran $tahunAnggaran): View
    {
        $validated = $request->validate([
            'sub_kegiatan' => ['required', 'string', Rule::in($this->subKegiatanSlugs())],
        ]);

        $subKegiatan = $validated['sub_kegiatan'];
        $subKegiatanLabel = DpaMonitoring::subKegiatanLabel($subKegiatan);

        $dpas = $tahunAnggaran->dpas()
            ->where('sub_kegiatan', $subKegiatan)
            ->with(['paketPekerjaans' => fn ($query) => $query->orderBy('nama_paket')])
            ->withCount('paketPekerjaans')
            ->orderBy('nama_dpa')
            ->get();

        $statsPerTahap = [];
        foreach (DpaMonitoring::TAHAP as $tahap) {
            $statsPerTahap[$tahap] = $dpas->sum(
                fn ($dpa) => $dpa->paketPekerjaans->where('tahap', $tahap)->count(),
            );
        }

        return view('admin.dpa.tahun_anggarans.kelola', [
            'tahunAnggaran' => $tahunAnggaran,
            'subKegiatan' => $subKegiatan,
            'subKegiatanLabel' => $subKegiatanLabel,
            'dpas' => $dpas,
            'statsPerTahap' => $statsPerTahap,
            'tahapLabels' => collect(DpaMonitoring::TAHAP)
                ->mapWithKeys(fn (string $tahap) => [$tahap => DpaMonitoring::tahapLabel($tahap)])
                ->all(),
        ]);
    }

    /**
     * @return list<string>
     */
    private function subKegiatanSlugs(): array
    {
        return collect(DpaMonitoring::SUB_KEGIATAN)->pluck('slug')->all();
    }
}
