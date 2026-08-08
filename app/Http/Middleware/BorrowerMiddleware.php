<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BorrowerMiddleware
{
    /**
     * Borrower area. Administrators are admitted too — the administrator role
     * is system-wide and must be able to reach every part of the application.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->isBorrower() || $user->isAdministrator())) {
            return $next($request);
        }

        abort(403, 'Forbidden');
    }
}
