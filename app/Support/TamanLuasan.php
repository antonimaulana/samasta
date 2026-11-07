<?php

namespace App\Support;

final class TamanLuasan
{
    public const DECIMAL_PLACES = 3;

    /**
     * Nilai untuk input HTML (tanpa pemisah ribuan).
     */
    public static function inputValue(float|int|string|null $luasan): string
    {
        if ($luasan === null || $luasan === '') {
            return '';
        }

        $value = (float) $luasan;

        if (! is_finite($value)) {
            return '';
        }

        return rtrim(rtrim(number_format($value, self::DECIMAL_PLACES, '.', ''), '0'), '.');
    }

    /**
     * Tampilan dengan pemisah ribuan Indonesia; desimal hanya jika perlu.
     */
    public static function format(float|int|string|null $luasan): string
    {
        $value = (float) ($luasan ?? 0);

        if (! is_finite($value)) {
            return '0';
        }

        if (abs($value - round($value)) < 0.0005) {
            return number_format($value, 0, ',', '.');
        }

        $formatted = number_format($value, self::DECIMAL_PLACES, ',', '.');

        if (str_contains($formatted, ',')) {
            $formatted = rtrim(rtrim($formatted, '0'), ',');
        }

        return $formatted;
    }

    public static function normalizeRequest(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        $value = preg_replace('/\s*m[²2]\s*$/iu', '', $value) ?? $value;
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $value = str_replace([' ', "'"], '', $value);

        if (preg_match('/^\d{1,3}(\.\d{3})+,\d+$/', $value) === 1) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $value) === 1) {
            $value = str_replace('.', '', $value);
        } elseif (preg_match('/^\d{1,3}(,\d{3})+$/', $value) === 1) {
            $value = str_replace(',', '', $value);
        } elseif (preg_match('/^\d+,\d+$/', $value) === 1) {
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '.', $value);
        }

        if (! is_numeric($value)) {
            return null;
        }

        $float = (float) $value;

        if ($float < 0) {
            return null;
        }

        return self::inputValue($float);
    }

    public static function filled(float|int|string|null $luasan): bool
    {
        return (float) ($luasan ?? 0) > 0;
    }
}
