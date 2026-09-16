<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class OperasionalPelaksanaanTime
{
    public static function parse(CarbonInterface|string|null $value): Carbon
    {
        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        if (blank($value)) {
            return now();
        }

        $normalized = trim(str_replace('T', ' ', (string) $value));

        if (preg_match('/^(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2}:\d{2})(?:\s+\d{2}:\d{2}:\d{2})?/', $normalized, $matches)) {
            return Carbon::parse($matches[1].' '.$matches[2]);
        }

        return Carbon::parse($normalized);
    }

    public static function normalizeInput(?string $value): string
    {
        if (blank($value)) {
            return now()->format('Y-m-d H:i:s');
        }

        return self::parse($value)->format('Y-m-d H:i:s');
    }

    public static function inputValue(CarbonInterface|string|null $value = null): string
    {
        return self::parse($value ?? now())->format('Y-m-d\TH:i');
    }

    public static function datePart(CarbonInterface|string|null $value): string
    {
        return self::parse($value ?? now())->toDateString();
    }

    public static function display(CarbonInterface|string|null $value): string
    {
        if (blank($value)) {
            return '—';
        }

        return self::parse($value)->format('d/m/Y H:i');
    }

    public static function displayLong(CarbonInterface|string|null $value): string
    {
        if (blank($value)) {
            return '—';
        }

        $carbon = self::parse($value);

        return $carbon->translatedFormat('l, d F Y').' · '.$carbon->format('H:i');
    }
}
