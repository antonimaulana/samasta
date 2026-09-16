<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimPelaksana;
use App\Support\Evaluasi\KinerjaTimReportBuilder;
use App\Support\PdfExport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EvaluasiKinerjaTimController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->authorize('viewAny', TimPelaksana::class);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        return view('admin.evaluasi.kinerja-tim.index', KinerjaTimReportBuilder::build(
            $request,
            $request->user(),
        ));
    }

    public function exportPdf(Request $request): Response
    {
        PdfExport::ensureGdLoaded();

        $data = KinerjaTimReportBuilder::build($request, $request->user(), forPdf: true);
        $html = view('admin.evaluasi.kinerja-tim.pdf', $data)->render();
        $slug = str($data['labelPeriode'])->slug('-')->limit(40, '');

        return PdfExport::download(
            $html,
            'evaluasi-kinerja-tim-'.$slug.'.pdf',
            'landscape',
        );
    }
}
