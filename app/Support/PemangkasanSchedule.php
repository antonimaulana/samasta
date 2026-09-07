<?php

namespace App\Support;

use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class PemangkasanSchedule
{
    public static function totalHari(CarbonInterface|string|null $mulai, CarbonInterface|string|null $akhir): int
    {
        $start = Carbon::parse($mulai)->startOfDay();
        $end = Carbon::parse($akhir ?? $mulai)->startOfDay();

        if ($end->lt($start)) {
            return 1;
        }

        return (int) $start->diffInDays($end) + 1;
    }

    public static function hariKe(CarbonInterface|string $mulai, CarbonInterface|string $tanggal): int
    {
        $start = Carbon::parse($mulai)->startOfDay();
        $date = Carbon::parse($tanggal)->startOfDay();

        if ($date->lt($start)) {
            return 1;
        }

        return (int) $start->diffInDays($date) + 1;
    }

    public static function persentaseProgres(int $hariTercapai, int $totalHari): int
    {
        if ($totalHari <= 0) {
            return 0;
        }

        return min(100, (int) round(($hariTercapai / $totalHari) * 100));
    }

    /**
     * @return array{total_hari: int, tanggal_akhir_jadwal: string}
     */
    public static function normalizeScheduleFields(CarbonInterface|string $mulai, CarbonInterface|string|null $akhir): array
    {
        $total = self::totalHari($mulai, $akhir ?? $mulai);

        return [
            'total_hari' => $total,
            'tanggal_akhir_jadwal' => Carbon::parse($akhir ?? $mulai)->toDateString(),
        ];
    }

    public static function recalculate(Pemangkasan $permohonan): Pemangkasan
    {
        $totalHari = (int) ($permohonan->total_hari ?: self::totalHari(
            $permohonan->tanggal_eksekusi,
            $permohonan->tanggal_akhir_jadwal ?? $permohonan->tanggal_eksekusi,
        ));

        $hariTercapai = $permohonan->progres()->count();

        $permohonan->forceFill([
            'total_hari' => $totalHari,
            'hari_tercapai' => $hariTercapai,
            'persentase_progres' => self::persentaseProgres($hariTercapai, $totalHari),
        ])->save();

        return $permohonan->fresh();
    }

    public static function tanggalWithinSchedule(Pemangkasan $permohonan, CarbonInterface|string $tanggal): bool
    {
        $date = Carbon::parse($tanggal)->startOfDay();
        $mulai = Carbon::parse($permohonan->tanggal_eksekusi)->startOfDay();
        $akhir = Carbon::parse($permohonan->tanggal_akhir_jadwal ?? $permohonan->tanggal_eksekusi)->startOfDay();

        return $date->betweenIncluded($mulai, $akhir);
    }

    public static function labelRentang(Pemangkasan $permohonan): string
    {
        $mulai = $permohonan->tanggal_eksekusi->format('d/m/Y');
        $akhir = ($permohonan->tanggal_akhir_jadwal ?? $permohonan->tanggal_eksekusi)->format('d/m/Y');

        if ($mulai === $akhir) {
            return $mulai;
        }

        return $mulai.' – '.$akhir;
    }

    public static function progressSummary(Pemangkasan $permohonan): string
    {
        $total = (int) ($permohonan->total_hari ?? 1);
        $tercapai = (int) ($permohonan->hari_tercapai ?? 0);
        $persen = (int) ($permohonan->persentase_progres ?? 0);

        return $tercapai.' / '.$total.' hari ('.$persen.'% progres)';
    }

    public static function dailyProgressLabel(Pemangkasan $permohonan, PemangkasanProgres $entry): string
    {
        $total = (int) ($permohonan->total_hari ?? 1);
        $hariKe = (int) $entry->hari_ke;
        $persen = self::persentaseProgres($hariKe, $total);

        return $hariKe.' / '.$total.' hari ('.$persen.'% progres)';
    }

    public static function isLate(Pemangkasan $permohonan): bool
    {
        if ($permohonan->status === 'Selesai') {
            return false;
        }

        $akhir = Carbon::parse($permohonan->tanggal_akhir_jadwal ?? $permohonan->tanggal_eksekusi)->startOfDay();

        return $akhir->lt(today());
    }

    public static function isActiveToday(Pemangkasan $permohonan): bool
    {
        if ($permohonan->status === 'Selesai') {
            return false;
        }

        return self::tanggalWithinSchedule($permohonan, today());
    }
}
