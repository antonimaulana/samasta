<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KotaProfile;
use App\Support\KontenBerandaCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KotaProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.kota_profiles.edit', [
            'profile' => KotaProfile::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'visi' => ['nullable', 'string'],
            'misi' => ['nullable', 'string'],
        ]);

        KotaProfile::current()->update($validated);
        KontenBerandaCache::forgetAll();

        return redirect()
            ->route('admin.kota-profile.edit')
            ->with('success', 'Visi dan misi kota berhasil diperbarui.');
    }
}
