<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Petugas extends Model
{
    protected $table = 'petugas';

    protected $fillable = [
        'tim_pelaksana_id',
        'nama',
        'jabatan',
        'is_inti',
        'is_pengawas',
        'aktif',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'is_inti' => 'boolean',
            'is_pengawas' => 'boolean',
            'aktif' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function timPelaksana(): BelongsTo
    {
        return $this->belongsTo(TimPelaksana::class);
    }

    public function pemeliharaanTamans(): BelongsToMany
    {
        return $this->belongsToMany(PemeliharaanTaman::class, 'pemeliharaan_taman_petugas');
    }

    public function pemangkasanProgres(): BelongsToMany
    {
        return $this->belongsToMany(PemangkasanProgres::class, 'pemangkasan_progres_petugas');
    }

    public function displayLabel(): string
    {
        if (filled($this->jabatan)) {
            return $this->nama.' ('.$this->jabatan.')';
        }

        return $this->nama;
    }
}
