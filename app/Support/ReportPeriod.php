<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportPeriod
{
    /** @var list<string> */
    public const RENTANG_KEYS = ['bulan', '3bulan', '6bulan', '1tahun', 'semua'];

    /**
     * @return array<string, string>
     */
    public static function rentangOptions(): array
    {
        return [
            'bulan' => 'Per bulan',
            '3bulan' => '3 bulan',
            '6bulan' => '6 bulan',
            '1tahun' => '1 tahun',
            'semua' => 'Semua waktu',
        ];
    }

    public static function normalizedRentang(Request $request): string
    {
        $rentang = (string) $request->input('rentang', '');

        if ($rentang !== '' && in_array($rentang, self::RENTANG_KEYS, true)) {
            return $rentang;
        }

        if ($request->input('mode') === 'periode' && $request->filled('dari') && $request->filled('sampai')) {
            return 'periode';
        }

        return 'bulan';
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string, 3: int, 4: int, 5: string, 6: string, 7: string}
     */
    public static function resolve(Request $request): array
    {
        $rentang = self::normalizedRentang($request);

        if ($rentang === 'periode') {
            $from = Carbon::parse($request->input('dari'))->startOfDay();
            $to = Carbon::parse($request->input('sampai'))->endOfDay();

            return [
                $from,
                $to,
                $from->translatedFormat('d M Y').' – '.$to->translatedFormat('d M Y'),
                (int) $from->month,
                (int) $from->year,
                'periode',
                $request->input('dari'),
                $request->input('sampai'),
            ];
        }

        if ($rentang !== 'bulan') {
            return self::resolvePresetRentang($rentang);
        }

        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);
        $from = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $to = $from->copy()->endOfMonth()->endOfDay();

        return [
            $from,
            $to,
            $from->translatedFormat('F Y'),
            $bulan,
            $tahun,
            'bulan',
            $from->toDateString(),
            $to->toDateString(),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string, 3: int, 4: int, 5: string, 6: string, 7: string}
     */
    private static function resolvePresetRentang(string $rentang): array
    {
        $to = now()->copy()->endOfDay();

        $from = match ($rentang) {
            '3bulan' => now()->copy()->subMonths(2)->startOfMonth()->startOfDay(),
            '6bulan' => now()->copy()->subMonths(5)->startOfMonth()->startOfDay(),
            '1tahun' => now()->copy()->subMonths(11)->startOfMonth()->startOfDay(),
            'semua' => Carbon::create(2018, 1, 1)->startOfDay(),
            default => now()->copy()->startOfMonth()->startOfDay(),
        };

        $label = match ($rentang) {
            '3bulan' => '3 bulan terakhir',
            '6bulan' => '6 bulan terakhir',
            '1tahun' => '12 bulan terakhir',
            'semua' => 'Semua waktu',
            default => $from->translatedFormat('F Y'),
        };

        return [
            $from,
            $to,
            $label,
            (int) $from->month,
            (int) $from->year,
            'periode',
            $from->toDateString(),
            $to->toDateString(),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function daftarBulan(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }

    /**
     * @return list<int>
     */
    public static function daftarTahun(int $yearsBack = 3): array
    {
        return range(now()->year, now()->year - $yearsBack);
    }

    /**
     * @return array<string, mixed>
     */
    public static function queryParams(Request $request): array
    {
        $rentang = self::normalizedRentang($request);
        [, , , $bulan, $tahun, , $dari, $sampai] = self::resolve($request);

        $params = ['rentang' => $rentang];

        if ($rentang === 'bulan') {
            $params['bulan'] = $bulan;
            $params['tahun'] = $tahun;
        }

        if ($rentang === 'periode') {
            $params['mode'] = 'periode';
            $params['dari'] = $dari;
            $params['sampai'] = $sampai;
        }

        return $params;
    }
}
