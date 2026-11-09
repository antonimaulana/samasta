<?php

namespace App\Support;

use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\PemeliharaanTaman;
use App\Models\User;
use Illuminate\Support\Collection;

class PermohonanPelaksanaProgress
{
    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_DIKERJAKAN = 'dikerjakan';

    /**
     * @return list<string>
     */
    public static function assignedTeams(Pemangkasan $permohonan): array
    {
        $teams = $permohonan->pelaksana;

        return is_array($teams) ? array_values(array_filter($teams)) : [];
    }

    /**
     * Tim yang boleh diinput oleh akun lapangan pada permohonan ini.
     *
     * @return list<string>
     */
    public static function inputTeamsForUser(?User $user, Pemangkasan $permohonan): array
    {
        $assigned = self::assignedTeams($permohonan);

        if ($assigned === []) {
            return [];
        }

        if (! $user?->requiresWilayahScope()) {
            return $assigned;
        }

        $scope = app(OperatorWilayahScope::class);

        if ($user->akses_semua_wilayah) {
            return $assigned;
        }

        $userTeams = $scope->teamNames($user);

        if ($userTeams === []) {
            return [];
        }

        return array_values(array_intersect($assigned, $userTeams));
    }

    /**
     * Satu tim terkunci untuk form lapangan (Armada, Nursery, atau satu wilayah).
     */
    public static function operatorTimForForm(?User $user, Pemangkasan $permohonan): ?string
    {
        $inputTeams = self::inputTeamsForUser($user, $permohonan);

        return count($inputTeams) === 1 ? $inputTeams[0] : null;
    }

    public static function resolveInputTim(?User $user, Pemangkasan $permohonan, ?string $requestedTim = null): ?string
    {
        $assigned = self::assignedTeams($permohonan);
        $allowed = self::inputTeamsForUser($user, $permohonan);

        if ($allowed !== []) {
            if (count($allowed) === 1) {
                return $allowed[0];
            }

            if (filled($requestedTim) && in_array($requestedTim, $allowed, true)) {
                return $requestedTim;
            }

            return null;
        }

        if (filled($requestedTim) && in_array($requestedTim, $assigned, true)) {
            return $requestedTim;
        }

        return $assigned[0] ?? null;
    }

    public static function userIsArmadaOperator(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return in_array(PemeliharaanTaman::TIM_ARMADA, app(OperatorWilayahScope::class)->teamNames($user), true);
    }

    public static function shouldCollectArmada(?User $user, ?string $inputTim = null): bool
    {
        return self::userIsArmadaOperator($user);
    }

    /**
     * Dokumentasi 6 foto progres (sama pola Armada / wilayah). Mini Garden: hanya Tim Nursery.
     */
    public static function shouldCollectFotoDocumentation(Pemangkasan $permohonan, ?string $inputTim = null): bool
    {
        if ($permohonan->jenis_layanan !== 'Pemasangan Mini Garden') {
            return true;
        }

        if ($inputTim === 'Tim Nursery') {
            return true;
        }

        if ($inputTim !== null) {
            return false;
        }

        return in_array('Tim Nursery', self::assignedTeams($permohonan), true);
    }

    /**
     * @return array<string, string>
     */
    public static function teamStatusesForDate(Pemangkasan $permohonan, string $datePart): array
    {
        $teams = self::assignedTeams($permohonan);

        if ($teams === []) {
            return [];
        }

        $entries = $permohonan->relationLoaded('progres')
            ? $permohonan->progres
            : $permohonan->progres()->get();

        $onDate = $entries->filter(
            fn (PemangkasanProgres $entry) => OperasionalPelaksanaanTime::datePart($entry->tanggal) === $datePart,
        );

        $legacyAllTeams = $onDate->contains(fn (PemangkasanProgres $entry) => blank($entry->tim));

        $reportedTeams = $onDate
            ->pluck('tim')
            ->filter()
            ->unique()
            ->values();

        $statuses = [];

        foreach ($teams as $team) {
            if ($legacyAllTeams || $reportedTeams->contains($team)) {
                $statuses[$team] = self::STATUS_DIKERJAKAN;
            } else {
                $statuses[$team] = self::STATUS_MENUNGGU;
            }
        }

        return $statuses;
    }

    public static function countHariTercapai(Pemangkasan $permohonan): int
    {
        $teams = collect(self::assignedTeams($permohonan));

        if ($teams->isEmpty()) {
            return $permohonan->progres()->get()
                ->map(fn (PemangkasanProgres $entry) => OperasionalPelaksanaanTime::datePart($entry->tanggal))
                ->unique()
                ->count();
        }

        $byDate = $permohonan->progres()->get()->groupBy(
            fn (PemangkasanProgres $entry) => OperasionalPelaksanaanTime::datePart($entry->tanggal),
        );

        return $byDate->filter(function (Collection $entries) use ($teams) {
            if ($entries->contains(fn (PemangkasanProgres $entry) => blank($entry->tim))) {
                return true;
            }

            $reported = $entries->pluck('tim')->filter()->unique();

            return $teams->every(fn (string $team) => $reported->contains($team));
        })->count();
    }
}
