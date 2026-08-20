<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class AdminLayoutData
{
    private const CACHE_SECONDS = 60;

    public static function jadwalTerlambatCount(): int
    {
        return Cache::remember('admin_jadwal_terlambat_count', self::CACHE_SECONDS, function () {
            return JadwalLayananQuery::terlambat()->count();
        });
    }

    public static function forgetJadwalTerlambatCount(): void
    {
        Cache::forget('admin_jadwal_terlambat_count');
    }
}
