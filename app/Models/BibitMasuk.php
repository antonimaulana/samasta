<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BibitMasuk extends Model
{
    protected $table = 'bibit_masuks';

    protected $fillable = [
        'bibit_masuk_batch_id',
        'bibit_id',
        'jumlah',
        'sisa_stok',
        'sumber',
        'status_siap_tanam',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'sisa_stok' => 'integer',
            'status_siap_tanam' => 'boolean',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(BibitMasukBatch::class, 'bibit_masuk_batch_id');
    }

    public function bibit(): BelongsTo
    {
        return $this->belongsTo(Bibit::class);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeWhereBatchTanggalBetween($query, $awal, $akhir)
    {
        return $query->whereHas('batch', fn ($batch) => $batch->whereBetween('tanggal_masuk', [$awal, $akhir]));
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeWhereBatchDate($query, $date)
    {
        return $query->whereHas('batch', fn ($batch) => $batch->whereDate('tanggal_masuk', $date));
    }
}
