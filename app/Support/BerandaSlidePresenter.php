<?php

namespace App\Support;

use App\Models\BerandaSlide;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class BerandaSlidePresenter
{
    /**
     * @param  array<string, mixed>  $homeStats
     * @param  Collection<int, \App\Models\EnsiklopediaKategori>|null  $ensiklopediaKategoris
     */
    public function present(BerandaSlide $slide, array $homeStats, ?Collection $ensiklopediaKategoris = null): array
    {
        $props = [
            'slide' => $slide,
            'href' => $this->resolveHref($slide),
            'titleAttr' => $slide->title_attr,
        ];

        if ($slide->jenis === BerandaSlide::JENIS_SIMPONI) {
            $props['href'] = $slide->config['external_url'] ?? $slide->href ?? 'https://simponi.batam.go.id';

            return $props;
        }

        if ($slide->jenis === BerandaSlide::JENIS_PERDA) {
            return $props;
        }

        $props = array_merge($props, [
            'leftGradient' => $slide->left_gradient ?? 'from-lime-400 via-green-400 to-emerald-500',
            'badge' => $slide->badge ?? '',
            'heroMode' => $slide->hero_mode ?? 'single',
            'showStats' => $slide->show_stats,
            'heading' => $slide->heading,
            'headingHover' => $slide->heading_hover ?? 'group-hover:text-green-600',
            'description' => $slide->description,
            'tags' => $this->resolveTags($slide, $ensiklopediaKategoris),
            'tagClasses' => $slide->tag_classes ?? 'bg-lime-100 text-green-700 ring-lime-200',
            'ctaText' => $slide->cta_text,
            'ctaColor' => $slide->cta_color ?? 'text-green-600',
            'cardRing' => $slide->card_ring ?? 'ring-green-100',
            'cardShadow' => $slide->card_shadow ?? 'shadow-green-200/60 hover:shadow-green-300/50',
        ]);

        $this->applyLivePreset($props, $slide, $homeStats, $ensiklopediaKategoris);

        if ($props['heroMode'] === 'dual' && empty($slide->hero_live_preset)) {
            $props['heroDualLeftValue'] = $slide->hero_dual_left_value ?? '';
            $props['heroDualLeftLabel'] = $slide->hero_dual_left_label ?? '';
            $props['heroDualRightValue'] = $slide->hero_dual_right_value ?? '';
            $props['heroDualRightLabel'] = $slide->hero_dual_right_label ?? '';
        } elseif ($props['heroMode'] !== 'dual') {
            $props['heroValue'] = $props['heroValue'] ?? $slide->hero_value ?? '';
            $props['heroLabel'] = $props['heroLabel'] ?? $slide->hero_label ?? '';
        }

        if ($props['showStats'] && empty($slide->hero_live_preset)) {
            $props['statLeftValue'] = $slide->stat_left_value ?? '';
            $props['statLeftLabel'] = $slide->stat_left_label ?? '';
            $props['statRightValue'] = $slide->stat_right_value ?? '';
            $props['statRightLabel'] = $slide->stat_right_label ?? '';
        }

        return $props;
    }

    private function resolveHref(BerandaSlide $slide): string
    {
        if ($slide->route_name && Route::has($slide->route_name)) {
            return route($slide->route_name);
        }

        return $slide->href ?? '#';
    }

    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, mixed>  $homeStats
     * @param  Collection<int, \App\Models\EnsiklopediaKategori>|null  $ensiklopediaKategoris
     */
    private function applyLivePreset(array &$props, BerandaSlide $slide, array $homeStats, ?Collection $ensiklopediaKategoris): void
    {
        $rthKategoriCount = count(app(PublicRthStatisticsBuilder::class)->build()['kategoriCards'] ?? []);

        match ($slide->hero_live_preset) {
            'rth' => $this->applyRthLive($props, $homeStats, $rthKategoriCount),
            'operasional_dual' => $this->applyOperasionalDual($props, $homeStats),
            'ensiklopedia' => $this->applyEnsiklopediaLive($props, $homeStats, $ensiklopediaKategoris),
            'aduan' => $this->applyAduanLive($props, $homeStats),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, mixed>  $homeStats
     */
    private function applyRthLive(array &$props, array $homeStats, int $rthKategoriCount): void
    {
        $rth = app(PublicRthStatisticsBuilder::class)->build();
        $props['heroValue'] = number_format($rth['totalLuasan'], 0, ',', '.');
        $props['heroLabel'] = 'M² luas RTH terpelihara';
        $props['statLeftValue'] = number_format($rth['totalTaman']);
        $props['statLeftLabel'] = 'Lokasi';
        $props['statRightValue'] = (string) $rthKategoriCount;
        $props['statRightLabel'] = 'Kategori';
    }

    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, mixed>  $homeStats
     */
    private function applyOperasionalDual(array &$props, array $homeStats): void
    {
        $props['heroMode'] = 'dual';
        $props['heroDualLeftValue'] = number_format($homeStats['operasionalPemangkasanSelesai'] ?? 0);
        $props['heroDualLeftLabel'] = 'Pemangkasan Pohon';
        $props['heroDualRightValue'] = number_format($homeStats['operasionalPohonTumbangSelesai'] ?? 0);
        $props['heroDualRightLabel'] = 'Penanganan Pohon Tumbang';
    }

    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, mixed>  $homeStats
     * @param  Collection<int, \App\Models\EnsiklopediaKategori>|null  $ensiklopediaKategoris
     */
    private function applyEnsiklopediaLive(array &$props, array $homeStats, ?Collection $ensiklopediaKategoris): void
    {
        $props['heroValue'] = number_format($homeStats['ensiklopediaTotalArtikel'] ?? 0);
        $props['heroLabel'] = 'Artikel edukasi tersedia';
        $props['statLeftValue'] = $ensiklopediaKategoris?->count() ?? 0;
        $props['statLeftLabel'] = 'Topik';
        $props['statRightValue'] = '📚';
        $props['statRightLabel'] = 'Ensiklopedia';
    }

    /**
     * @param  array<string, mixed>  $props
     * @param  array<string, mixed>  $homeStats
     */
    private function applyAduanLive(array &$props, array $homeStats): void
    {
        $props['heroValue'] = number_format($homeStats['aduanTotal'] ?? 0);
        $props['heroLabel'] = 'Aduan masuk';
        $props['statLeftValue'] = number_format($homeStats['aduanSelesai'] ?? 0);
        $props['statLeftLabel'] = 'Selesai';
        $props['statRightValue'] = number_format($homeStats['aduanBulanIni'] ?? 0);
        $props['statRightLabel'] = 'Bulan ini';
    }

    /**
     * @param  Collection<int, \App\Models\EnsiklopediaKategori>|null  $ensiklopediaKategoris
     * @return list<string>
     */
    private function resolveTags(BerandaSlide $slide, ?Collection $ensiklopediaKategoris): array
    {
        if ($slide->slug === 'ensiklopedia' && $ensiklopediaKategoris && $ensiklopediaKategoris->isNotEmpty()) {
            $fromDb = $ensiklopediaKategoris
                ->map(fn ($kat) => trim(($kat->icon ?? '').' '.$kat->nama))
                ->all();

            if ($fromDb !== []) {
                return $fromDb;
            }
        }

        return $slide->tags ?? [];
    }
}
