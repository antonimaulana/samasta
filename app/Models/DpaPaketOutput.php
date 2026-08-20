<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DpaPaketOutput extends Model
{
    protected $fillable = [
        'dpa_paket_pekerjaan_id',
        'judul',
        'keterangan',
        'file_path',
    ];

    public function paketPekerjaan(): BelongsTo
    {
        return $this->belongsTo(DpaPaketPekerjaan::class, 'dpa_paket_pekerjaan_id');
    }
}
