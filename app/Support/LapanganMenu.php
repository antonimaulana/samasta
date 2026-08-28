<?php

namespace App\Support;

use App\Models\PemeliharaanTaman;
use App\Models\User;

class LapanganMenu
{
    /**
     * @return list<array{slug: string, label: string, description: string, icon: string, type: string, tim?: string}>
     */
    public static function items(?User $user = null): array
    {
        $items = [
            [
                'slug' => 'wilayah-1',
                'label' => 'Wilayah 1',
                'description' => 'Input pemeliharaan rutin Tim Wilayah 1',
                'icon' => '1',
                'type' => 'pemeliharaan',
                'tim' => 'Tim Wilayah 1',
            ],
            [
                'slug' => 'wilayah-2',
                'label' => 'Wilayah 2',
                'description' => 'Input pemeliharaan rutin Tim Wilayah 2',
                'icon' => '2',
                'type' => 'pemeliharaan',
                'tim' => 'Tim Wilayah 2',
            ],
            [
                'slug' => 'wilayah-3',
                'label' => 'Wilayah 3',
                'description' => 'Input pemeliharaan rutin Tim Wilayah 3',
                'icon' => '3',
                'type' => 'pemeliharaan',
                'tim' => 'Tim Wilayah 3',
            ],
            [
                'slug' => 'wilayah-4',
                'label' => 'Wilayah 4',
                'description' => 'Input pemeliharaan rutin Tim Wilayah 4',
                'icon' => '4',
                'type' => 'pemeliharaan',
                'tim' => 'Tim Wilayah 4',
            ],
            [
                'slug' => 'nursery',
                'label' => 'Nursery',
                'description' => 'Input pemeliharaan rutin Tim Nursery',
                'icon' => '🌱',
                'type' => 'pemeliharaan',
                'tim' => 'Tim Nursery',
            ],
            [
                'slug' => 'armada',
                'label' => 'Armada',
                'description' => 'Input pemeliharaan rutin Tim Armada',
                'icon' => '🚛',
                'type' => 'pemeliharaan',
                'tim' => PemeliharaanTaman::TIM_ARMADA,
            ],
            [
                'slug' => 'permohonan',
                'label' => 'Permohonan',
                'description' => 'Pilih permohonan admin dan isi progres pelaksanaan',
                'icon' => '📋',
                'type' => 'permohonan',
            ],
        ];

        $scope = app(OperatorWilayahScope::class);

        if (! $scope->restrictsWilayah($user)) {
            return $items;
        }

        $allowedTeams = $scope->teamNames($user);

        return array_values(array_filter($items, function (array $item) use ($allowedTeams) {
            if ($item['type'] === 'permohonan') {
                return true;
            }

            return in_array($item['tim'] ?? '', $allowedTeams, true);
        }));
    }

    public static function timFromSlug(string $slug): ?string
    {
        foreach (self::items(null) as $item) {
            if ($item['slug'] === $slug && $item['type'] === 'pemeliharaan') {
                return $item['tim'] ?? null;
            }
        }

        return null;
    }

    /**
     * @return array{slug: string, label: string, description: string, icon: string, type: string, tim?: string}|null
     */
    public static function find(string $slug, ?User $user = null): ?array
    {
        foreach (self::items($user) as $item) {
            if ($item['slug'] === $slug) {
                return $item;
            }
        }

        return null;
    }

    public static function userCanAccessTim(?User $user, string $tim): bool
    {
        $scope = app(OperatorWilayahScope::class);

        if (! $scope->restrictsWilayah($user)) {
            return in_array($tim, PemeliharaanTaman::timNames(), true);
        }

        return in_array($tim, $scope->teamNames($user), true);
    }
}
