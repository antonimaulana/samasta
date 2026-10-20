<?php

namespace App\Support;

use App\Models\EnsiklopediaPortalSetting;
use Illuminate\Support\Facades\Cache;

class EnsiklopediaPortalConfig
{
    private const CACHE_KEY = 'ensiklopedia_portal_settings';

    private const CACHE_MINUTES = 30;

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function record(): ?EnsiklopediaPortalSetting
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(self::CACHE_MINUTES),
            fn () => EnsiklopediaPortalSetting::query()->first()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function interaktifDefaults(): array
    {
        return [
            'musim_kemarau' => [
                'label' => 'Musim Kemarau',
                'icon' => '☀️',
                'deskripsi' => 'Periode lebih kering — fokus pada penyiraman efisien, mulsa, dan perlindungan tanaman dari panas.',
                'warna' => 'amber',
            ],
            'musim_hujan' => [
                'label' => 'Musim Hujan',
                'icon' => '🌧️',
                'deskripsi' => 'Periode curah hujan tinggi — perhatikan drainase, cegah genangan, dan manfaatkan air hujan.',
                'warna' => 'sky',
            ],
            'artikel_slugs_musim' => [
                'kemarau' => EnsiklopediaInteraktif::artikelSlugsMusimStatic('kemarau'),
                'hujan' => EnsiklopediaInteraktif::artikelSlugsMusimStatic('hujan'),
            ],
            'tips_musim' => [
                'kemarau' => EnsiklopediaInteraktif::tipsMusimStatic('kemarau'),
                'hujan' => EnsiklopediaInteraktif::tipsMusimStatic('hujan'),
            ],
            'tips_berkebun' => EnsiklopediaInteraktif::tipsBerkebunStatic(),
            'fakta_edukasi' => EnsiklopediaInteraktif::faktaEdukasiStatic(),
            'kuis' => EnsiklopediaInteraktif::kuisStatic(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function interaktif(): array
    {
        $stored = self::record()?->interaktif_config ?? [];

        return array_replace_recursive(self::interaktifDefaults(), $stored);
    }

    public static function heroBadge(): string
    {
        return self::record()?->hero_badge
            ?? '📚 Pusat Pengetahuan Hijau';
    }

    public static function heroTitle(): string
    {
        return self::record()?->hero_title ?? 'Ensiklopedia';
    }

    public static function heroDescription(): string
    {
        return self::record()?->hero_description
            ?? 'Pelajari program pemerintah, jenis tanaman, dan tips penghijauan — sumber edukasi resmi seputar pertamanan & RTH Kota Batam.';
    }

    public static function searchPlaceholder(): string
    {
        return self::record()?->search_placeholder
            ?? 'Cari artikel, topik, atau kata kunci...';
    }

    public static function interaktifTitle(): string
    {
        return self::record()?->interaktif_title ?? 'Belajar Sambil Berinteraksi';
    }

    public static function interaktifSubtitle(): string
    {
        return self::record()?->interaktif_subtitle ?? 'Interaktif';
    }

    public static function quizButtonLabel(): string
    {
        return self::record()?->quiz_button_label ?? '🧠 Mulai Kuis Tanaman';
    }

    /**
     * @return array<string, mixed>
     */
    public static function heroFormDefaults(): array
    {
        return [
            'hero_badge' => self::heroBadge(),
            'hero_title' => self::heroTitle(),
            'hero_description' => self::heroDescription(),
            'search_placeholder' => self::searchPlaceholder(),
            'interaktif_title' => self::interaktifTitle(),
            'interaktif_subtitle' => self::interaktifSubtitle(),
            'quiz_button_label' => self::quizButtonLabel(),
        ];
    }
}
