<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnsiklopediaKategori extends Model
{
    protected $fillable = [
        'nama',
        'slug',
        'icon',
        'deskripsi',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function artikels(): HasMany
    {
        return $this->hasMany(EnsiklopediaArtikel::class)->orderBy('urutan');
    }

    public function artikelsPublished(): HasMany
    {
        return $this->hasMany(EnsiklopediaArtikel::class)
            ->where('is_published', true)
            ->orderBy('urutan');
    }
}
