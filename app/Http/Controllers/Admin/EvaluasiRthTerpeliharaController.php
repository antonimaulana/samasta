<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Taman;
use App\Support\Evaluasi\RthTerpeliharaReportBuilder;
use App\Support\PdfExport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EvaluasiRthTerpeliharaController extends Controller
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
        return view('admin.evaluasi.rth-terpelihara.index', RthTerpeliharaReportBuilder::build(
            $request,
            $request->user(),
        ));
    }

    public function exportPdf(Request $request): Response
    {
        PdfExport::ensureGdLoaded();

        $data = RthTerpeliharaReportBuilder::build($request, $request->user(), forPdf: true);
        $html = view('admin.evaluasi.rth-terpelihara.pdf', $data)->render();

        return PdfExport::download(
            $html,
            'evaluasi-rth-terpelihara-'.$data['freshDays'].'h-'.now()->format('Y-m-d').'.pdf',
            'landscape',
        );
    }
}
