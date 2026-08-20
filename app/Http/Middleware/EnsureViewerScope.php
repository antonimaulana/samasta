<?php

namespace App\Http\Middleware;

use App\Support\ViewerRouteAllowlist;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureViewerScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isViewer()) {
            return $next($request);
        }

        if (! $request->isMethodSafe()) {
            abort(403, 'Akun viewer hanya dapat melihat data.');
        }

        $routeName = $request->route()?->getName();

        if (ViewerRouteAllowlist::allows($routeName)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Halaman tidak tersedia dalam mode ringkasan pimpinan.');
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('viewer_redirect', true);
    }
}
