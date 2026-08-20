<?php

namespace App\Support;

use App\Models\AduanMasyarakat;
use App\Models\SurveyKepuasan;
use Illuminate\Support\Facades\Cache;

class MasukanPanelIndicator
{
    public static function aduanBaruCount(): int
    {
        return AduanMasyarakat::query()->where('status', 'Baru')->count();
    }

    public static function surveyBaruCount(?int $userId = null): int
    {
        $userId ??= auth()->id();

        if ($userId === null) {
            return 0;
        }

        $query = SurveyKepuasan::query();
        $lastSeen = Cache::get(self::surveySeenKey($userId));

        if ($lastSeen) {
            $query->where('created_at', '>', $lastSeen);
        } else {
            $query->where('created_at', '>=', now()->subDays(7));
        }

        return $query->count();
    }

    public static function masukanBaruCount(?int $userId = null): int
    {
        return self::aduanBaruCount() + self::surveyBaruCount($userId);
    }

    public static function markSurveySeen(int $userId): void
    {
        Cache::forever(self::surveySeenKey($userId), now());
    }

    private static function surveySeenKey(int $userId): string
    {
        return "admin_masukan_seen:{$userId}:survey";
    }
}
