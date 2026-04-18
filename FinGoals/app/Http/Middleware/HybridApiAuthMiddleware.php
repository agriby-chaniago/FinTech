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
        $mode = strtolower(trim((string) config('keycloak.auth_mode', 'legacy')));

        if ($mode !== 'legacy') {
            $token = trim((string) $request->bearerToken());

            if ($token !== '') {
                $user = $this->resolveUserFromOidcToken($token);

                if ($user instanceof User) {
                    Auth::guard()->setUser($user);

                    return $next($request);
                }

                return new JsonResponse([
                    'message' => 'Unauthorized. Invalid OIDC bearer token.',
                ], 401);
            }
        }

        if (Auth::check()) {
            return $next($request);
        }

        if ($mode === 'oidc') {
            return new JsonResponse([
                'message' => 'Unauthorized. Please login via OIDC.',
            ], 401);
        }

        return $this->authorizeViaApiKey($request, $next);
    }

    private function authorizeViaApiKey(Request $request, Closure $next): Response
    {
        $expectedApiKey = trim((string) config('services.investment_planner.api_key'));
        $providedApiKey = trim((string) $request->header('x-api-key', ''));

        if ($expectedApiKey === '') {
            return new JsonResponse([
                'message' => 'Server API key belum dikonfigurasi.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($providedApiKey === '' || ! hash_equals($expectedApiKey, $providedApiKey)) {
            return new JsonResponse([
                'message' => 'Unauthorized. API key tidak valid.',
            ], Response::HTTP_UNAUTHORIZED);
        }

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
