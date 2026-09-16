<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemangkasan;
use App\Support\Evaluasi\OperasionalPermohonanReportBuilder;
use App\Support\PdfExport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EvaluasiOperasionalPermohonanController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->authorize('viewAny', Pemangkasan::class);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        return view('admin.evaluasi.operasional-permohonan.index', OperasionalPermohonanReportBuilder::build(
            $request,
            $request->user(),
        ));
    }

    public function exportPdf(Request $request): Response
    {
        PdfExport::ensureGdLoaded();

        $data = OperasionalPermohonanReportBuilder::build($request, $request->user(), forPdf: true);
        $html = view('admin.evaluasi.operasional-permohonan.pdf', $data)->render();
        $slug = str($data['labelPeriode'])->slug('-')->limit(40, '');

        return PdfExport::download(
            $html,
            'evaluasi-operasional-permohonan-'.$slug.'.pdf',
            'landscape',
        );
    }
}
