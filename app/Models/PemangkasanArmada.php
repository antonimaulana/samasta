<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemangkasanArmada extends Model
{
    protected $fillable = [
        'pemangkasan_id',
        'alat_sarana_operasional_id',
        'jenis_armada',
        'no_plat',
        'sopir',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function pemangkasan(): BelongsTo
    {
        return $this->belongsTo(Pemangkasan::class);
    }

    public function alatSarana(): BelongsTo
    {
        return $this->belongsTo(AlatSaranaOperasional::class, 'alat_sarana_operasional_id');
    }

    public function namaArmadaLabel(): string
    {
        $nama = $this->alatSarana?->nama;

        if (filled($nama)) {
            return $nama.' - '.$this->jenis_armada;
        }

        if (filled($this->no_plat)) {
            return $this->no_plat.' - '.$this->jenis_armada;
        }

        return $this->jenis_armada;
    }

    public function pdfEntryHtml(): string
    {
        $label = e($this->namaArmadaLabel());

        if (filled($this->sopir)) {
            return $label.' <span class="sopir-name">('.e($this->sopir).')</span>';
        }

        return $label;
    }
}
