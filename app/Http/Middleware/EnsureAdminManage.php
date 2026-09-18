<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminManage
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->canManageUsers()) {
            abort(403, 'Hanya Administrator yang dapat mengakses menu Sistem dan Monitoring DPA.');
        }

        return $next($request);
    }
}
