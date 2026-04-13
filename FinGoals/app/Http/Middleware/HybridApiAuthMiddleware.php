<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class HybridApiAuthMiddleware
{
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
        $keycloakSub = trim((string) data_get($userinfo, 'sub', ''));

        if ($keycloakSub === '') {
            return null;
        }

        $email = strtolower(trim((string) data_get($userinfo, 'email', '')));

        if ($email === '') {
            $email = strtolower($keycloakSub).'@keycloak.local';
        }

        $name = trim((string) data_get($userinfo, 'name', ''));

        if ($name === '') {
            $name = trim((string) data_get($userinfo, 'preferred_username', ''));
        }

        if ($name === '') {
            $name = Str::before($email, '@');
        }

        if ($name === '') {
            $name = 'User';
        }

        $user = User::query()
            ->where('keycloak_sub', $keycloakSub)
            ->first();

        if (! $user instanceof User) {
            $user = User::query()
                ->where('email', $email)
                ->first();
        }

        if ($user instanceof User) {
            $user->forceFill([
                'name' => $name,
                'email' => $email,
                'keycloak_sub' => $keycloakSub,
            ])->save();

            return $user;
        }

        return User::create([
            'name' => $name,
            'email' => $email,
            'keycloak_sub' => $keycloakSub,
            'password' => Hash::make(Str::random(40)),
        ]);
    }
}
