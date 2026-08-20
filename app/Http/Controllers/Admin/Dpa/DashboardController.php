<?php

namespace App\Http\Controllers\Admin\Dpa;

use App\Http\Controllers\Controller;
use App\Models\DpaTahunAnggaran;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.manage');
    }

    public function index(): View
    {
        $tahunList = DpaTahunAnggaran::query()
            ->orderByDesc('tahun')
            ->get();

        return view('admin.dpa.dashboard', [
            'tahunList' => $tahunList,
        ]);
    }
}
