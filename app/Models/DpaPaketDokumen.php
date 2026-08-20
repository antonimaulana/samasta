<?php

namespace App\Models;

use App\Support\DpaMonitoring;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DpaPaketDokumen extends Model
{
    protected $fillable = [
        'dpa_paket_pekerjaan_id',
        'tahap',
        'kode_dokumen',
        'file_path',
        'input_data',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'input_data' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function paketPekerjaan(): BelongsTo
    {
        return $this->belongsTo(DpaPaketPekerjaan::class, 'dpa_paket_pekerjaan_id');
    }

    public function dokumenLabel(): string
    {
        return DpaMonitoring::dokumenLabel($this->kode_dokumen);
    }
}
