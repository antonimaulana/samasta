<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NearbyTamanPaginator
{
    private const DEFAULT_RADIUS_KM = 100;

    /**
     * @param  Builder<\App\Models\Taman>  $query
     */
    public static function paginate(Builder $query, float $userLat, float $userLng, Request $request, int $perPage = 12): LengthAwarePaginator
    {
        $query->whereNotNull('latitude')->whereNotNull('longitude');
        self::applyBoundingBox($query, $userLat, $userLng, self::DEFAULT_RADIUS_KM);

        /** @var Collection<int, \App\Models\Taman> $sorted */
        $sorted = $query->get()
            ->map(function ($taman) use ($userLat, $userLng) {
                $taman->distance_km = GeoDistance::haversineKm(
                    $userLat,
                    $userLng,
                    (float) $taman->latitude,
                    (float) $taman->longitude
                );

                return $taman;
            })
            ->filter(fn ($taman) => $taman->distance_km <= self::DEFAULT_RADIUS_KM)
            ->sortBy('distance_km')
            ->values();

        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $sorted->forPage($page, $perPage)->values(),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /**
     * @param  Builder<\App\Models\Taman>  $query
     */
    private static function applyBoundingBox(Builder $query, float $lat, float $lng, float $radiusKm): void
    {
        $latDelta = $radiusKm / 111.0;
        $lngDelta = $radiusKm / max(0.1, 111.0 * cos(deg2rad($lat)));

        $query->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
            ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta]);
    }
}
