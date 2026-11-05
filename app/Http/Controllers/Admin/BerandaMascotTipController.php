<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BerandaMascotSetting;
use App\Models\BerandaMascotTip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class BerandaMascotTipController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.manage');
    }

    public function index(): View
    {
        $schemaReady = BerandaMascotSetting::schemaReady();

        if ($schemaReady && BerandaMascotTip::query()->count() === 0) {
            BerandaMascotTip::seedDefaults();
        }

        $tips = $schemaReady
            ? BerandaMascotTip::query()->orderBy('urutan')->orderBy('id')->get()
            : collect();

        return view('admin.beranda_mascot_tips.index', [
            'tips' => $tips,
            'setting' => BerandaMascotSetting::current(),
            'schemaReady' => $schemaReady,
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        if (! BerandaMascotSetting::schemaReady()) {
            return back()->with('error', 'Tabel pesan Tama belum ada. Jalankan php artisan migrate di server.');
        }

        $request->validate([
            'popups_enabled' => ['required', 'in:0,1'],
        ]);

        $enabled = $request->boolean('popups_enabled');

        BerandaMascotSetting::current()->update([
            'popups_enabled' => $enabled,
        ]);

        $message = $enabled
            ? 'Pop-up Tama di beranda diaktifkan.'
            : 'Pop-up Tama di beranda dinonaktifkan.';

        return back()->with('success', $message);
    }

    public function activateAll(): RedirectResponse
    {
        if (! BerandaMascotSetting::schemaReady()) {
            return back()->with('error', 'Tabel pesan Tama belum ada. Jalankan php artisan migrate di server.');
        }

        BerandaMascotTip::query()->update(['is_active' => true]);

        return back()->with('success', 'Semua pesan Tama diaktifkan.');
    }

    public function deactivateAll(): RedirectResponse
    {
        if (! BerandaMascotSetting::schemaReady()) {
            return back()->with('error', 'Tabel pesan Tama belum ada. Jalankan php artisan migrate di server.');
        }

        BerandaMascotTip::query()->update(['is_active' => false]);

        return back()->with('success', 'Semua pesan Tama dinonaktifkan.');
    }

    public function edit(BerandaMascotTip $berandaMascotTip): View
    {
        return view('admin.beranda_mascot_tips.edit', [
            'tip' => $berandaMascotTip,
        ]);
    }

    public function update(Request $request, BerandaMascotTip $berandaMascotTip): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:2000'],
            'image_asset' => ['required', 'string', 'max:255'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'cta_url' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $berandaMascotTip->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.beranda-mascot-tips.index')
            ->with('success', 'Pesan Tama «'.$berandaMascotTip->section_label.'» disimpan.');
    }

    public function toggle(BerandaMascotTip $berandaMascotTip): RedirectResponse
    {
        $berandaMascotTip->update(['is_active' => ! $berandaMascotTip->is_active]);

        return back()->with('success', 'Status pesan Tama diperbarui.');
    }
}
