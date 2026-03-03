<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResetAdminLoginSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->isMethod('GET')
            && $request->routeIs('filament.admin.auth.login')
            && ! auth()->check()
        ) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $next($request);
    }
}
