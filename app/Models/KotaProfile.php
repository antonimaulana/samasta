<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KotaProfile extends Model
{
    protected $fillable = [
        'visi',
        'misi',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'visi' => '',
            'misi' => '',
        ]);
    }
}
