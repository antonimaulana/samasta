<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $routeName = $request->route()?->getName() ?? '';

        if (! $user->canWrite()) {
            if (! $request->isMethodSafe()) {
                abort(403, 'Akun ini hanya dapat melihat data (Pimpinan). Pengawas memakai Input Lapangan untuk entri di lapangan.');
            }

            if (str_ends_with($routeName, '.create') || str_ends_with($routeName, '.edit')) {
                abort(403, 'Akun ini hanya dapat melihat data (Pimpinan). Pengawas memakai Input Lapangan untuk entri di lapangan.');
            }
        }

        if ($request->isMethod('DELETE') && ! $user->canDelete()) {
            $operatorMayDeleteBibitTransaksi = $user->canWrite()
                && (
                    str_starts_with($routeName, 'admin.bibit-masuks.')
                    || str_starts_with($routeName, 'admin.bibit-keluars.')
                );

            if (! $operatorMayDeleteBibitTransaksi) {
                abort(403, 'Hanya administrator yang dapat menghapus data.');
            }
        }

        return $next($request);
    }
}
