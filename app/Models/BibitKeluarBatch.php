<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class BibitKeluarBatch extends Model
{
    public const PERUNTUKAN = [
        'Penanaman Awal',
        'Pergantian',
        'Perbanyakan',
        'Permintaan Masyarakat/Instansi',
    ];

    protected $fillable = [
        'tanggal_keluar',
        'peruntukan',
        'taman_id',
        'lokasi',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_keluar' => 'date',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(BibitKeluar::class, 'bibit_keluar_batch_id');
    }

    public function taman(): BelongsTo
    {
        return $this->belongsTo(Taman::class);
    }

    public function lokasiLabel(): string
    {
        if (filled($this->lokasi)) {
            return (string) $this->lokasi;
        }

        if ($this->taman_id) {
            $taman = $this->relationLoaded('taman')
                ? $this->taman
                : $this->taman()->first(['id', 'nama_taman', 'alamat']);

            if ($taman) {
                return PemeliharaanTaman::lokasiLabelFromTaman($taman);
            }
        }

        return '—';
    }

    public function totalJumlah(): int
    {
        if ($this->relationLoaded('items')) {
            return (int) $this->items->sum('jumlah');
        }

        return (int) $this->items()->sum('jumlah');
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
