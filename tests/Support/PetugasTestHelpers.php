<?php

namespace Tests\Support;

use App\Models\Petugas;

trait PetugasTestHelpers
{
    /**
     * @return list<int>
     */
    protected function petugasIdsForTeam(string $teamName, int $count = 2): array
    {
        return Petugas::query()
            ->whereHas('timPelaksana', fn ($query) => $query->where('nama', $teamName))
            ->orderBy('id')
            ->limit($count)
            ->pluck('id')
            ->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function withPetugasForTeam(array $payload, string $teamName, int $count = 2): array
    {
        $ids = $this->petugasIdsForTeam($teamName, $count);

        if ($ids !== []) {
            $payload['petugas_ids'] = $ids;
            unset($payload['jumlah_personil']);
        }

        return $payload;
    }

    /**
     * @param  list<string>  $teamNames
     * @return list<int>
     */
    protected function petugasIdsForTeams(array $teamNames, int $count = 2): array
    {
        return Petugas::query()
            ->whereHas('timPelaksana', fn ($query) => $query->whereIn('nama', $teamNames))
            ->orderBy('id')
            ->limit($count)
            ->pluck('id')
            ->all();
    }
}
