<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Hanya administrator yang dapat melihat log aktivitas.');

        $logs = ActivityLog::query()
            ->with('user')
            ->latest()
            ->paginate(25);

        return view('admin.activity_logs.index', compact('logs'));
    }
}
