<?php

namespace App\Support;

use App\Models\Petugas;
use App\Models\TimPelaksana;
use Illuminate\Support\Collection;

class PetugasRosterBuilder
{
    /**
     * @return list<array{id: int, nama: string, jabatan: ?string, is_inti: bool, is_pengawas: bool, tim: string}>
     */
    public function forTeamName(?string $teamName): array
    {
        if (blank($teamName)) {
            return [];
        }

        $team = TimPelaksana::query()->where('nama', $teamName)->first();

        if (! $team) {
            return [];
        }

        return $this->forTeam($team);
    }

    /**
     * @param  list<string>  $teamNames
     * @return list<array{id: int, nama: string, jabatan: ?string, is_inti: bool, is_pengawas: bool, tim: string}>
     */
    public function forTeamNames(array $teamNames): array
    {
        $names = collect($teamNames)->filter()->unique()->values();

        if ($names->isEmpty()) {
            return [];
        }

        $teams = TimPelaksana::query()
            ->whereIn('nama', $names->all())
            ->get()
            ->keyBy('nama');

        return $names
            ->flatMap(fn (string $name) => $teams->has($name)
                ? $this->forTeam($teams->get($name))
                : [])
            ->values()
            ->all();
    }

    /**
     * @return array<string, list<array{id: int, nama: string, jabatan: ?string, is_inti: bool, is_pengawas: bool, tim: string}>>
     */
    public function allActiveByTeam(): array
    {
        return TimPelaksana::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn (TimPelaksana $team) => [$team->nama => $this->forTeam($team)])
            ->all();
    }

    /**
     * @return list<array{id: int, nama: string, jabatan: ?string, is_inti: bool, is_pengawas: bool, tim: string}>
     */
    private function forTeam(TimPelaksana $team): array
    {
        return Petugas::query()
            ->where('tim_pelaksana_id', $team->id)
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get()
            ->map(fn (Petugas $petugas) => [
                'id' => $petugas->id,
                'nama' => $petugas->nama,
                'jabatan' => $petugas->jabatan,
                'is_inti' => (bool) $petugas->is_inti,
                'is_pengawas' => (bool) $petugas->is_pengawas,
                'tim' => $team->nama,
            ])
            ->all();
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, Petugas>
     */
    public function loadByIds(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return Petugas::query()
            ->with('timPelaksana')
            ->whereIn('id', $ids)
            ->get();
    }
}
