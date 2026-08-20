<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class SurveyKepuasan extends Model
{
    protected $table = 'survey_kepuasans';

    public const KATEGORI = [
        'Operasional Pertamanan',
        'Kondisi Taman',
        'Respon Aduan',
        'Portal Samasta',
    ];

    public static function kategoriLabel(?string $kategori): string
    {
        return match ($kategori) {
            'Layanan Pertamanan' => 'Operasional Pertamanan',
            'Kinerja Pertamanan' => 'Operasional Pertamanan',
            'Portal Batam Tumbuh' => 'Portal Samasta',
            default => $kategori ?? '',
        };
    }

    protected $fillable = [
        'kategori',
        'rating',
        'taman_id',
        'saran',
        'nama',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function taman(): BelongsTo
    {
        return $this->belongsTo(Taman::class);
    }

    public static function ratingLabel(int $rating): string
    {
        return match ($rating) {
            5 => 'Sangat Puas',
            4 => 'Puas',
            3 => 'Cukup',
            2 => 'Kurang Puas',
            default => 'Tidak Puas',
        };
    }

    public static function ratingStars(int $rating): string
    {
        return str_repeat('★', $rating).str_repeat('☆', 5 - $rating);
    }

    /**
     * @return array{total: int, average: float, distribution: array<int, int>, by_kategori: Collection<int, array{kategori: string, total: int, average: float}>}
     */
    public static function summary(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): array
    {
        $query = static::query();

        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        $total = (clone $query)->count();
        $average = round((float) (clone $query)->avg('rating'), 1);

        $distribution = collect(range(1, 5))->mapWithKeys(function (int $star) use ($query) {
            return [$star => (clone $query)->where('rating', $star)->count()];
        })->all();

        $byKategori = collect(self::KATEGORI)->map(function (string $kategori) use ($query) {
            $categoryQuery = (clone $query)->where('kategori', $kategori);
            $count = $categoryQuery->count();

            return [
                'kategori' => $kategori,
                'total' => $count,
                'average' => $count > 0 ? round((float) $categoryQuery->avg('rating'), 1) : 0.0,
            ];
        });

        return [
            'total' => $total,
            'average' => $average,
            'distribution' => $distribution,
            'by_kategori' => $byKategori,
        ];
    }
}
