<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedApiKey = (string) config('services.investment_planner.api_key');
        $providedApiKey = (string) $request->header('x-api-key');

        if ($expectedApiKey === '') {
            return response()->json([
                'message' => 'Server API key belum dikonfigurasi.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($providedApiKey === '' || ! hash_equals($expectedApiKey, $providedApiKey)) {
            return response()->json([
                'message' => 'Unauthorized. API key tidak valid.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
