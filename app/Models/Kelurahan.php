<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelurahan extends Model
{
    protected $fillable = [
        'kecamatan_id',
        'nama',
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function tamans(): HasMany
    {
        return $this->hasMany(Taman::class);
    }

    public function labelWithKecamatan(): string
    {
        return $this->kecamatan?->nama
            ? $this->kecamatan->nama.' — '.$this->nama
            : $this->nama;
    }

    public function timPelaksanas(): BelongsToMany
    {
        return $this->belongsToMany(TimPelaksana::class, 'kelurahan_tim_pelaksana')
            ->withTimestamps();
    }
}
