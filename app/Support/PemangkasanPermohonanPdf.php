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

        $progresRelations = ['petugas', 'armadas'];

        if (ArmadaSchema::isReady()) {
            $progresRelations[] = 'armadas.armada';
        }

        $permohonan->progres->each(function ($progres) use ($progresRelations) {
            $progres->loadMissing($progresRelations);
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
