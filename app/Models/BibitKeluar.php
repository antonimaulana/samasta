<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BibitKeluar extends Model
{
    public const PERUNTUKAN = ['Penanaman Awal', 'Pergantian', 'Perbanyakan'];

    protected $table = 'bibit_keluars';

    protected $fillable = [
        'bibit_id',
        'jumlah',
        'tanggal_keluar',
        'peruntukan',
        'taman_id',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'tanggal_keluar' => 'date',
        ];
    }

    public function bibit(): BelongsTo
    {
        return $this->belongsTo(Bibit::class);
    }

    public function taman(): BelongsTo
    {
        return $this->belongsTo(Taman::class);
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto) {
            return null;
        }

        return asset('storage/'.$this->foto);
    }
}
