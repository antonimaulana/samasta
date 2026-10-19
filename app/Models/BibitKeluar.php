<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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

    public function hasFotoPath(): bool
    {
        return filled($this->foto);
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (! filled($this->foto)) {
            return null;
        }

        $path = str_replace('\\', '/', (string) $this->foto);

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
