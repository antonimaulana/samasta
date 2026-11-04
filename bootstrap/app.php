<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/admin');
        $middleware->alias([
            'admin.access' => \App\Http\Middleware\EnsureAdminAccess::class,
            'admin.manage' => \App\Http\Middleware\EnsureAdminManage::class,
            'admin.audit' => \App\Http\Middleware\LogAdminActivity::class,
            'admin.viewer.scope' => \App\Http\Middleware\EnsureViewerScope::class,
            'lapangan.access' => \App\Http\Middleware\EnsureLapanganAccess::class,
            'admin.feature' => \App\Http\Middleware\EnsureAdminFeature::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('layanan:send-reminders')->dailyAt('07:00');
        $schedule->command('operational:send-digest')->dailyAt('07:30');
        $schedule->command('simtaman:backup --full')->dailyAt('02:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi habis. Muat ulang halaman dan login jika perlu.',
                ], 419);
            }

            $message = match (true) {
                $request->is('logout') => 'Sesi Anda sudah habis. Anda tidak perlu keluar lagi — silakan masuk kembali jika ingin melanjutkan.',
                $request->is('lapangan*') => 'Sesi habis. Login ulang, buka Input Lapangan, lalu isi form lagi.',
                default => 'Sesi habis atau halaman kedaluwarsa. Login ulang, muat ulang halaman kerja, lalu ulangi tindakan Anda.',
            };

            return redirect()
                ->route('login')
                ->withErrors(['session' => $message]);
        });
    })->create();
