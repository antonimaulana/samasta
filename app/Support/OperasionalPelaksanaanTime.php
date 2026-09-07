<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class OperasionalPelaksanaanTime
{
    public static function normalizeInput(?string $value): string
    {
        if (blank($value)) {
            return now()->format('Y-m-d H:i:s');
        }

        return Carbon::parse(str_replace('T', ' ', $value))->format('Y-m-d H:i:s');
    }

    public static function inputValue(CarbonInterface|string|null $value = null): string
    {
        return Carbon::parse($value ?? now())->format('Y-m-d\TH:i');
    }

    public static function datePart(CarbonInterface|string|null $value): string
    {
        return Carbon::parse(str_replace('T', ' ', $value ?? now()))->toDateString();
    }

    public static function display(CarbonInterface|string|null $value): string
    {
        if (blank($value)) {
            return '—';
        }

        return Carbon::parse($value)->format('d/m/Y H:i');
    }

    public static function displayLong(CarbonInterface|string|null $value): string
    {
        if (blank($value)) {
            return '—';
        }

        $carbon = Carbon::parse($value);

        return $carbon->translatedFormat('l, d F Y').' · '.$carbon->format('H:i');
    }
}
