<?php

namespace App\Support;

use App\Models\Taman;
use Illuminate\Support\Collection;

class TamanUnggulanHero
{
    /**
     * @return list<array{nama: string, label: string}>
     */
    public static function items(): array
    {
        return [
            [
                'nama' => 'Taman Cemara Asri',
                'label' => 'Sertifikasi RBRA',
            ],
            [
                'nama' => 'Dataran Engku Putri',
                'label' => 'Sering dikunjungi',
            ],
        ];
    }

    /**
     * @return Collection<int, array{taman: Taman, label: string}>
     */
    public static function resolve(): Collection
    {
        return collect(self::items())
            ->map(function (array $item) {
                $taman = Taman::query()
                    ->with('images')
                    ->where('nama_taman', $item['nama'])
                    ->first();

                if ($taman === null) {
                    return null;
                }

                return [
                    'taman' => $taman,
                    'label' => $item['label'],
                ];
            })
            ->filter()
            ->values();
    }
}
