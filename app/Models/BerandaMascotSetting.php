<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

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
        if (! Schema::hasTable('beranda_mascot_settings')) {
            return static::defaultInstance();
        }

        return static::query()->firstOrCreate([], [
            'popups_enabled' => true,
        ]);
    }

    public static function defaultInstance(): self
    {
        $setting = new self([
            'popups_enabled' => true,
        ]);
        $setting->exists = false;

        return $setting;
    }

    public static function popupsEnabledForHome(): bool
    {
        if (! Schema::hasTable('beranda_mascot_settings')) {
            return true;
        }

        return static::current()->popups_enabled;
    }

    public static function schemaReady(): bool
    {
        return Schema::hasTable('beranda_mascot_settings')
            && Schema::hasTable('beranda_mascot_tips');
    }
}
