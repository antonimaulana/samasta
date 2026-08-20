<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyKepuasan;
use App\Support\MasukanPanelIndicator;
use App\Support\ReportPeriod;
use App\Support\TableSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SurveyKepuasanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SurveyKepuasan::class, 'survey_kepuasan', [
            'only' => ['index', 'destroy'],
        ]);
    }

    public function index(Request $request): View
    {
        MasukanPanelIndicator::markSurveySeen($request->user()->id);

        [$from, $to, $labelPeriode, $bulan, $tahun, $mode, $dari, $sampai] = ReportPeriod::resolve($request);

        $surveys = SurveyKepuasan::query()
            ->with('taman')
            ->whereBetween('created_at', [$from, $to])
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori', $request->kategori))
            ->when($request->filled('rating'), fn ($q) => $q->where('rating', $request->rating))
            ->tap(fn ($q) => TableSearch::apply($q, $request, ['kategori', 'nama', 'saran']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.survey_kepuasan.index', [
            'surveys' => $surveys,
            'summary' => SurveyKepuasan::summary($from, $to),
            'labelPeriode' => $labelPeriode,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'mode' => $mode,
            'dari' => $dari,
            'sampai' => $sampai,
            'daftarBulan' => ReportPeriod::daftarBulan(),
            'daftarTahun' => ReportPeriod::daftarTahun(),
        ]);
    }

    public function destroy(SurveyKepuasan $surveyKepuasan): RedirectResponse
    {
        $surveyKepuasan->delete();

        return redirect()
            ->back()
            ->with('success', 'Data survey berhasil dihapus.');
    }
}
