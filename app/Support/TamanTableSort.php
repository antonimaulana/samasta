<?php

namespace App\Support;

use App\Models\Kelurahan;
use App\Models\Taman;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TamanTableSort
{
    /**
     * @return array<string, string|callable(Builder, string): void>
     */
    public static function columns(): array
    {
        return [
            'nama' => 'nama_taman',
            'kategori' => function (Builder $q, string $direction): void {
                $cases = [];
                $bindings = [];

                foreach (Taman::KATEGORI as $index => $category) {
                    $cases[] = 'WHEN kategori = ? THEN ?';
                    $bindings[] = $category;
                    $bindings[] = $index;
                }

                $caseSql = 'CASE '.implode(' ', $cases).' ELSE 999 END';

                $q->orderByRaw("({$caseSql}) ".($direction === 'desc' ? 'DESC' : 'ASC'), $bindings)
                    ->orderBy('nama_taman', 'asc');
            },
            'wilayah' => function (Builder $q, string $direction): void {
                $q->orderBy(
                    Kelurahan::query()
                        ->select('kecamatans.nama')
                        ->join('kecamatans', 'kecamatans.id', '=', 'kelurahans.kecamatan_id')
                        ->whereColumn('kelurahans.id', 'tamans.kelurahan_id')
                        ->limit(1),
                    $direction,
                )->orderBy(
                    Kelurahan::query()
                        ->select('kelurahans.nama')
                        ->whereColumn('kelurahans.id', 'tamans.kelurahan_id')
                        ->limit(1),
                    $direction,
                )->orderBy('nama_taman', 'asc');
            },
            'luasan' => 'luasan',
            'tahun' => 'tahun_pembangunan',
            'status_data' => 'status_data',
        ];
    }

    /**
     * @return array{sort: string, direction: string}
     */
    public static function apply(
        Builder $query,
        Request $request,
        string $defaultColumn = 'kategori',
        string $defaultDirection = 'asc',
    ): array {
        return TableSort::apply(
            $query,
            $request,
            self::columns(),
            $defaultColumn,
            $defaultDirection,
        );
    }
}
