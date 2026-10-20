<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PemeliharaanTaman;
use App\Support\Evaluasi\EvaluasiOverviewBuilder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvaluasiDashboardController extends Controller
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
        return view('admin.evaluasi.index', EvaluasiOverviewBuilder::build(
            $request,
            $request->user(),
        ));
    }
}
