<?php

namespace App\Http\Controllers\Lapangan;

use App\Http\Controllers\Controller;
use App\Models\PemeliharaanTaman;
use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\Taman;
use App\Support\ArmadaAssignment;
use App\Support\LapanganGuestAccess;
use App\Support\LapanganMenu;
use App\Support\OperatorWilayahScope;
use App\Support\PemeliharaanTamanRecorder;
use App\Support\PemangkasanProgresPdf;
use App\Support\PermohonanProgressRecorder;
use App\Support\TimPelaksanaResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class OperasionalController extends Controller
{
    public function showUnlock(Request $request): View|RedirectResponse
    {
        if ($request->user()?->canWrite()) {
            return redirect()->route('lapangan.index');
        }

        if (LapanganGuestAccess::isUnlocked($request)) {
            return redirect()->route('lapangan.index');
        }

        if (! LapanganGuestAccess::enabled()) {
            return redirect()->route('login');
        }

        return view('lapangan.unlock');
    }

    public function storeUnlock(Request $request): RedirectResponse
    {
        if ($request->user()?->canWrite()) {
            return redirect()->route('lapangan.index');
        }

        if (! LapanganGuestAccess::enabled()) {
            return redirect()->route('login');
        }

        $request->validate([
            'pin' => ['required', 'string', 'max:32'],
        ]);

        if (! LapanganGuestAccess::attempt($request, $request->string('pin')->toString())) {
            return back()
                ->withErrors(['pin' => 'PIN salah.'])
                ->onlyInput('pin');
        }

        return redirect()->intended(route('lapangan.index'));
    }

    public function lock(Request $request): RedirectResponse
    {
        LapanganGuestAccess::lock($request);

        if ($request->user()?->canWrite()) {
            return redirect()->route('lapangan.index');
        }

        return redirect()
            ->route('lapangan.unlock')
            ->with('success', 'Sesi input lapangan ditutup.');
    }

    public function index(Request $request): View
    {
        return view('lapangan.index', [
            'menuItems' => LapanganMenu::items($request->user()),
            'guestMode' => ! $request->user() && LapanganGuestAccess::isUnlocked($request),
        ]);
    }

    public function createPemeliharaan(Request $request, string $slug): View
    {
        $this->ensureCanWriteOperasional($request, PemeliharaanTaman::class);

        $menuItem = LapanganMenu::find($slug, $request->user());

        if (! $menuItem || $menuItem['type'] !== 'pemeliharaan') {
            abort(404);
        }

        $tim = $menuItem['tim'];
        $recorder = app(PemeliharaanTamanRecorder::class);

        return view('lapangan.pemeliharaan.create', [
            'menuItem' => $menuItem,
            'tim' => $tim,
            'tamans' => $recorder->tamanOptionsForTeam($request->user(), $tim),
            'prefillTamanId' => $request->integer('taman_id') ?: null,
            'timWilayahKelurahan' => app(TimPelaksanaResolver::class)->kelurahanIdsByTeamName(),
            'armadaInventory' => ArmadaAssignment::inventory(),
        ]);
    }

    public function storePemeliharaan(Request $request, string $slug): RedirectResponse
    {
        $this->ensureCanWriteOperasional($request, PemeliharaanTaman::class);

        $menuItem = LapanganMenu::find($slug, $request->user());

        if (! $menuItem || $menuItem['type'] !== 'pemeliharaan') {
            abort(404);
        }

        $request->merge(['tim' => $menuItem['tim']]);

        $pemeliharaan = app(PemeliharaanTamanRecorder::class)->record($request, $request->user());

        return redirect()
            ->route('lapangan.index')
            ->with('success', 'Pemeliharaan rutin '.$menuItem['label'].' berhasil dicatat untuk '.\App\Support\OperasionalPelaksanaanTime::display($pemeliharaan->tanggal).'.');
    }

    public function indexPermohonan(Request $request): View
    {
        $this->ensureCanWriteOperasional($request, Pemangkasan::class);

        $scope = app(OperatorWilayahScope::class);
        $status = (string) $request->input('status', 'Rencana');

        if (! in_array($status, Pemangkasan::STATUS, true)) {
            $status = 'Rencana';
        }

        $permohonans = $scope->scopePemangkasan(
            Pemangkasan::query()
                ->with(['taman'])
                ->where('status', $status)
                ->when($status === 'Selesai', fn ($q) => $q->orderByDesc('tanggal_penyelesaian'))
                ->when($status !== 'Selesai', fn ($q) => $q->orderBy('tanggal_eksekusi'))
                ->orderByDesc('tanggal_permohonan'),
            $request->user(),
        )->get();

        $counts = collect(Pemangkasan::STATUS)
            ->mapWithKeys(fn (string $label) => [
                $label => $scope->scopePemangkasan(
                    Pemangkasan::query()->where('status', $label),
                    $request->user(),
                )->count(),
            ])
            ->all();

        return view('lapangan.permohonan.index', [
            'menuItem' => LapanganMenu::find('permohonan', $request->user()),
            'permohonans' => $permohonans,
            'statusFilter' => $status,
            'statusCounts' => $counts,
        ]);
    }

    public function editPermohonan(Request $request, Pemangkasan $pemangkasan): View
    {
        $this->ensureCanUpdatePermohonan($request, $pemangkasan);

        $pemangkasan->load(['taman', 'progres']);

        return view('lapangan.permohonan.edit', [
            'menuItem' => LapanganMenu::find('permohonan', $request->user()),
            'permohonan' => $pemangkasan,
            'armadaInventory' => ArmadaAssignment::inventory(),
        ]);
    }

    public function updatePermohonan(Request $request, Pemangkasan $pemangkasan): RedirectResponse
    {
        $this->ensureCanUpdatePermohonan($request, $pemangkasan);

        $wasRencana = $pemangkasan->status === 'Rencana';

        app(PermohonanProgressRecorder::class)->update($pemangkasan, $request);

        $message = match ($request->input('status')) {
            'Selesai' => 'Progres permohonan berhasil disimpan. Pekerjaan ditandai selesai.',
            'Diproses' => $wasRencana
                ? 'Pekerjaan dimulai. Progres hari pertama berhasil disimpan.'
                : 'Progres permohonan berhasil disimpan.',
            default => 'Progres permohonan berhasil disimpan.',
        };

        return redirect()
            ->route('lapangan.permohonan.index')
            ->with('success', $message);
    }

    public function exportProgresPdf(Request $request, Pemangkasan $pemangkasan, PemangkasanProgres $pemangkasanProgres): Response
    {
        $this->ensureCanViewPermohonan($request, $pemangkasan);

        return PemangkasanProgresPdf::download($pemangkasan, $pemangkasanProgres);
    }

    /**
     * @param  class-string  $modelClass
     */
    private function ensureCanWriteOperasional(Request $request, string $modelClass): void
    {
        if ($request->user()?->canWrite()) {
            $this->authorize('create', $modelClass);

            return;
        }

        if (! LapanganGuestAccess::isUnlocked($request)) {
            abort(403);
        }
    }

    private function ensureCanUpdatePermohonan(Request $request, Pemangkasan $pemangkasan): void
    {
        if ($pemangkasan->status === 'Selesai') {
            abort(403, 'Permohonan ini sudah selesai.');
        }

        if ($request->user()?->canWrite()) {
            $this->authorize('update', $pemangkasan);

            if (! app(OperatorWilayahScope::class)->canAccess($request->user(), $pemangkasan)) {
                abort(403);
            }

            return;
        }

        if (! LapanganGuestAccess::isUnlocked($request)) {
            abort(403);
        }
    }

    private function ensureCanViewPermohonan(Request $request, Pemangkasan $pemangkasan): void
    {
        if ($request->user()?->canWrite()) {
            $this->authorize('view', $pemangkasan);

            if (! app(OperatorWilayahScope::class)->canAccess($request->user(), $pemangkasan)) {
                abort(403);
            }

            return;
        }

        if (! LapanganGuestAccess::isUnlocked($request)) {
            abort(403);
        }
    }
}
