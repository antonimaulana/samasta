<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BibitMasuk extends Model
{
    protected $table = 'bibit_masuks';

    protected $fillable = [
        'bibit_id',
        'jumlah',
        'sisa_stok',
        'tanggal_masuk',
        'sumber',
        'status_siap_tanam',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'sisa_stok' => 'integer',
            'tanggal_masuk' => 'date',
            'status_siap_tanam' => 'boolean',
        ];
    }

    public function bibit(): BelongsTo
    {
        return $this->belongsTo(Bibit::class);
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
