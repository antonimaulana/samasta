<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnsiklopediaArtikel extends Model
{
    protected $fillable = [
        'ensiklopedia_kategori_id',
        'judul',
        'slug',
        'icon',
        'ringkas',
        'konten',
        'urutan',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(EnsiklopediaKategori::class, 'ensiklopedia_kategori_id');
    }
}
