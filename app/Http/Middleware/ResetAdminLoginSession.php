<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResetAdminLoginSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $isAdminEntry = $request->is('admin');
        $isAdminLogin = $request->routeIs('filament.admin.auth.login');

        if (
            $request->isMethod('GET')
            && ($isAdminEntry || $isAdminLogin)
            && ! auth()->check()
        ) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $next($request);
    }
}
