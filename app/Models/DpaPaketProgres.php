<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DpaPaketProgres extends Model
{
    protected $table = 'dpa_paket_progres';

    protected $fillable = [
        'dpa_paket_pekerjaan_id',
        'tanggal',
        'persentase',
        'keterangan',
        'dokumentasi_path',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'persentase' => 'integer',
        ];
    }

    public function paketPekerjaan(): BelongsTo
    {
        return $this->belongsTo(DpaPaketPekerjaan::class, 'dpa_paket_pekerjaan_id');
    }
}
