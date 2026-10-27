<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemeliharaanTamanArmada extends Model
{
    protected $fillable = [
        'pemeliharaan_taman_id',
        'armada_id',
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

    public function pemeliharaanTaman(): BelongsTo
    {
        return $this->belongsTo(PemeliharaanTaman::class);
    }

    public function armada(): BelongsTo
    {
        return $this->belongsTo(Armada::class);
    }

    public function namaArmadaLabel(): string
    {
        $nama = $this->armada?->nama;

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
        $sopir = e($this->sopir);

        return $label.' (Sopir: <span class="sopir-name">'.$sopir.'</span>)';
    }
}
