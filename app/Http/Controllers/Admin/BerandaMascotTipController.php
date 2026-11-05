<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BerandaMascotTip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BerandaMascotTipController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.manage');
    }

    public function index(): View
    {
        $tips = BerandaMascotTip::query()
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        return view('admin.beranda_mascot_tips.index', compact('tips'));
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
