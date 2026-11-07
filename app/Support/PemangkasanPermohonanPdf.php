<?php

namespace App\Support;

use App\Models\Pemangkasan;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class PemangkasanPermohonanPdf
{
    public static function download(Pemangkasan $permohonan): Response
    {
        try {
            return self::attemptDownload($permohonan, includeProgresPhotos: true);
        } catch (\Throwable $first) {
            report($first);

            try {
                return self::attemptDownload($permohonan, includeProgresPhotos: false);
            } catch (\Throwable $second) {
                report($second);

                throw $second;
            }
        }
    }

    private static function attemptDownload(Pemangkasan $permohonan, bool $includeProgresPhotos): Response
    {
        PdfExport::ensureGdLoaded();

        $permohonan->loadMissing(['taman']);
        self::loadProgresEntries($permohonan);
        PemangkasanProgresPdfRelations::loadForPdf($permohonan->progres);

        $html = view('admin.pemangkasans.pdf_permohonan', [
            'permohonan' => $permohonan,
            'includeProgresPhotos' => $includeProgresPhotos,
            'progresPerTim' => TimPelaksanaPdfOrder::groupPemangkasanProgres($permohonan->progres, $permohonan),
        ])->render();

        return PdfExport::download($html, self::filename($permohonan));
    }

    private static function loadProgresEntries(Pemangkasan $permohonan): void
    {
        if (! Schema::hasTable('pemangkasan_progres')) {
            $permohonan->setRelation('progres', collect());

            return;
        }

        $permohonan->load([
            'progres' => function ($query) {
                if (Schema::hasColumn('pemangkasan_progres', 'hari_ke')) {
                    $query->orderBy('hari_ke');
                }

                if (Schema::hasColumn('pemangkasan_progres', 'tanggal')) {
                    $query->orderBy('tanggal');
                }

                $query->orderBy('id');
            },
        ]);
    }

    public static function filename(Pemangkasan $permohonan): string
    {
        return OperasionalKegiatanPdfFilename::permohonanGabungan($permohonan);
    }
}
