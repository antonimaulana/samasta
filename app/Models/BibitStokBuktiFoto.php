<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class BibitStokBuktiFoto extends Model
{
    protected $fillable = [
        'photographable_type',
        'photographable_id',
        'path',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function photographable(): MorphTo
    {
        return $this->morphTo();
    }

    public function url(): ?string
    {
        if (! filled($this->path)) {
            return null;
        }

        $path = str_replace('\\', '/', (string) $this->path);

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
