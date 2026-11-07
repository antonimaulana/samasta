<?php

namespace App\Support;

use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\TimPelaksana;
use Illuminate\Support\Collection;

final class TimPelaksanaPdfOrder
{
    /**
     * @return list<string>
     */
    public static function orderedTimNames(): array
    {
        $fromDb = TimPelaksana::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('nama')
            ->pluck('nama')
            ->all();

        if ($fromDb !== []) {
            return $fromDb;
        }

        return TimPelaksana::legacyNames();
    }

    /**
     * @param  Collection<string, Collection<int, mixed>>  $groupedByTim
     * @return Collection<string, Collection<int, mixed>>
     */
    public static function sortGrouped(Collection $groupedByTim): Collection
    {
        $sorted = collect();
        $order = self::orderedTimNames();

        foreach ($order as $tim) {
            if ($groupedByTim->has($tim)) {
                $sorted->put($tim, $groupedByTim->get($tim));
            }
        }

        $remaining = $groupedByTim->keys()
            ->diff($sorted->keys())
            ->sort()
            ->values();

        foreach ($remaining as $tim) {
            $sorted->put($tim, $groupedByTim->get($tim));
        }

        return $sorted;
    }

    /**
     * @param  Collection<int, PemangkasanProgres>  $entries
     * @return Collection<string, Collection<int, PemangkasanProgres>>
     */
    public static function groupPemangkasanProgres(Collection $entries, Pemangkasan $permohonan): Collection
    {
        $pelaksana = $permohonan->pelaksana;
        $fallbackTim = is_array($pelaksana) ? ($pelaksana[0] ?? null) : null;

        $grouped = $entries->groupBy(function (PemangkasanProgres $entry) use ($fallbackTim): string {
            if (filled($entry->tim)) {
                return (string) $entry->tim;
            }

            return (string) ($fallbackTim ?? 'Operasional');
        });

        return self::sortGrouped($grouped)->map(
            fn (Collection $items) => $items->values(),
        );
    }
}
