<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PemeliharaanTaman;
use App\Support\Evaluasi\PemeliharaanPerTamanReportBuilder;
use App\Support\PdfExport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EvaluasiPemeliharaanController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->authorize('viewAny', PemeliharaanTaman::class);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        return view('admin.evaluasi.pemeliharaan.index', PemeliharaanPerTamanReportBuilder::build(
            $request,
            $request->user(),
        ));
    }

    public function exportPdf(Request $request): Response
    {
        PdfExport::ensureGdLoaded();

        $data = PemeliharaanPerTamanReportBuilder::build($request, $request->user(), forPdf: true);
        $html = view('admin.evaluasi.pemeliharaan.pdf', $data)->render();
        $slug = str($data['labelPeriode'])->slug('-')->limit(40, '');

        return PdfExport::download(
            $html,
            'evaluasi-pemeliharaan-per-taman-'.$slug.'.pdf',
            'landscape',
        );
    }
}
