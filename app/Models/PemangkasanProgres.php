<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PemangkasanProgres extends Model
{
    protected $table = 'pemangkasan_progres';

    protected $fillable = [
        'pemangkasan_id',
        'tanggal',
        'hari_ke',
        'jumlah_personil',
        'foto_sebelum',
        'foto_saat',
        'foto_sesudah',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'hari_ke' => 'integer',
            'jumlah_personil' => 'integer',
        ];
    }

    public function pemangkasan(): BelongsTo
    {
        return $this->belongsTo(Pemangkasan::class);
    }

    public function fotoUrl(string $field): ?string
    {
        $path = $this->{$field};

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return asset('storage/'.$path);
    }

    public function fotoBase64(string $field): ?string
    {
        $path = $this->{$field};

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode(Storage::disk('public')->get($path));
    }

    /**
     * @return array{width: int, height: int}|null
     */
    public function fotoPdfSize(string $field, int $maxWidth = 520, int $maxHeight = 178): ?array
    {
        $path = $this->{$field};

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $size = @getimagesize(Storage::disk('public')->path($path));

        if ($size === false) {
            return null;
        }

        [$width, $height] = $size;
        $scale = min($maxWidth / $width, $maxHeight / $height, 1);

        return [
            'width' => max(1, (int) round($width * $scale)),
            'height' => max(1, (int) round($height * $scale)),
        ];
    }
}
