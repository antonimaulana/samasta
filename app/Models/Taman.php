<?php

namespace App\Models;

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
        'RTH Jalur Hijau',
        'TPU',
        'Kebun Raya',
    ];

    public static function kategoriPinColor(string $kategori): string
    {
        return match ($kategori) {
            'Taman Kota' => '#16a34a',
            'Taman Lingkungan' => '#0891b2',
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
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'fasilitas' => 'array',
            'luasan' => 'integer',
        ];
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
