<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TamanImage extends Model
{
    protected $fillable = [
        'taman_id',
        'path_foto',
    ];

    public function taman(): BelongsTo
    {
        return $this->belongsTo(Taman::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->path_foto);
    }
}
