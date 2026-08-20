<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto) {
            return null;
        }

        return asset('storage/'.$this->foto);
    }
}
