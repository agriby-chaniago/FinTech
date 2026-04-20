<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\OidcUserResolver;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class HybridApiAuthMiddleware
{
    public function __construct(
        private readonly OidcUserResolver $oidcUserResolver
    ) {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            return $next($request);
        }

        $token = trim((string) $request->bearerToken());

        if ($token === '') {
            return new JsonResponse([
                'message' => 'Unauthorized. Please login via OIDC.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->resolveUserFromOidcToken($token);

        if (! $user instanceof User) {
            return new JsonResponse([
                'message' => 'Unauthorized. Invalid OIDC bearer token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        Auth::guard()->setUser($user);

        return $next($request);
    }

    private function resolveUserFromOidcToken(string $token): ?User
    {
        $userinfoEndpoint = trim((string) config('keycloak.endpoints.userinfo', ''));

        if ($userinfoEndpoint === '') {
            return null;
        }

        $response = Http::acceptJson()
            ->withToken($token)
            ->timeout((int) config('keycloak.http_timeout', 10))
            ->get($userinfoEndpoint);

        if (! $response->successful()) {
            return null;
        }

        $userinfo = $response->json();

        if (! is_array($userinfo)) {
            return null;
        }

        return $this->oidcUserResolver->resolveFromUserinfo($userinfo);
    }
}
