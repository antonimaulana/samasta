<?php

namespace App\Support;

class ParkNavigation
{
    public static function googleDirectionsUrl(float $lat, float $lng, ?float $originLat = null, ?float $originLng = null): string
    {
        $params = [
            'api' => '1',
            'destination' => $lat.','.$lng,
        ];

        if ($originLat !== null && $originLng !== null) {
            $params['origin'] = $originLat.','.$originLng;
        }

        return 'https://www.google.com/maps/dir/?'.http_build_query($params);
    }

    public static function googleMapsSearchUrl(string $query): string
    {
        return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($query);
    }

    public static function estimateTravelMinutes(float $distanceKm, float $avgSpeedKmh = 30): int
    {
        return max(1, (int) round(($distanceKm / $avgSpeedKmh) * 60));
    }
}
