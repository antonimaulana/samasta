<?php

return [
    'geocoder' => [
        'enabled' => env('WILAYAH_GEOCODER_ENABLED', true),
        'nominatim_url' => env('NOMINATIM_URL', 'https://nominatim.openstreetmap.org/reverse'),
        'user_agent' => env('NOMINATIM_USER_AGENT', env('APP_NAME', 'Samasta').' Geocoder'),
        'cache_ttl' => (int) env('WILAYAH_GEOCODER_CACHE_TTL', 60 * 60 * 24 * 30),
        'timeout' => (int) env('WILAYAH_GEOCODER_TIMEOUT', 8),
    ],

    /*
     * Perkiraan bounding box Kota Batam untuk validasi koordinat.
     */
    'batam_bounds' => [
        'min_lat' => -1.25,
        'max_lat' => -0.75,
        'min_lng' => 103.85,
        'max_lng' => 104.65,
    ],
];
