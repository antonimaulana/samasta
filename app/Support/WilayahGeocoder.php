<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WilayahGeocoder
{
    /**
     * @return array{kelurahan: ?string, kecamatan: ?string, source: string}|null
     */
    public function reverse(float $latitude, float $longitude): ?array
    {
        if (! config('wilayah.geocoder.enabled')) {
            return null;
        }

        if (! $this->isWithinBatamBounds($latitude, $longitude)) {
            return null;
        }

        $cacheKey = 'wilayah.reverse.'
            .round($latitude, 5).'.'
            .round($longitude, 5);

        return Cache::remember($cacheKey, config('wilayah.geocoder.cache_ttl'), function () use ($latitude, $longitude) {
            return $this->fetchReverse($latitude, $longitude);
        });
    }

    /**
     * @return array{kelurahan: ?string, kecamatan: ?string, source: string}|null
     */
    private function fetchReverse(float $latitude, float $longitude): ?array
    {
        try {
            $response = Http::timeout(config('wilayah.geocoder.timeout'))
                ->withHeaders([
                    'User-Agent' => config('wilayah.geocoder.user_agent'),
                    'Accept-Language' => 'id',
                ])
                ->get(config('wilayah.geocoder.nominatim_url'), [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'format' => 'json',
                    'addressdetails' => 1,
                    'zoom' => 14,
                ]);

            if (! $response->successful()) {
                return null;
            }

            /** @var array<string, mixed> $payload */
            $payload = $response->json();

            return $this->parseNominatimPayload($payload);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{kelurahan: ?string, kecamatan: ?string, source: string}|null
     */
    private function parseNominatimPayload(array $payload): ?array
    {
        $address = $payload['address'] ?? null;

        if (! is_array($address)) {
            return null;
        }

        $kelurahanCandidates = $this->candidateValues($address, [
            'village',
            'suburb',
            'neighbourhood',
            'quarter',
            'hamlet',
            'city_block',
            'residential',
        ]);

        $kecamatanCandidates = $this->candidateValues($address, [
            'city_district',
            'district',
            'county',
            'municipality',
            'subdistrict',
        ]);

        $kelurahan = $kelurahanCandidates[0] ?? null;
        $kecamatan = $kecamatanCandidates[0] ?? null;

        if ($kelurahan === null && $kecamatan === null) {
            return null;
        }

        return [
            'kelurahan' => $kelurahan,
            'kecamatan' => $kecamatan,
            'source' => 'nominatim',
        ];
    }

    /**
     * @param  array<string, mixed>  $address
     * @param  list<string>  $keys
     * @return list<string>
     */
    private function candidateValues(array $address, array $keys): array
    {
        $values = [];

        foreach ($keys as $key) {
            $value = $address[$key] ?? null;

            if (! is_string($value)) {
                continue;
            }

            $normalized = $this->normalizeName($value);

            if ($normalized === '' || in_array($normalized, $values, true)) {
                continue;
            }

            $values[] = $normalized;
        }

        return $values;
    }

    private function normalizeName(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/^(kelurahan|kel\.?|desa|kecamatan|kec\.?)\s+/iu', '', $value) ?? $value;
        $value = preg_replace('/\s*,\s*.*$/', '', $value) ?? $value;

        return trim($value);
    }

    private function isWithinBatamBounds(float $latitude, float $longitude): bool
    {
        $bounds = config('wilayah.batam_bounds');

        return $latitude >= $bounds['min_lat']
            && $latitude <= $bounds['max_lat']
            && $longitude >= $bounds['min_lng']
            && $longitude <= $bounds['max_lng'];
    }
}
