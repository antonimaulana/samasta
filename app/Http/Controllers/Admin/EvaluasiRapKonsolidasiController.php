<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Taman;
use App\Support\Evaluasi\RapKonsolidasiReportBuilder;
use App\Support\PdfExport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EvaluasiRapKonsolidasiController extends Controller
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
        return view('admin.evaluasi.rap-konsolidasi.index', RapKonsolidasiReportBuilder::build(
            $request,
            $request->user(),
        ));
    }

    public function exportPdf(Request $request): Response
    {
        PdfExport::ensureGdLoaded();

        $data = RapKonsolidasiReportBuilder::build($request, $request->user(), forPdf: true);
        $html = view('admin.evaluasi.rap-konsolidasi.pdf', $data)->render();
        $slug = str($data['labelPeriode'])->slug('-')->limit(40, '');

        return PdfExport::download(
            $html,
            'laporan-rap-konsolidasi-'.$slug.'.pdf',
            'landscape',
        );
    }
}
