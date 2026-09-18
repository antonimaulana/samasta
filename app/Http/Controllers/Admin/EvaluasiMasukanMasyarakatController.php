<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AduanMasyarakat;
use App\Support\Evaluasi\MasukanMasyarakatReportBuilder;
use App\Support\PdfExport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EvaluasiMasukanMasyarakatController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->authorize('viewAny', AduanMasyarakat::class);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        return view('admin.evaluasi.masukan-masyarakat.index', MasukanMasyarakatReportBuilder::build(
            $request,
            $request->user(),
        ));
    }

    public function exportPdf(Request $request): Response
    {
        PdfExport::ensureGdLoaded();

        $data = MasukanMasyarakatReportBuilder::build($request, $request->user(), forPdf: true);
        $html = view('admin.evaluasi.masukan-masyarakat.pdf', $data)->render();
        $slug = str($data['labelPeriode'])->slug('-')->limit(40, '');

        return PdfExport::download(
            $html,
            'evaluasi-masukan-masyarakat-'.$slug.'.pdf',
            'landscape',
        );
    }
}
