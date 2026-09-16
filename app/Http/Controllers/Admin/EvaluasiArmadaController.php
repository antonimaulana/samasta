<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlatSaranaOperasional;
use App\Support\Evaluasi\ArmadaUtilisasiReportBuilder;
use App\Support\PdfExport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EvaluasiArmadaController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->authorize('viewAny', AlatSaranaOperasional::class);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        return view('admin.evaluasi.armada.index', ArmadaUtilisasiReportBuilder::build(
            $request,
            $request->user(),
        ));
    }

    public function exportPdf(Request $request): Response
    {
        PdfExport::ensureGdLoaded();

        $data = ArmadaUtilisasiReportBuilder::build($request, $request->user(), forPdf: true);
        $html = view('admin.evaluasi.armada.pdf', $data)->render();
        $slug = str($data['labelPeriode'])->slug('-')->limit(40, '');

        return PdfExport::download(
            $html,
            'evaluasi-utilisasi-armada-'.$slug.'.pdf',
            'landscape',
        );
    }
}
