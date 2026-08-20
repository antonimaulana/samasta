<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DpaPaketItemBelanja extends Model
{
    protected $fillable = [
        'dpa_paket_pekerjaan_id',
        'jenis_dokumen',
        'urutan',
        'uraian',
        'volume',
        'satuan',
        'harga_satuan',
        'jumlah',
    ];

    protected function casts(): array
    {
        return [
            'volume' => 'decimal:2',
            'harga_satuan' => 'integer',
            'jumlah' => 'integer',
            'urutan' => 'integer',
        ];
    }

    public function paketPekerjaan(): BelongsTo
    {
        return $this->belongsTo(DpaPaketPekerjaan::class, 'dpa_paket_pekerjaan_id');
    }
}
