<?php

namespace App\Support;

use App\Models\Pemangkasan;
use Illuminate\Http\Response;

class PemangkasanPermohonanPdf
{
    public static function download(Pemangkasan $permohonan): Response
    {
        PdfExport::ensureGdLoaded();

        $permohonan->loadMissing(['taman']);
        $permohonan->load([
            'progres' => function ($query) {
                $query->orderBy('hari_ke')->orderBy('tanggal');
            },
        ]);

        $permohonan->progres->each(function ($progres) {
            $progres->loadMissing(['petugas', 'armadas.armada']);
        });

        return PdfExport::download(
            view('admin.pemangkasans.pdf_permohonan', [
                'permohonan' => $permohonan,
            ])->render(),
            self::filename($permohonan),
        );
    }

    public static function filename(Pemangkasan $permohonan): string
    {
        return OperasionalKegiatanPdfFilename::permohonanGabungan($permohonan);
    }
}
