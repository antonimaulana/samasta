<?php

namespace App\Support;

use App\Models\Kelurahan;

class KelurahanResolver
{
    public static function findByNames(?string $kecamatan, ?string $kelurahan): ?Kelurahan
    {
        $kecamatan = trim((string) $kecamatan);
        $kelurahan = trim((string) $kelurahan);

        if ($kecamatan === '' || $kelurahan === '') {
            return null;
        }

        return Kelurahan::query()
            ->whereHas('kecamatan', fn ($query) => $query->whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($kecamatan)]))
            ->whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($kelurahan)])
            ->first();
    }

    public static function findByCoordinates(float $latitude, float $longitude, ?WilayahGeocoder $geocoder = null): ?Kelurahan
    {
        $geocoder ??= app(WilayahGeocoder::class);
        $result = $geocoder->reverse($latitude, $longitude);

        if ($result === null) {
            return null;
        }

        if (filled($result['kelurahan']) && filled($result['kecamatan'])) {
            $exact = self::findByNames($result['kecamatan'], $result['kelurahan']);

            if ($exact) {
                return $exact;
            }
        }

        if (filled($result['kelurahan'])) {
            $byKelurahan = self::findByKelurahanName($result['kelurahan'], $result['kecamatan'] ?? null);

            if ($byKelurahan) {
                return $byKelurahan;
            }
        }

        if (filled($result['kecamatan']) && filled($result['kelurahan'])) {
            return self::findByFuzzyNames($result['kecamatan'], $result['kelurahan']);
        }

        return null;
    }

    public static function findByKelurahanName(string $kelurahanName, ?string $kecamatanName = null): ?Kelurahan
    {
        $kelurahanName = self::normalizeName($kelurahanName);
        $kecamatanName = $kecamatanName ? self::normalizeName($kecamatanName) : null;

        if ($kelurahanName === '') {
            return null;
        }

        $query = Kelurahan::query()
            ->whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($kelurahanName)]);

        if ($kecamatanName) {
            $query->whereHas('kecamatan', fn ($q) => $q->whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($kecamatanName)]));
        }

        $match = $query->first();

        if ($match || ! $kecamatanName) {
            return $match;
        }

        return Kelurahan::query()
            ->whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($kelurahanName)])
            ->first();
    }

    public static function findByFuzzyNames(string $kecamatanName, string $kelurahanName): ?Kelurahan
    {
        $kecamatanName = mb_strtolower(self::normalizeName($kecamatanName));
        $kelurahanName = mb_strtolower(self::normalizeName($kelurahanName));

        if ($kecamatanName === '' || $kelurahanName === '') {
            return null;
        }

        return Kelurahan::query()
            ->whereHas('kecamatan', fn ($query) => $query->whereRaw('LOWER(TRIM(nama)) LIKE ?', ['%'.$kecamatanName.'%']))
            ->whereRaw('LOWER(TRIM(nama)) LIKE ?', ['%'.$kelurahanName.'%'])
            ->first();
    }

    private static function normalizeName(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/^(kelurahan|kel\.?|desa|kecamatan|kec\.?)\s+/iu', '', $value) ?? $value;

        return trim($value);
    }
}
