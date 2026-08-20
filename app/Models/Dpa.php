<?php

namespace App\Models;

use App\Support\DpaMonitoring;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dpa extends Model
{
    protected $fillable = [
        'dpa_tahun_anggaran_id',
        'sub_kegiatan',
        'nomor_dpa',
        'nama_dpa',
        'keterangan',
    ];

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(DpaTahunAnggaran::class, 'dpa_tahun_anggaran_id');
    }

    public function paketPekerjaans(): HasMany
    {
        return $this->hasMany(DpaPaketPekerjaan::class)->orderBy('nama_paket');
    }

    public function subKegiatanLabel(): string
    {
        return DpaMonitoring::subKegiatanLabel($this->sub_kegiatan);
    }
}
