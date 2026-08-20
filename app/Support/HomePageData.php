<?php

namespace App\Support;

use App\Models\AduanMasyarakat;
use App\Models\EnsiklopediaArtikel;
use App\Models\EnsiklopediaKategori;
use App\Models\Pemangkasan;
use App\Models\Taman;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HomePageData
{
    private const STATS_CACHE_KEY = 'homepage_stats';

    private const STATS_CACHE_MINUTES = 10;

    /**
     * @return Collection<int, Taman>
     */
    public static function featuredTamans(int $limit = 6): Collection
    {
        return Taman::query()
            ->with('images')
            ->select('tamans.*')
            ->selectRaw(
                'CASE WHEN EXISTS (SELECT 1 FROM taman_images WHERE taman_images.taman_id = tamans.id) '
                ."OR (tamans.foto IS NOT NULL AND tamans.foto != '') THEN 1 ELSE 0 END as has_photo"
            )
            ->orderByDesc('has_photo')
            ->latest('tamans.created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{
     *     totalTaman: int,
     *     operasionalPemangkasanSelesai: int,
     *     operasionalPohonTumbangSelesai: int,
     *     ensiklopediaTotalArtikel: int,
     *     aduanTotal: int,
     *     aduanSelesai: int,
     *     aduanBulanIni: int
     * }
     */
    public static function stats(): array
    {
        return Cache::remember(
            self::STATS_CACHE_KEY,
            now()->addMinutes(self::STATS_CACHE_MINUTES),
            function () {
                $now = now();

                return [
                    'totalTaman' => Taman::count(),
                    'operasionalPemangkasanSelesai' => Pemangkasan::query()
                        ->where('jenis_layanan', 'Pemangkasan Pohon')
                        ->where('status', 'Selesai')
                        ->count(),
                    'operasionalPohonTumbangSelesai' => Pemangkasan::query()
                        ->where('jenis_layanan', 'Penanganan Pohon Tumbang')
                        ->where('status', 'Selesai')
                        ->count(),
                    'ensiklopediaTotalArtikel' => EnsiklopediaArtikel::query()
                        ->where('is_published', true)
                        ->count(),
                    'aduanTotal' => AduanMasyarakat::count(),
                    'aduanSelesai' => AduanMasyarakat::query()
                        ->where('status', 'Selesai')
                        ->count(),
                    'aduanBulanIni' => AduanMasyarakat::query()
                        ->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year)
                        ->count(),
                ];
            }
        );
    }

    public static function forgetStatsCache(): void
    {
        Cache::forget(self::STATS_CACHE_KEY);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, EnsiklopediaKategori>
     */
    public static function ensiklopediaKategoris()
    {
        return Cache::remember(
            'homepage_ensiklopedia_kategoris',
            now()->addMinutes(self::STATS_CACHE_MINUTES),
            fn () => EnsiklopediaKategori::query()
                ->whereHas('artikels', fn ($q) => $q->where('is_published', true))
                ->orderBy('urutan')
                ->orderBy('nama')
                ->get()
        );
    }

    public static function forgetEnsiklopediaKategorisCache(): void
    {
        Cache::forget('homepage_ensiklopedia_kategoris');
    }
}
