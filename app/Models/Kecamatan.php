<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    protected $fillable = [
        'nama',
        'kode_kemendagri',
    ];

    public function kelurahans(): HasMany
    {
        return $this->hasMany(Kelurahan::class)->orderBy('nama');
    }

    public function tamans(): HasMany
    {
        return $this->hasManyThrough(Taman::class, Kelurahan::class);
    }
}
