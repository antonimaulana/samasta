<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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

    public function getUrlAttribute(): ?string
    {
        if (! filled($this->path_foto)) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->path_foto)) {
            return null;
        }

        return asset('storage/'.$this->path_foto);
    }
}
