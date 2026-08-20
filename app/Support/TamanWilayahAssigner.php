<?php

namespace App\Support;

use App\Models\Kelurahan;

class TamanWilayahAssigner
{
    public function __construct(
        private WilayahGeocoder $geocoder,
    ) {}

    public function resolveKelurahanId(?string $latitude, ?string $longitude): ?int
    {
        if (! $this->hasValidCoordinates($latitude, $longitude)) {
            return null;
        }

        $kelurahan = KelurahanResolver::findByCoordinates(
            (float) $latitude,
            (float) $longitude,
            $this->geocoder,
        );

        return $kelurahan?->id;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function apply(array $payload, bool $overwriteExisting = false): array
    {
        if (! $overwriteExisting && filled($payload['kelurahan_id'] ?? null)) {
            return $payload;
        }

        $kelurahanId = $this->resolveKelurahanId(
            isset($payload['latitude']) ? (string) $payload['latitude'] : null,
            isset($payload['longitude']) ? (string) $payload['longitude'] : null,
        );

        if ($kelurahanId !== null) {
            $payload['kelurahan_id'] = $kelurahanId;
        }

        return $payload;
    }

    private function hasValidCoordinates(?string $latitude, ?string $longitude): bool
    {
        if (! filled($latitude) || ! filled($longitude)) {
            return false;
        }

        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return false;
        }

        $lat = (float) $latitude;
        $lng = (float) $longitude;

        return $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180;
    }
}
