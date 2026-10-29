<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BibitKeluar extends Model
{
    public const PERUNTUKAN = [
        'Penanaman Awal',
        'Pergantian',
        'Perbanyakan',
        'Permintaan Masyarakat/Instansi',
    ];

    protected $table = 'bibit_keluars';

    protected $fillable = [
        'bibit_id',
        'jumlah',
        'tanggal_keluar',
        'peruntukan',
        'taman_id',
        'lokasi',
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
