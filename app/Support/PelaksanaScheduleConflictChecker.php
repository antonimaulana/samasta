<?php

namespace App\Support;

use App\Models\Pemangkasan;
use Illuminate\Support\Collection;

class PelaksanaScheduleConflictChecker
{
    /**
     * @param  list<string>  $teams
     * @return Collection<int, array{id: int, jenis_layanan: string, lokasi_pohon: string, status: string, teams_overlap: list<string>}>
     */
    public static function find(string $date, array $teams, ?int $excludeId = null): Collection
    {
        $teams = array_values(array_filter($teams));

        if ($date === '' || $teams === []) {
            return collect();
        }

        return Pemangkasan::query()
            ->whereDate('tanggal_eksekusi', $date)
            ->where('status', '!=', 'Selesai')
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->orderBy('jenis_layanan')
            ->get(['id', 'jenis_layanan', 'lokasi_pohon', 'status', 'pelaksana'])
            ->map(function (Pemangkasan $layanan) use ($teams) {
                $layananTeams = is_array($layanan->pelaksana) ? $layanan->pelaksana : [];
                $overlap = array_values(array_intersect($layananTeams, $teams));

                if ($overlap === []) {
                    return null;
                }

                return [
                    'id' => $layanan->id,
                    'jenis_layanan' => $layanan->jenis_layanan,
                    'lokasi_pohon' => $layanan->lokasi_pohon,
                    'status' => $layanan->status,
                    'teams_overlap' => $overlap,
                ];
            })
            ->filter()
            ->values();
    }
}
