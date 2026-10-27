<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Models\PemeliharaanTaman;
use App\Support\ArmadaUsageHistory;
use App\Support\TableSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArmadaController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Armada::class, 'armada');
    }

    public function index(Request $request): View
    {
        $items = Armada::query()
            ->armadaInventory()
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis', $request->input('jenis')))
            ->when($request->filled('kondisi'), fn ($q) => $q->where('kondisi', $request->input('kondisi')))
            ->tap(fn ($q) => TableSearch::apply($q, $request, ['nama', 'jenis', 'no_plat', 'kondisi', 'keterangan']))
            ->orderBy('jenis')
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('admin.armadas.index', [
            'items' => $items,
            'recentUsage' => app(ArmadaUsageHistory::class)->recent(15),
        ]);
    }

    public function show(Armada $armada): View
    {
        $usageHistory = app(ArmadaUsageHistory::class)->forArmada($armada);

        return view('admin.armadas.show', [
            'item' => $armada,
            'usageHistory' => $usageHistory,
        ]);
    }

    public function create(): View
    {
        return view('admin.armadas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Armada::create($this->validated($request));

        return redirect()
            ->route('admin.armadas.index')
            ->with('success', 'Data armada berhasil ditambahkan.');
    }

    public function edit(Armada $armada): View
    {
        return view('admin.armadas.edit', [
            'item' => $armada,
        ]);
    }

    public function update(Request $request, Armada $armada): RedirectResponse
    {
        $armada->update($this->validated($request));

        return redirect()
            ->route('admin.armadas.index')
            ->with('success', 'Data armada berhasil diperbarui.');
    }

    public function destroy(Armada $armada): RedirectResponse
    {
        $armada->delete();

        return redirect()
            ->route('admin.armadas.index')
            ->with('success', 'Data armada berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', Rule::in(Armada::ARMADA_JENIS)],
            'no_plat' => ['required', 'string', 'max:20'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:99999'],
            'kondisi' => ['required', Rule::in(Armada::KONDISI)],
            'keterangan' => ['nullable', 'string'],
        ], [], [
            'nama' => 'nama armada',
            'jenis' => 'jenis armada',
            'no_plat' => 'nomor plat',
            'jumlah' => 'jumlah',
            'kondisi' => 'kondisi',
            'keterangan' => 'keterangan',
        ]);

        $validated['peruntukan'] = PemeliharaanTaman::TIM_ARMADA;
        $validated['sopir'] = null;

        return $validated;
    }
}
