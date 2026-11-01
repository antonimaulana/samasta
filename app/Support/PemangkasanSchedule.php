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
        $totalHari = (int) ($permohonan->total_hari ?: self::totalHari(
            $permohonan->tanggal_eksekusi,
            $permohonan->tanggal_akhir_jadwal ?? $permohonan->tanggal_eksekusi,
        ));
        $hariLabel = $totalHari.' hari';

        if ($mulai === $akhir) {
            return $mulai.' · '.$hariLabel;
        }

        return $mulai.' – '.$akhir.' · '.$hariLabel;
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

    public static function plannedScheduleEnd(Pemangkasan $permohonan): Carbon
    {
        return Carbon::parse(
            $permohonan->tanggal_akhir_jadwal_rencana
                ?? $permohonan->tanggal_akhir_jadwal
                ?? $permohonan->tanggal_eksekusi,
        )->startOfDay();
    }

    public static function startedEarly(Pemangkasan $permohonan): bool
    {
        $mulai = Carbon::parse($permohonan->tanggal_eksekusi)->startOfDay();

        $entries = $permohonan->relationLoaded('progres')
            ? $permohonan->progres
            : $permohonan->progres()->get(['tanggal']);

        if ($entries->isEmpty()) {
            return false;
        }

        $earliest = $entries->min(fn (PemangkasanProgres $entry) => Carbon::parse($entry->tanggal)->startOfDay());

        return $earliest->lt($mulai);
    }

    public static function needsExtraTime(Pemangkasan $permohonan): bool
    {
        $rencana = self::plannedScheduleEnd($permohonan);
        $aktual = Carbon::parse($permohonan->tanggal_akhir_jadwal ?? $permohonan->tanggal_eksekusi)->startOfDay();

        return $aktual->gt($rencana);
    }

    /**
     * @return list<array{label: string, class: string}>
     */
    public static function jadwalAdjustmentLabels(Pemangkasan $permohonan): array
    {
        $labels = [];

        if (self::startedEarly($permohonan)) {
            $labels[] = [
                'label' => 'Mulai lebih awal',
                'class' => 'text-amber-700',
            ];
        }

        if (self::needsExtraTime($permohonan)) {
            $labels[] = [
                'label' => 'Perlu waktu tambahan',
                'class' => 'text-violet-700',
            ];
        }

        return $labels;
    }
}
