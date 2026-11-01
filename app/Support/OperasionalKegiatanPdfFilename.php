<?php

namespace App\Support;

use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use App\Models\PemeliharaanTaman;
use DateTimeInterface;
use Illuminate\Support\Str;

class OperasionalKegiatanPdfFilename
{
    private const PEMELIHARAAN_KEGIATAN = 'pemeliharaan rutin';

    public static function pemeliharaan(PemeliharaanTaman $kinerja): string
    {
        return self::build(
            tim: (string) $kinerja->tim,
            kegiatan: self::PEMELIHARAAN_KEGIATAN,
            tanggal: $kinerja->tanggal,
            progresPercent: self::pemeliharaanProgresPercent($kinerja),
        );
    }

    public static function permohonanProgres(Pemangkasan $permohonan, PemangkasanProgres $progres): string
    {
        $tim = collect($permohonan->pelaksana ?? [])->filter()->first() ?? 'Permohonan';

        return self::build(
            tim: (string) $tim,
            kegiatan: (string) ($permohonan->jenis_layanan ?? 'permohonan'),
            tanggal: $progres->tanggal ?? $permohonan->tanggal_eksekusi,
            progresPercent: self::permohonanProgresPercent($permohonan, $progres),
        );
    }

    private static function pemeliharaanProgresPercent(PemeliharaanTaman $kinerja): int
    {
        if ($kinerja->persentase_progres !== null) {
            return max(0, min(100, (int) $kinerja->persentase_progres));
        }

        if ($kinerja->hari_ke !== null && $kinerja->total_hari !== null && $kinerja->total_hari > 0) {
            return max(0, min(100, (int) round(($kinerja->hari_ke / $kinerja->total_hari) * 100)));
        }

        return 0;
    }

    private static function permohonanProgresPercent(Pemangkasan $permohonan, PemangkasanProgres $progres): int
    {
        $total = max(1, (int) ($permohonan->total_hari ?? 1));

        return max(0, min(100, (int) round(((int) $progres->hari_ke / $total) * 100)));
    }

    private static function build(string $tim, string $kegiatan, mixed $tanggal, int $progresPercent): string
    {
        if ($tanggal instanceof DateTimeInterface) {
            $date = $tanggal->format('Y-m-d');
        } elseif (is_string($tanggal) && $tanggal !== '') {
            $date = substr($tanggal, 0, 10);
        } else {
            $date = now()->format('Y-m-d');
        }

        $parts = [
            self::formatTimLabel($tim),
            self::formatKegiatanLabel($kegiatan),
            $date,
            $progresPercent.'%',
        ];

        $filename = implode('_', array_filter($parts, static fn (string $part) => $part !== ''));

        return Str::limit($filename, 180, '').'.pdf';
    }

    private static function formatTimLabel(string $tim): string
    {
        $label = trim($tim);

        if (preg_match('/^tim\s+/iu', $label)) {
            $label = preg_replace('/^tim\s+/iu', '', $label);
        }

        return self::sanitizeFilenameSegment(mb_strtolower(trim($label)));
    }

    private static function formatKegiatanLabel(string $kegiatan): string
    {
        return self::sanitizeFilenameSegment(mb_strtolower(trim($kegiatan)));
    }

    private static function sanitizeFilenameSegment(string $segment): string
    {
        $segment = preg_replace('/[\x00-\x1F\x7F\/\\\\:*?"<>|]/u', '', $segment) ?? '';
        $segment = preg_replace('/\s+/u', ' ', $segment) ?? '';

        return trim($segment);
    }
}
