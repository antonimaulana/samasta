<?php

namespace App\Http\Controllers\Admin\Dpa;

use App\Http\Controllers\Controller;
use App\Models\Dpa;
use App\Models\DpaTahunAnggaran;
use App\Support\DpaMonitoring;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DpaController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.manage');
    }

    public function create(Request $request): View
    {
        $tahunAnggaran = DpaTahunAnggaran::query()->findOrFail($request->integer('tahun_anggaran_id'));
        $subKegiatan = $request->input('sub_kegiatan');

        return view('admin.dpa.dpas.create', [
            'tahunAnggaran' => $tahunAnggaran,
            'subKegiatan' => $subKegiatan,
            'subKegiatanOptions' => DpaMonitoring::SUB_KEGIATAN,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dpa_tahun_anggaran_id' => ['required', 'exists:dpa_tahun_anggarans,id'],
            'sub_kegiatan' => ['required', 'string', Rule::in($this->subKegiatanSlugs())],
            'nomor_dpa' => ['nullable', 'string', 'max:255'],
            'nama_dpa' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $dpa = Dpa::create($validated);

        return redirect()
            ->route('admin.dpa.tahun-anggarans.kelola', [
                'tahun_anggaran' => $dpa->dpa_tahun_anggaran_id,
                'sub_kegiatan' => $dpa->sub_kegiatan,
            ])
            ->with('success', 'DPA berhasil ditambahkan.');
    }

    public function show(Dpa $dpa): View
    {
        $dpa->load(['tahunAnggaran', 'paketPekerjaans.penyedia']);

        return view('admin.dpa.dpas.show', compact('dpa'));
    }

    public function edit(Dpa $dpa): View
    {
        $dpa->load('tahunAnggaran');

        return view('admin.dpa.dpas.edit', [
            'dpa' => $dpa,
            'subKegiatanOptions' => DpaMonitoring::SUB_KEGIATAN,
        ]);
    }

    public function update(Request $request, Dpa $dpa): RedirectResponse
    {
        $validated = $request->validate([
            'sub_kegiatan' => ['required', 'string', Rule::in($this->subKegiatanSlugs())],
            'nomor_dpa' => ['nullable', 'string', 'max:255'],
            'nama_dpa' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $dpa->update($validated);

        return redirect()
            ->route('admin.dpa.dpas.show', $dpa)
            ->with('success', 'DPA berhasil diperbarui.');
    }

    public function destroy(Dpa $dpa): RedirectResponse
    {
        $tahunAnggaranId = $dpa->dpa_tahun_anggaran_id;
        $subKegiatan = $dpa->sub_kegiatan;
        $dpa->delete();

        return redirect()
            ->route('admin.dpa.tahun-anggarans.kelola', [
                'tahun_anggaran' => $tahunAnggaranId,
                'sub_kegiatan' => $subKegiatan,
            ])
            ->with('success', 'DPA berhasil dihapus.');
    }

    /**
     * @return list<string>
     */
    private function subKegiatanSlugs(): array
    {
        return collect(DpaMonitoring::SUB_KEGIATAN)->pluck('slug')->all();
    }
}
