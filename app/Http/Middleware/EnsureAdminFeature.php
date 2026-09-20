<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (! config("simtaman.features.{$feature}", false)) {
            abort(404);
        }

        return $next($request);
    }
}
