<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BerandaMascotSetting extends Model
{
    protected $fillable = [
        'popups_enabled',
    ];

    protected function casts(): array
    {
        return [
            'popups_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'popups_enabled' => true,
        ]);
    }

    public static function popupsEnabledForHome(): bool
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('beranda_mascot_settings')) {
            return true;
        }

        return static::current()->popups_enabled;
    }
}
