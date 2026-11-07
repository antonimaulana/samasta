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

        PemangkasanProgresPdfRelations::loadForPdf($permohonan->progres);

        try {
            $html = view('admin.pemangkasans.pdf_permohonan', [
                'permohonan' => $permohonan,
            ])->render();
        } catch (\Throwable $exception) {
            report($exception);

            abort(503, 'Laporan PDF permohonan tidak dapat dibuat. Periksa data progres atau hubungi administrator.');
        }

        try {
            return PdfExport::download($html, self::filename($permohonan));
        } catch (\Throwable $exception) {
            report($exception);

            abort(503, 'Laporan PDF permohonan tidak dapat dibuat. Periksa foto progres atau aktifkan ekstensi GD di server.');
        }
    }

    public static function filename(Pemangkasan $permohonan): string
    {
        return OperasionalKegiatanPdfFilename::permohonanGabungan($permohonan);
    }
}
