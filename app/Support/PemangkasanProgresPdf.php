<?php

namespace App\Support;

use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use Illuminate\Http\Response;

class PemangkasanProgresPdf
{
    public static function resolveEntry(Pemangkasan $permohonan, PemangkasanProgres|int $progres): PemangkasanProgres
    {
        $progresId = $progres instanceof PemangkasanProgres ? $progres->getKey() : $progres;

        return PemangkasanProgres::query()
            ->whereKey($progresId)
            ->where('pemangkasan_id', $permohonan->getKey())
            ->firstOrFail();
    }

    public static function render(PemangkasanProgres $progres, Pemangkasan $permohonan): string
    {
        return view('admin.pemangkasans.pdf_progres', [
            'progres' => $progres,
            'permohonan' => $permohonan,
        ])->render();
    }

    public static function download(Pemangkasan $permohonan, PemangkasanProgres|int $progres): Response
    {
        PdfExport::ensureGdLoaded();

        $entry = self::resolveEntry($permohonan, $progres);
        $entry->loadMissing(['armadas.alatSarana']);
        $permohonan->loadMissing(['taman']);

        return PdfExport::download(
            self::render($entry, $permohonan),
            self::filename($entry, $permohonan),
        );
    }

    public static function filename(PemangkasanProgres $progres, Pemangkasan $permohonan): string
    {
        $slug = str($permohonan->lokasi_pohon)->slug('-')->limit(30, '');

        return 'operasional-'
            .$progres->tanggal->format('Y-m-d')
            .'-hari-'.$progres->hari_ke
            .'-'.$slug
            .'.pdf';
    }
}
