<?php

namespace App\Models;

use App\Support\DpaMonitoring;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DpaPaketPekerjaan extends Model
{
    protected $fillable = [
        'dpa_id',
        'dpa_penyedia_id',
        'nomor_rekening',
        'nama_rekening',
        'nama_paket',
        'pagu_anggaran',
        'rincian_item_belanja',
        'kode_rup',
        'jenis_pengadaan',
        'metode_pemilihan',
        'anggaran_kas',
        'masa_pelaksanaan',
        'tahap',
    ];

    protected function casts(): array
    {
        return [
            'pagu_anggaran' => 'integer',
            'anggaran_kas' => 'integer',
        ];
    }

    public function dpa(): BelongsTo
    {
        return $this->belongsTo(Dpa::class);
    }

    public function penyedia(): BelongsTo
    {
        return $this->belongsTo(DpaPenyedia::class, 'dpa_penyedia_id');
    }

    public function itemBelanjas(): HasMany
    {
        return $this->hasMany(DpaPaketItemBelanja::class)->orderBy('urutan');
    }

    public function dokumens(): HasMany
    {
        return $this->hasMany(DpaPaketDokumen::class);
    }

    public function progresKontraks(): HasMany
    {
        return $this->hasMany(DpaPaketProgres::class)->orderByDesc('tanggal');
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(DpaPaketOutput::class)->orderByDesc('created_at');
    }

    public function tahapLabel(): string
    {
        return DpaMonitoring::tahapLabel($this->tahap);
    }

    public function hpsItems(): HasMany
    {
        return $this->itemBelanjas()->where('jenis_dokumen', 'hps');
    }

    public function spkItems(): HasMany
    {
        return $this->itemBelanjas()->where('jenis_dokumen', 'spk');
    }
}
