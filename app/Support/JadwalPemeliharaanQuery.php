<?php

namespace App\Support;

use App\Models\PemeliharaanTaman;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class JadwalPemeliharaanQuery
{
    public static function belumSelesai(): Builder
    {
        return PemeliharaanTaman::query()->where('persentase_progres', '<', 100);
    }

    /**
     * Kegiatan multi-hari yang aktif pada tanggal tertentu.
     */
    public static function overlapsDate(Builder $query, Carbon|string $date): Builder
    {
        $dateString = Carbon::parse($date)->toDateString();

        return $query
            ->where('persentase_progres', '<', 100)
            ->whereDate('tanggal', '<=', $dateString)
            ->whereRaw(
                "date(date(tanggal), '+' || (COALESCE(total_hari, 1) - 1) || ' days') >= ?",
                [$dateString],
            );
    }

    public static function hariIni(?string $tim = null): Builder
    {
        return self::applyTim(
            self::overlapsDate(PemeliharaanTaman::query(), today()),
            $tim,
        )->orderBy('tim')->orderBy('tanggal');
    }

    public static function besok(?string $tim = null): Builder
    {
        return self::applyTim(
            self::overlapsDate(PemeliharaanTaman::query(), today()->addDay()),
            $tim,
        )->orderBy('tim')->orderBy('tanggal');
    }

    public static function diproses(?string $tim = null): Builder
    {
        return self::applyTim(
            self::belumSelesai()
                ->where('persentase_progres', '>', 0)
                ->whereRaw(
                    "date(date(tanggal), '+' || (COALESCE(total_hari, 1) - 1) || ' days') >= ?",
                    [today()->toDateString()],
                ),
            $tim,
        )->orderByDesc('tanggal');
    }

    public static function terlambat(?string $tim = null): Builder
    {
        return self::applyTim(
            self::belumSelesai()->whereRaw(
                "date(date(tanggal), '+' || (COALESCE(total_hari, 1) - 1) || ' days') < ?",
                [today()->toDateString()],
            ),
            $tim,
        )->orderBy('tanggal');
    }

    public static function forView(string $view, ?string $tim = null): Builder
    {
        return match ($view) {
            'besok' => self::besok($tim),
            'diproses' => self::diproses($tim),
            'terlambat' => self::terlambat($tim),
            default => self::hariIni($tim),
        };
    }

    private static function applyTim(Builder $query, ?string $tim): Builder
    {
        return $query->when(filled($tim), fn (Builder $q) => $q->where('tim', $tim));
    }
}
