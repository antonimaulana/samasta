<?php

namespace App\Support;

use App\Models\Pemangkasan;
use Illuminate\Database\Eloquent\Builder;

class JadwalLayananQuery
{
    public static function belumSelesai(): Builder
    {
        return Pemangkasan::query()->where('status', '!=', 'Selesai');
    }

    public static function applyFilters(Builder $query, ?string $pelaksana = null, ?string $jenis = null): Builder
    {
        return $query
            ->when(filled($pelaksana), fn (Builder $q) => $q->whereJsonContains('pelaksana', $pelaksana))
            ->when(filled($jenis), fn (Builder $q) => $q->where('jenis_layanan', $jenis));
    }

    public static function semua(?string $pelaksana = null, ?string $jenis = null): Builder
    {
        return self::applyFilters(
            Pemangkasan::query(),
            $pelaksana,
            $jenis,
        )->latest('tanggal_permohonan');
    }

    public static function hariIni(?string $pelaksana = null, ?string $jenis = null): Builder
    {
        return self::applyFilters(
            self::belumSelesai()->whereDate('tanggal_eksekusi', today()),
            $pelaksana,
            $jenis,
        );
    }

    public static function besok(?string $pelaksana = null, ?string $jenis = null): Builder
    {
        return self::applyFilters(
            self::belumSelesai()->whereDate('tanggal_eksekusi', today()->addDay()),
            $pelaksana,
            $jenis,
        );
    }

    public static function mingguIni(?string $pelaksana = null, ?string $jenis = null): Builder
    {
        return self::applyFilters(
            self::belumSelesai()->whereBetween('tanggal_eksekusi', [
                today()->startOfWeek()->toDateString(),
                today()->endOfWeek()->toDateString(),
            ]),
            $pelaksana,
            $jenis,
        );
    }

    public static function antrianRencana(?string $pelaksana = null, ?string $jenis = null): Builder
    {
        return self::applyFilters(
            self::belumSelesai()->where('status', 'Rencana'),
            $pelaksana,
            $jenis,
        )->orderBy('tanggal_eksekusi')->orderBy('tanggal_permohonan');
    }

    public static function diproses(?string $pelaksana = null, ?string $jenis = null): Builder
    {
        return self::applyFilters(
            self::belumSelesai()->where('status', 'Diproses'),
            $pelaksana,
            $jenis,
        )->orderBy('tanggal_eksekusi');
    }

    public static function terlambat(?string $pelaksana = null, ?string $jenis = null): Builder
    {
        return self::applyFilters(
            self::belumSelesai()->whereDate('tanggal_eksekusi', '<', today()),
            $pelaksana,
            $jenis,
        )->orderBy('tanggal_eksekusi');
    }

    /**
     * @return array{semua: int, hari_ini: int, besok: int, minggu_ini: int, rencana: int, diproses: int, terlambat: int}
     */
    public static function counts(?string $pelaksana = null, ?string $jenis = null): array
    {
        return [
            'semua' => self::semua($pelaksana, $jenis)->count(),
            'hari_ini' => self::hariIni($pelaksana, $jenis)->count(),
            'besok' => self::besok($pelaksana, $jenis)->count(),
            'minggu_ini' => self::mingguIni($pelaksana, $jenis)->count(),
            'rencana' => self::antrianRencana($pelaksana, $jenis)->count(),
            'diproses' => self::diproses($pelaksana, $jenis)->count(),
            'terlambat' => self::terlambat($pelaksana, $jenis)->count(),
        ];
    }

    public static function forView(string $view, ?string $pelaksana = null, ?string $jenis = null): Builder
    {
        $query = match ($view) {
            'semua' => self::semua($pelaksana, $jenis),
            'besok' => self::besok($pelaksana, $jenis),
            'minggu' => self::mingguIni($pelaksana, $jenis),
            'rencana' => self::antrianRencana($pelaksana, $jenis),
            'diproses' => self::diproses($pelaksana, $jenis),
            'terlambat' => self::terlambat($pelaksana, $jenis),
            default => self::hariIni($pelaksana, $jenis),
        };

        if (in_array($view, ['hari_ini', 'besok', 'minggu'], true)) {
            $query->orderBy('tanggal_eksekusi')->orderBy('status');
        }

        return $query;
    }

    /**
     * @return list<array{key: string, label: string, description: string}>
     */
    public static function viewOptions(): array
    {
        return [
            ['key' => 'semua', 'label' => 'Semua', 'description' => 'Seluruh data operasional pertamanan'],
            ['key' => 'hari_ini', 'label' => 'Hari Ini', 'description' => 'Jadwal pelaksanaan hari ini'],
            ['key' => 'besok', 'label' => 'Besok', 'description' => 'Reminder jadwal H-1'],
            ['key' => 'minggu', 'label' => 'Minggu Ini', 'description' => 'Semua jadwal minggu berjalan'],
            ['key' => 'rencana', 'label' => 'Antrian Rencana', 'description' => 'Menunggu pelaksanaan'],
            ['key' => 'diproses', 'label' => 'Sedang Diproses', 'description' => 'Sedang dikerjakan di lapangan'],
            ['key' => 'terlambat', 'label' => 'Terlambat', 'description' => 'Jadwal lewat, belum selesai'],
        ];
    }
}
