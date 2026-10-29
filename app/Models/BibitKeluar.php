<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BibitKeluar extends Model
{
    protected $table = 'bibit_keluars';

    protected $fillable = [
        'bibit_keluar_batch_id',
        'bibit_id',
        'jumlah',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(BibitKeluarBatch::class, 'bibit_keluar_batch_id');
    }

    public function bibit(): BelongsTo
    {
        return $this->belongsTo(Bibit::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeWhereBatchTanggalBetween(Builder $query, $awal, $akhir): Builder
    {
        return $query->whereHas('batch', fn (Builder $batch) => $batch->whereBetween('tanggal_keluar', [$awal, $akhir]));
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeWhereBatchDate(Builder $query, $date): Builder
    {
        return $query->whereHas('batch', fn (Builder $batch) => $batch->whereDate('tanggal_keluar', $date));
    }
}
