<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnsiklopediaPortalSetting;
use App\Support\EnsiklopediaPortalConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnsiklopediaPortalController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.manage');
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = EnsiklopediaPortalSetting::query()->firstOrCreate([]);

        $validated = $request->validate([
            'hero_badge' => ['required', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:120'],
            'hero_description' => ['required', 'string', 'max:2000'],
            'search_placeholder' => ['required', 'string', 'max:160'],
            'interaktif_title' => ['required', 'string', 'max:160'],
            'interaktif_subtitle' => ['required', 'string', 'max:80'],
            'quiz_button_label' => ['required', 'string', 'max:120'],
            'musim_kemarau_label' => ['required', 'string', 'max:80'],
            'musim_kemarau_icon' => ['required', 'string', 'max:10'],
            'musim_kemarau_deskripsi' => ['required', 'string', 'max:500'],
            'musim_hujan_label' => ['required', 'string', 'max:80'],
            'musim_hujan_icon' => ['required', 'string', 'max:10'],
            'musim_hujan_deskripsi' => ['required', 'string', 'max:500'],
            'fakta_edukasi_text' => ['required', 'string', 'max:8000'],
            'tips_berkebun' => ['required', 'array', 'min:1'],
            'tips_berkebun.*.icon' => ['required', 'string', 'max:10'],
            'tips_berkebun.*.judul' => ['required', 'string', 'max:120'],
            'tips_berkebun.*.isi' => ['required', 'string', 'max:500'],
            'tips_kemarau' => ['required', 'array', 'min:1'],
            'tips_kemarau.*.icon' => ['required', 'string', 'max:10'],
            'tips_kemarau.*.judul' => ['required', 'string', 'max:120'],
            'tips_kemarau.*.isi' => ['required', 'string', 'max:500'],
            'tips_hujan' => ['required', 'array', 'min:1'],
            'tips_hujan.*.icon' => ['required', 'string', 'max:10'],
            'tips_hujan.*.judul' => ['required', 'string', 'max:120'],
            'tips_hujan.*.isi' => ['required', 'string', 'max:500'],
            'kuis' => ['required', 'array', 'min:1'],
            'kuis.*.pertanyaan' => ['required', 'string', 'max:500'],
            'kuis.*.opsi' => ['required', 'array', 'min:2'],
            'kuis.*.opsi.*' => ['required', 'string', 'max:200'],
            'kuis.*.jawaban' => ['required', 'integer', 'min:0'],
            'kuis.*.penjelasan' => ['required', 'string', 'max:500'],
        ]);

        $tipsBerkebun = array_values($validated['tips_berkebun']);
        $tipsKemarau = array_values($validated['tips_kemarau']);
        $tipsHujan = array_values($validated['tips_hujan']);
        $kuis = array_values($validated['kuis']);

        $fakta = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validated['fakta_edukasi_text']) ?: [])));

        $existing = EnsiklopediaPortalConfig::interaktifDefaults();
        $interaktifConfig = array_replace_recursive($existing, [
            'musim_kemarau' => [
                'label' => $validated['musim_kemarau_label'],
                'icon' => $validated['musim_kemarau_icon'],
                'deskripsi' => $validated['musim_kemarau_deskripsi'],
                'warna' => 'amber',
            ],
            'musim_hujan' => [
                'label' => $validated['musim_hujan_label'],
                'icon' => $validated['musim_hujan_icon'],
                'deskripsi' => $validated['musim_hujan_deskripsi'],
                'warna' => 'sky',
            ],
            'tips_musim' => [
                'kemarau' => $tipsKemarau,
                'hujan' => $tipsHujan,
            ],
            'tips_berkebun' => $tipsBerkebun,
            'fakta_edukasi' => $fakta,
            'kuis' => $kuis,
        ]);

        $setting->update([
            'hero_badge' => $validated['hero_badge'],
            'hero_title' => $validated['hero_title'],
            'hero_description' => $validated['hero_description'],
            'search_placeholder' => $validated['search_placeholder'],
            'interaktif_title' => $validated['interaktif_title'],
            'interaktif_subtitle' => $validated['interaktif_subtitle'],
            'quiz_button_label' => $validated['quiz_button_label'],
            'interaktif_config' => $interaktifConfig,
        ]);

        EnsiklopediaPortalConfig::forgetCache();

        return redirect()
            ->route('admin.ensiklopedia-pengaturan.index', ['tab' => 'portal'])
            ->with('success', 'Pengaturan portal ensiklopedia berhasil disimpan.');
    }
}
