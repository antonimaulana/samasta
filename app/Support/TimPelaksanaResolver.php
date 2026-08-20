<?php

namespace App\Support;

use App\Models\Kelurahan;
use App\Models\TimPelaksana;

class TimPelaksanaResolver
{
    /**
     * @return list<string>
     */
    public function forKelurahan(?int $kelurahanId): array
    {
        if (! $kelurahanId) {
            return [];
        }

        return TimPelaksana::query()
            ->where('aktif', true)
            ->where('memiliki_wilayah_kerja', true)
            ->whereHas('kelurahans', fn ($query) => $query->where('kelurahans.id', $kelurahanId))
            ->orderBy('urutan')
            ->orderBy('nama')
            ->pluck('nama')
            ->all();
    }

    /**
     * @return list<string>
     */
    public function forTamanId(?int $tamanId): array
    {
        if (! $tamanId) {
            return [];
        }

        $kelurahanId = \App\Models\Taman::query()->whereKey($tamanId)->value('kelurahan_id');

        return $this->forKelurahan($kelurahanId ? (int) $kelurahanId : null);
    }

    /**
     * Kelurahan IDs for a team name. Null = team has no fixed wilayah (all locations allowed).
     *
     * @return list<int>|null
     */
    public function kelurahanIdsForTeamName(string $teamName): ?array
    {
        $team = TimPelaksana::query()
            ->where('nama', $teamName)
            ->where('aktif', true)
            ->first();

        if (! $team) {
            return null;
        }

        if (! $team->memiliki_wilayah_kerja) {
            return null;
        }

        return $team->kelurahans()
            ->pluck('kelurahans.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return array<string, list<int>|null>
     */
    public function kelurahanIdsByTeamName(): array
    {
        return TimPelaksana::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn (TimPelaksana $team) => [
                $team->nama => $team->memiliki_wilayah_kerja
                    ? $team->kelurahans()->pluck('kelurahans.id')->map(fn ($id) => (int) $id)->all()
                    : null,
            ])
            ->all();
    }

    public function tamanAllowedForTeam(string $teamName, int $tamanId): bool
    {
        $allowedKelurahanIds = $this->kelurahanIdsForTeamName($teamName);

        if ($allowedKelurahanIds === null) {
            return true;
        }

        $kelurahanId = \App\Models\Taman::query()->whereKey($tamanId)->value('kelurahan_id');

        if (! $kelurahanId) {
            return false;
        }

        return in_array((int) $kelurahanId, $allowedKelurahanIds, true);
    }
}
