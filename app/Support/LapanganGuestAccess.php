<?php

namespace App\Support;

use Illuminate\Http\Request;

class LapanganGuestAccess
{
    public static function enabled(): bool
    {
        return filled(config('simtaman.lapangan.pin'));
    }

    public static function isUnlocked(Request $request): bool
    {
        if (! self::enabled()) {
            return false;
        }

        $unlockedAt = $request->session()->get(self::sessionKey());

        if (! is_int($unlockedAt)) {
            return false;
        }

        $ttlMinutes = (int) config('simtaman.lapangan.session_ttl_minutes', 480);

        if ($ttlMinutes > 0 && (time() - $unlockedAt) > ($ttlMinutes * 60)) {
            self::lock($request);

            return false;
        }

        return true;
    }

    public static function attempt(Request $request, string $pin): bool
    {
        $expected = (string) config('simtaman.lapangan.pin');

        if ($expected === '' || ! hash_equals($expected, $pin)) {
            return false;
        }

        $request->session()->put(self::sessionKey(), time());

        return true;
    }

    public static function lock(Request $request): void
    {
        $request->session()->forget(self::sessionKey());
    }

    public static function allowsSubmission(Request $request): bool
    {
        if ($request->user()?->canInputLapangan()) {
            return true;
        }

        return self::isUnlocked($request);
    }

    public static function sessionKey(): string
    {
        return (string) config('simtaman.lapangan.session_key', 'lapangan_guest_unlocked_at');
    }
}
