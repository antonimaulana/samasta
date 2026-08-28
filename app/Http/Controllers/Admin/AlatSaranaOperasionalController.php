<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlatSaranaOperasional;
use App\Support\ArmadaUsageHistory;
use App\Support\TableSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AlatSaranaOperasionalController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(AlatSaranaOperasional::class, 'alat_sarana_operasional');
    }

    public function index(Request $request): View
    {
        $items = AlatSaranaOperasional::query()
            ->when($request->filled('peruntukan'), fn ($q) => $q->where('peruntukan', $request->input('peruntukan')))
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis', $request->input('jenis')))
            ->when($request->filled('kondisi'), fn ($q) => $q->where('kondisi', $request->input('kondisi')))
            ->tap(fn ($q) => TableSearch::apply($q, $request, ['nama', 'jenis', 'peruntukan', 'kondisi', 'keterangan']))
            ->orderBy('peruntukan')
            ->orderBy('jenis')
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('admin.alat_sarana_operasionals.index', [
            'items' => $items,
        ]);
    }

    public function show(AlatSaranaOperasional $alatSaranaOperasional): View
    {
        $usageHistory = app(ArmadaUsageHistory::class)->forAlat($alatSaranaOperasional);

        return view('admin.alat_sarana_operasionals.show', [
            'item' => $alatSaranaOperasional,
            'usageHistory' => $usageHistory,
        ]);
    }

    public function create(): View
    {
        return view('admin.alat_sarana_operasionals.create');
    }

    public function store(Request $request): RedirectResponse
    {
        AlatSaranaOperasional::create($this->validated($request));

        return redirect()
            ->route('admin.alat-sarana-operasionals.index')
            ->with('success', 'Alat/sarana operasional berhasil ditambahkan.');
    }

    public function edit(AlatSaranaOperasional $alatSaranaOperasional): View
    {
        return view('admin.alat_sarana_operasionals.edit', [
            'item' => $alatSaranaOperasional,
        ]);
    }

    public function update(Request $request, AlatSaranaOperasional $alatSaranaOperasional): RedirectResponse
    {
        $alatSaranaOperasional->update($this->validated($request));

        return redirect()
            ->route('admin.alat-sarana-operasionals.index')
            ->with('success', 'Alat/sarana operasional berhasil diperbarui.');
    }

    public function destroy(AlatSaranaOperasional $alatSaranaOperasional): RedirectResponse
    {
        $alatSaranaOperasional->delete();

        return redirect()
            ->route('admin.alat-sarana-operasionals.index')
            ->with('success', 'Alat/sarana operasional berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', Rule::in(AlatSaranaOperasional::JENIS)],
            'no_plat' => [
                Rule::requiredIf(fn () => AlatSaranaOperasional::isArmadaJenis((string) $request->input('jenis'))),
                'nullable',
                'string',
                'max:20',
            ],
            'sopir' => [
                Rule::requiredIf(fn () => AlatSaranaOperasional::isArmadaJenis((string) $request->input('jenis'))),
                'nullable',
                'string',
                'max:100',
            ],
            'jumlah' => ['required', 'integer', 'min:1', 'max:99999'],
            'peruntukan' => ['required', Rule::in(AlatSaranaOperasional::timOptions())],
            'kondisi' => ['required', Rule::in(AlatSaranaOperasional::KONDISI)],
            'keterangan' => ['nullable', 'string'],
        ], [], [
            'nama' => 'nama alat/armada',
            'jenis' => 'jenis',
            'no_plat' => 'nomor plat',
            'sopir' => 'sopir',
            'jumlah' => 'jumlah',
            'peruntukan' => 'peruntukan (tim)',
            'kondisi' => 'kondisi',
            'keterangan' => 'keterangan',
        ]);

        if (! AlatSaranaOperasional::isArmadaJenis($validated['jenis'])) {
            $validated['no_plat'] = null;
            $validated['sopir'] = null;
        }

        return $validated;
    }
}
