<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportPeriod
{
    /**
     * @return array{0: Carbon, 1: Carbon, 2: string, 3: int, 4: int, 5: string, 6: string, 7: string}
     */
    public static function resolve(Request $request): array
    {
        $mode = $request->input('mode', 'bulan');

        if ($mode === 'periode' && $request->filled('dari') && $request->filled('sampai')) {
            $from = Carbon::parse($request->input('dari'))->startOfDay();
            $to = Carbon::parse($request->input('sampai'))->endOfDay();

            return [
                $from,
                $to,
                $from->translatedFormat('d M Y').' – '.$to->translatedFormat('d M Y'),
                (int) $from->month,
                (int) $from->year,
                $mode,
                $request->input('dari'),
                $request->input('sampai'),
            ];
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
}
