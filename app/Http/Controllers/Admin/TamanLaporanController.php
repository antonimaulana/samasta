<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Taman;
use App\Support\PdfExport;
use App\Support\TamanReportBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TamanLaporanController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->authorize('viewAny', Taman::class);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $data = app(TamanReportBuilder::class)->build($request);

        return view('admin.taman_laporan.index', $data);
    }

    public function exportPdf(Request $request): Response
    {
        PdfExport::ensureGdLoaded();

        $data = app(TamanReportBuilder::class)->build($request);

        $html = view('admin.taman_laporan.pdf', [
            ...$data,
            'generatedAt' => now(),
        ])->render();

        $filename = 'laporan-rth-taman-'.now()->format('Y-m-d').'.pdf';

        return PdfExport::download($html, $filename, 'landscape');
    }
}
