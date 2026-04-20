<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HybridWebAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $mode = strtolower(trim((string) config('keycloak.auth_mode', 'legacy')));

        if ($mode === 'legacy') {
            return $next($request);
        }

        if (Auth::check()) {
            return $next($request);
        }

        return redirect()->guest(route('login'));
    }
}
