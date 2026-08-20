<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemeliharaanTamanArmada extends Model
{
    /** @var list<string> */
    public const SOPIR_OPTIONS = [
        'Misriwahyudi',
        'Darmani',
        'Hisar',
        'Hendrik',
        'Zazid',
        'Eko',
    ];

    protected $fillable = [
        'pemeliharaan_taman_id',
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

    public function pemeliharaanTaman(): BelongsTo
    {
        return $this->belongsTo(PemeliharaanTaman::class);
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
        $sopir = e($this->sopir);

        return $label.' (Sopir: <span class="sopir-name">'.$sopir.'</span>)';
    }
}
