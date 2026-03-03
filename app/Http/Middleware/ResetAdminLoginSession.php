<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;

class ResetAdminLoginSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $normalizedPath = trim($request->path(), '/');
        $isAdminLogin = $request->routeIs('filament.admin.auth.login');

        if (
            $request->isMethod('GET')
            && $isAdminLogin
        ) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $next($request);
    }
}
