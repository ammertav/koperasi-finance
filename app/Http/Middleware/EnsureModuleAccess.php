<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    /**
     * Tolak request jika peran pengguna tidak punya akses modul sesuai matriks akses (ORG-03).
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $module, string $access = 'view'): Response
    {
        if (! $request->user()?->hasAccess($module, $access)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
