<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class DpaTahunAnggaran extends Model
{
    protected $fillable = [
        'tahun',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
        ];
    }

    public function dpas(): HasMany
    {
        return $this->hasMany(Dpa::class);
    }

    public function paketPekerjaans(): HasMany
    {
        return $this->hasManyThrough(DpaPaketPekerjaan::class, Dpa::class);
    }
}
