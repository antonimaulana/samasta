<?php

namespace App\Models;

use App\Casts\OperasionalDateTime;
use App\Models\Concerns\HasBibitStokBuktiFotos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BibitMasukBatch extends Model
{
    use HasBibitStokBuktiFotos;

    protected $fillable = [
        'tanggal_masuk',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => OperasionalDateTime::class,
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(BibitMasuk::class, 'bibit_masuk_batch_id');
    }

    public function totalJumlah(): int
    {
        if ($this->relationLoaded('items')) {
            return (int) $this->items->sum('jumlah');
        }

        return (int) $this->items()->sum('jumlah');
    }
}
