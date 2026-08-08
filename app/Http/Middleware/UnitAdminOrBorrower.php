<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnitAdminOrBorrower
{
    /**
     * Shared area for unit admins and borrowers. Administrators are admitted
     * too — the administrator role is system-wide.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->isAdministrator() || $user->isUnitAdmin() || $user->isBorrower())) {
            return $next($request);
        }

        abort(403, 'Forbidden');
    }
}
