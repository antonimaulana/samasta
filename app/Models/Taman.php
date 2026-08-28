<?php

namespace App\Models;

use App\Support\TamanCompleteness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Taman extends Model
{
    protected $table = 'tamans';

    public const KATEGORI = [
        'Taman Kota',
        'Taman Lingkungan',
        'Jalur Hijau Jalan',
        'TPU',
        'Kebun Raya',
    ];

    public const FASILITAS_KONDISI = [
        'Baik',
        'Rusak Ringan',
        'Rusak Berat',
    ];

    public const FASILITAS_DAFTAR = [
        'Playground',
        'Jogging Track',
        'Area Olahraga',
        'Gazebo',
        'Bangku Taman',
        'Toilet',
        'Tempat Sampah',
        'Penerangan',
        'Papan Informasi',
        'Area Parkir',
        'Museum',
        'Pos Jaga',
    ];

    public const STATUS_DATA_LENGKAP = 'lengkap';

    public const STATUS_DATA_BELUM_LENGKAP = 'belum_lengkap';

    public const GALLERY_ASPECT_WIDTH = 16;

    public const GALLERY_ASPECT_HEIGHT = 9;

    public const GALLERY_MIN_SHORT_SIDE = 720;

    public const GALLERY_RECOMMENDED_WIDTH = 1920;

    public const GALLERY_RECOMMENDED_HEIGHT = 1080;

    public const GALLERY_MAX_SIZE_KB = 2048;

    /** Pusat Kota Batam — default peta & koordinat kosong (1°N, 104°E). */
    public const DEFAULT_LATITUDE = 1.0456;

    public const DEFAULT_LONGITUDE = 104.0305;

    public const DEFAULT_MAP_ZOOM = 13;

    public static function defaultLatitude(): string
    {
        return number_format(self::DEFAULT_LATITUDE, 8, '.', '');
    }

    public static function defaultLongitude(): string
    {
        return number_format(self::DEFAULT_LONGITUDE, 8, '.', '');
    }

    /**
     * @return array{latitude: string, longitude: string}
     */
    public static function defaultCoordinates(): array
    {
        return [
            'latitude' => self::defaultLatitude(),
            'longitude' => self::defaultLongitude(),
        ];
    }

    /**
     * @return array{latitude: string, longitude: string}
     */
    public static function applyDefaultCoordinates(?string $latitude, ?string $longitude): array
    {
        $latitude = filled(trim((string) $latitude)) ? trim((string) $latitude) : self::defaultLatitude();
        $longitude = filled(trim((string) $longitude)) ? trim((string) $longitude) : self::defaultLongitude();

        return self::normalizeBatamCoordinates($latitude, $longitude);
    }

    /**
     * @return array{latitude: string, longitude: string}
     */
    public static function normalizeBatamCoordinates(string $latitude, string $longitude): array
    {
        $lat = (float) $latitude;
        $lng = (float) $longitude;

        if (! is_finite($lat) || ! is_finite($lng)) {
            return [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
        }

        // Legacy import: latitude disimpan negatif meski Batam di utara khatulistiwa.
        if ($lat < 0 && $lng >= 100 && $lng <= 110) {
            $lat = abs($lat);
        }

        if (self::coordinatesLookSwapped($lat, $lng)) {
            [$lat, $lng] = [$lng, $lat];
        }

        [$lat, $lng] = self::fixScaledCoordinates($lat, $lng);

        return [
            'latitude' => number_format($lat, 8, '.', ''),
            'longitude' => number_format($lng, 8, '.', ''),
        ];
    }

    public static function isPlaceholderCoordinates(float $latitude, float $longitude): bool
    {
        return abs($latitude - self::DEFAULT_LATITUDE) < 0.0001
            && abs($longitude - self::DEFAULT_LONGITUDE) < 0.0001;
    }

    public static function isWithinBatamBounds(float $latitude, float $longitude): bool
    {
        $bounds = config('wilayah.batam_bounds');

        return $latitude >= $bounds['min_lat']
            && $latitude <= $bounds['max_lat']
            && $longitude >= $bounds['min_lng']
            && $longitude <= $bounds['max_lng'];
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    public function normalizedMapCoordinates(): ?array
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        $normalized = self::normalizeBatamCoordinates(
            (string) $this->latitude,
            (string) $this->longitude,
        );

        $lat = (float) $normalized['latitude'];
        $lng = (float) $normalized['longitude'];

        if (! is_finite($lat) || ! is_finite($lng)) {
            return null;
        }

        if (self::isPlaceholderCoordinates($lat, $lng)) {
            return null;
        }

        if (! self::isWithinBatamBounds($lat, $lng)) {
            return null;
        }

        return ['lat' => $lat, 'lng' => $lng];
    }

    private static function coordinatesLookSwapped(float $latitude, float $longitude): bool
    {
        $latitudeLooksLikeBatamLongitude = $latitude >= 103 && $latitude <= 105;
        $longitudeLooksLikeBatamLatitude = $longitude >= 0.5 && $longitude <= 2;

        if ($latitudeLooksLikeBatamLongitude && $longitudeLooksLikeBatamLatitude) {
            return true;
        }

        return abs($latitude) > 10 && abs($longitude) < 10;
    }

    /**
     * @return array{0: float, 1: float}
     */
    private static function fixScaledCoordinates(float $latitude, float $longitude): array
    {
        if (self::isWithinBatamBounds($latitude, $longitude)) {
            return [$latitude, $longitude];
        }

        foreach ([6, 7, 5, 4] as $power) {
            $divisor = 10 ** $power;
            $scaledLatitude = $latitude / $divisor;
            $scaledLongitude = $longitude / $divisor;

            if (self::isWithinBatamBounds($scaledLatitude, $scaledLongitude)) {
                return [$scaledLatitude, $scaledLongitude];
            }

            if (self::isWithinBatamBounds($latitude / $divisor, $longitude)) {
                return [$latitude / $divisor, $longitude];
            }

            if (self::isWithinBatamBounds($latitude, $longitude / $divisor)) {
                return [$latitude, $longitude / $divisor];
            }
        }

        return [$latitude, $longitude];
    }

    public static function galleryAspectRatioLabel(): string
    {
        return self::GALLERY_ASPECT_WIDTH.':'.self::GALLERY_ASPECT_HEIGHT;
    }

    public static function isValidGalleryAspectRatio(int $width, int $height, float $tolerance = 0.03): bool
    {
        if ($width < 1 || $height < 1 || $width < $height) {
            return false;
        }

        $ratio = $width / $height;
        $target = self::GALLERY_ASPECT_WIDTH / self::GALLERY_ASPECT_HEIGHT;

        return abs($ratio - $target) <= $target * $tolerance;
    }

    protected $fillable = [
        'nama_taman',
        'kategori',
        'kelurahan_id',
        'luasan',
        'alamat',
        'latitude',
        'longitude',
        'deskripsi',
        'fasilitas',
        'tahun_pembangunan',
        'nilai_pembangunan',
        'kontraktor',
        'konsultan_perencana',
        'data_verified_at',
        'status_data',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'fasilitas' => 'array',
            'luasan' => 'integer',
            'tahun_pembangunan' => 'integer',
            'nilai_pembangunan' => 'integer',
            'data_verified_at' => 'datetime',
        ];
    }

    public static function kategoriPinColor(string $kategori): string
    {
        return match ($kategori) {
            'Taman Kota' => '#16a34a',
            'Taman Lingkungan' => '#0891b2',
            'Jalur Hijau Jalan' => '#d97706',
            'RTH Jalur Hijau' => '#d97706',
            'TPU' => '#7c3aed',
            'Kebun Raya' => '#059669',
            default => '#16a34a',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function kategoriPinLegend(): array
    {
        return collect(self::KATEGORI)
            ->mapWithKeys(fn (string $kategori) => [$kategori => self::kategoriPinColor($kategori)])
            ->all();
    }

    /**
     * @return list<array{nama: string, kondisi: string}>
     */
    public static function normalizeFasilitasArray(?array $fasilitas): array
    {
        if ($fasilitas === null || $fasilitas === []) {
            return [];
        }

        $items = [];

        foreach ($fasilitas as $item) {
            if (is_string($item)) {
                $nama = self::resolveFasilitasNama($item) ?? trim($item);
                if ($nama !== '') {
                    $items[] = ['nama' => $nama, 'kondisi' => 'Baik'];
                }

                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $rawNama = trim((string) ($item['nama'] ?? ''));
            if ($rawNama === '') {
                continue;
            }

            $nama = self::resolveFasilitasNama($rawNama) ?? $rawNama;

            $kondisi = (string) ($item['kondisi'] ?? 'Baik');
            if (! in_array($kondisi, self::FASILITAS_KONDISI, true)) {
                $kondisi = 'Baik';
            }

            $items[] = ['nama' => $nama, 'kondisi' => $kondisi];
        }

        return collect($items)
            ->unique(fn (array $item) => mb_strtolower($item['nama']))
            ->values()
            ->all();
    }

    public static function resolveFasilitasNama(string $nama): ?string
    {
        $trimmed = trim($nama);
        if ($trimmed === '') {
            return null;
        }

        foreach (self::FASILITAS_DAFTAR as $fasilitas) {
            if (strcasecmp($fasilitas, $trimmed) === 0) {
                return $fasilitas;
            }
        }

        $aliases = [
            'area bermain' => 'Playground',
            'tempat parkir' => 'Area Parkir',
            'jogging' => 'Jogging Track',
            'penerangan' => 'Penerangan',
            'penerangan taman' => 'Penerangan',
            'papan informasi' => 'Papan Informasi',
        ];

        $key = mb_strtolower($trimmed);

        if (isset($aliases[$key])) {
            return $aliases[$key];
        }

        return null;
    }

    public function setFasilitasAttribute(mixed $value): void
    {
        $this->attributes['fasilitas'] = json_encode(self::normalizeFasilitasArray(is_array($value) ? $value : null));
    }

    /**
     * @return list<array{nama: string, kondisi: string}>
     */
    public function getFasilitasItemsAttribute(): array
    {
        return self::normalizeFasilitasArray($this->fasilitas);
    }

    /**
     * @return list<string>
     */
    public function getFasilitasNamaListAttribute(): array
    {
        return collect($this->fasilitas_items)->pluck('nama')->all();
    }

    public function getStatusDataTextAttribute(): string
    {
        return $this->status_data === self::STATUS_DATA_LENGKAP
            ? 'Lengkap'
            : 'Belum Lengkap';
    }

    public function syncStatusData(): void
    {
        $completeness = app(TamanCompleteness::class);

        $this->forceFill([
            'status_data' => $completeness->statusFor($this),
        ])->saveQuietly();
    }

    /** @deprecated Use syncStatusData() */
    public function syncKelengkapan(): void
    {
        $this->syncStatusData();
    }

    public function images(): HasMany
    {
        return $this->hasMany(TamanImage::class)->latest();
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class);
    }

    /**
     * @param  Collection<int, self>  $tamans
     * @return Collection<string, Collection<int, self>>
     */
    public static function groupByKategori(Collection $tamans): Collection
    {
        $grouped = collect(self::KATEGORI)
            ->mapWithKeys(fn (string $kategori) => [
                $kategori => $tamans->where('kategori', $kategori)->values(),
            ])
            ->filter(fn (Collection $items) => $items->isNotEmpty());

        $others = $tamans->whereNotIn('kategori', self::KATEGORI)->values();

        if ($others->isNotEmpty()) {
            $grouped->put('Lainnya', $others);
        }

        return $grouped;
    }

    public function pemeliharaans(): HasMany
    {
        return $this->hasMany(PemeliharaanTaman::class);
    }

    public function getFotoUrlAttribute(): ?string
    {
        if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
            return $this->images->first()->url;
        }

        if ($this->images()->exists()) {
            return $this->images()->first()->url;
        }

        if ($this->foto) {
            return asset('storage/'.$this->foto);
        }

        return null;
    }

    public function getGalleryUrlsAttribute(): array
    {
        $urls = $this->images->map(fn (TamanImage $image) => $image->url)->all();

        if ($urls === [] && $this->foto) {
            return [asset('storage/'.$this->foto)];
        }

        return $urls;
    }
}
