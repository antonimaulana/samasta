<?php

namespace App\Support;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PdfExport
{
    public static function ensureGdLoaded(): void
    {
        if (! extension_loaded('gd')) {
            abort(503, 'Ekstensi PHP GD belum aktif. Aktifkan extension=gd di php.ini (XAMPP: C:\\xampp\\php\\php.ini) lalu restart server PHP.');
        }
    }

    public static function download(string $html, string $filename, string $orientation = 'portrait'): Response
    {
        return response(self::renderBinary($html, $orientation), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public static function inline(string $html, string $filename, string $orientation = 'portrait'): Response
    {
        return response(self::renderBinary($html, $orientation), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public static function renderBinary(string $html, string $orientation = 'portrait'): string
    {
        $options = new Options;
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        return $dompdf->output();
    }

    public static function saveToPublicDisk(string $html, string $relativePath, string $orientation = 'portrait'): void
    {
        Storage::disk('public')->put($relativePath, self::renderBinary($html, $orientation));
    }
}
