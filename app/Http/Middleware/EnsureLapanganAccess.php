<?php

namespace App\Http\Middleware;

use App\Support\LapanganGuestAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLapanganAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->canWrite()) {
            return $next($request);
        }

        if ($user && ! $user->canWrite()) {
            abort(403, 'Halaman input lapangan hanya untuk petugas pelaksana.');
        }

        if (! LapanganGuestAccess::enabled()) {
            return redirect()->guest(route('login'));
        }

        if (LapanganGuestAccess::isUnlocked($request)) {
            return $next($request);
        }

        return redirect()->guest(route('lapangan.unlock'));
    }
}
