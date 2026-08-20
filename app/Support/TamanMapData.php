<?php

namespace App\Support;

use App\Models\Taman;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TamanMapData
{
    /**
     * @param  Builder<Taman>  $query
     * @return Collection<int, array<string, mixed>>
     */
    public static function mapPoints(Builder $query, string $cacheKey): Collection
    {
        return Cache::remember(
            'taman_map_points:'.$cacheKey,
            now()->addMinutes(10),
            function () use ($query) {
                return $query
                    ->select([
                        'id',
                        'nama_taman',
                        'kategori',
                        'latitude',
                        'longitude',
                        'alamat',
                        'fasilitas',
                        'foto',
                    ])
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->with(['images' => fn ($q) => $q->latest()->limit(1)])
                    ->get()
                    ->map(fn (Taman $taman) => [
                        'id' => $taman->id,
                        'nama' => $taman->nama_taman,
                        'kategori' => $taman->kategori,
                        'lat' => (float) $taman->latitude,
                        'lng' => (float) $taman->longitude,
                        'url' => route('tamans.show', $taman),
                        'alamat' => $taman->alamat,
                        'foto' => $taman->foto_url,
                        'fasilitas' => $taman->fasilitas ?? [],
                        'pinColor' => Taman::kategoriPinColor($taman->kategori),
                        'directionsUrl' => ParkNavigation::googleDirectionsUrl(
                            (float) $taman->latitude,
                            (float) $taman->longitude
                        ),
                    ])
                    ->values();
            }
        );
    }

    public static function forgetCache(): void
    {
        // Pattern-based forget not available; individual keys expire in 10 minutes.
        // Full flush handled via KontenBerandaCache when taman changes.
    }
}
