<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAduanRequest;
use App\Models\AduanMasyarakat;
use App\Support\OperationalAlertService;
use App\Support\OperatorWilayahScope;
use App\Support\TableSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AduanMasyarakatController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(AduanMasyarakat::class, 'aduan_masyarakat', [
            'except' => ['index'],
        ]);
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AduanMasyarakat::class);

        $scope = app(OperatorWilayahScope::class);

        $aduans = $scope->scopeAduan(
            AduanMasyarakat::query()->with('taman'),
            $request->user(),
        )
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->input('alert') === 'overdue', fn ($q) => $q
                ->where('status', config('alerts.aduan.unreviewed_status'))
                ->where('created_at', '<=', now()->subDays(config('alerts.aduan.unreviewed_days'))))
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis_aduan', $request->jenis))
            ->tap(fn ($q) => TableSearch::apply($q, $request, [
                'nomor_aduan',
                'lokasi',
                'deskripsi',
                'nama_pelapor',
                'kontak_pelapor',
                'jenis_aduan',
                'status',
            ]))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $ringkasanStatus = collect(AduanMasyarakat::STATUS)->mapWithKeys(
            fn (string $status) => [$status => $scope->scopeAduan(
                AduanMasyarakat::query()->where('status', $status),
                $request->user(),
            )->count()]
        );

        return view('admin.aduan_masyarakats.index', compact('aduans', 'ringkasanStatus'));
    }

    public function show(AduanMasyarakat $aduanMasyarakat): View
    {
        $aduanMasyarakat->load('taman');

        return view('admin.aduan_masyarakats.show', [
            'aduan' => $aduanMasyarakat,
        ]);
    }

    public function update(UpdateAduanRequest $request, AduanMasyarakat $aduanMasyarakat): RedirectResponse
    {
        $aduanMasyarakat->update($request->validated());

        OperationalAlertService::clearCache();

        return redirect()
            ->route('admin.aduan-masyarakats.show', $aduanMasyarakat)
            ->with('success', 'Status aduan berhasil diperbarui.');
    }

    public function updateStatus(Request $request, AduanMasyarakat $aduanMasyarakat): RedirectResponse
    {
        $this->authorize('updateStatus', $aduanMasyarakat);

        $validated = $request->validate([
            'status' => ['required', Rule::in(AduanMasyarakat::STATUS)],
        ]);

        $aduanMasyarakat->update(['status' => $validated['status']]);

        OperationalAlertService::clearCache();

        return back()->with('success', 'Status aduan diperbarui.');
    }

    public function destroy(AduanMasyarakat $aduanMasyarakat): RedirectResponse
    {
        if ($aduanMasyarakat->foto) {
            Storage::disk('public')->delete($aduanMasyarakat->foto);
        }

        $aduanMasyarakat->delete();

        return redirect()
            ->route('admin.aduan-masyarakats.index')
            ->with('success', 'Aduan berhasil dihapus.');
    }
}
