<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DashboardSummaryBuilder;
use App\Support\LayananJadwalReminderDispatcher;
use App\Support\PdfExport;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(DashboardSummaryBuilder $summaryBuilder): View
    {
        if (Schema::hasTable('notifications') && auth()->user()?->canWrite()) {
            Cache::remember(
                'layanan_jadwal_reminders_'.today()->toDateString(),
                now()->endOfDay(),
                function () {
                    app(LayananJadwalReminderDispatcher::class)->dispatch();

                    return true;
                }
            );
        }

        return view('admin.dashboard', [
            ...$summaryBuilder->build(auth()->user()),
            'is_viewer' => auth()->user()?->isViewer() ?? false,
        ]);
    }

    public function exportPdf(DashboardSummaryBuilder $summaryBuilder): Response
    {
        $this->authorize('dashboard.export');

        PdfExport::ensureGdLoaded();

        $data = $summaryBuilder->build(auth()->user());
        $html = view('admin.dashboard.pdf', $data)->render();
        $filename = 'ringkasan-samasta-'.now()->format('Y-m-d-His').'.pdf';

        return PdfExport::download($html, $filename);
    }
}
