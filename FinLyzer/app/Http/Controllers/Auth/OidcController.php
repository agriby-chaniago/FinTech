<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class OidcController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        return $this->buildAuthorizationRedirect($request, false);
    }

    public function register(Request $request): RedirectResponse
    {
        return $this->buildAuthorizationRedirect($request, true);
    }

    private function buildAuthorizationRedirect(Request $request, bool $isRegistration): RedirectResponse
    {
        if (! (bool) config('keycloak.enabled', false)) {
            return redirect('/')->withErrors([
                'oidc' => 'OIDC belum diaktifkan pada environment ini.',
            ]);
        }

        $authorizationEndpoint = (string) config('keycloak.endpoints.authorization', '');
        $clientId = (string) config('keycloak.client_id', '');
        $redirectUri = (string) config('keycloak.redirect_uri', '');

        $targetEndpoint = $authorizationEndpoint;

        if ($isRegistration) {
            $baseUrl = rtrim((string) config('keycloak.base_url', ''), '/');
            $realm = trim((string) config('keycloak.realm', ''));

            if ($baseUrl !== '' && $realm !== '') {
                $targetEndpoint = $baseUrl.'/realms/'.$realm.'/protocol/openid-connect/registrations';
            }
        }

        if ($targetEndpoint === '' || $clientId === '' || $redirectUri === '') {
            return redirect('/')->withErrors([
                'oidc' => 'Konfigurasi Keycloak belum lengkap.',
            ]);
        }

        $state = Str::random(40);
        $nonce = Str::random(40);
        $codeVerifier = Str::random(96);
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        $request->session()->put('oidc_state', $state);
        $request->session()->put('oidc_nonce', $nonce);
        $request->session()->put('oidc_code_verifier', $codeVerifier);

        $scopes = (array) config('keycloak.scopes', ['openid', 'profile', 'email']);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        return redirect()->away($targetEndpoint.'?'.$query);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect('/')->withErrors([
                'oidc' => (string) $request->input('error_description', 'Login OIDC gagal.'),
            ]);
        }

        $state = (string) $request->input('state', '');
        $expectedState = (string) $request->session()->pull('oidc_state', '');

        if ($state === '' || $expectedState === '' || ! hash_equals($expectedState, $state)) {
            return redirect('/')->withErrors([
                'oidc' => 'State OIDC tidak valid.',
            ]);
        }

        $code = (string) $request->input('code', '');

        if ($code === '') {
            return redirect('/')->withErrors([
                'oidc' => 'Authorization code tidak ditemukan.',
            ]);
        }

        $tokenEndpoint = (string) config('keycloak.endpoints.token', '');
        $clientId = (string) config('keycloak.client_id', '');
        $clientSecret = (string) config('keycloak.client_secret', '');
        $redirectUri = (string) config('keycloak.redirect_uri', '');

        if ($tokenEndpoint === '' || $clientId === '' || $redirectUri === '') {
            return redirect('/')->withErrors([
                'oidc' => 'Konfigurasi token endpoint Keycloak belum lengkap.',
            ]);
        }

        $tokenPayload = [
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ];

        $codeVerifier = (string) $request->session()->pull('oidc_code_verifier', '');

        if ($codeVerifier === '') {
            return redirect('/')->withErrors([
                'oidc' => 'PKCE code verifier tidak ditemukan di session.',
            ]);
        }

        $tokenPayload['code_verifier'] = $codeVerifier;

        if ($clientSecret !== '') {
            $tokenPayload['client_secret'] = $clientSecret;
        }

        $tokenResponse = Http::asForm()
            ->acceptJson()
            ->timeout((int) config('keycloak.http_timeout', 10))
            ->post($tokenEndpoint, $tokenPayload);

        if (! $tokenResponse->successful()) {
            return redirect('/')->withErrors([
                'oidc' => 'Gagal menukar authorization code ke token OIDC.',
            ]);
        }

        $tokenData = $tokenResponse->json();
        $accessToken = (string) data_get($tokenData, 'access_token', '');
        $idToken = (string) data_get($tokenData, 'id_token', '');

        $expectedNonce = (string) $request->session()->pull('oidc_nonce', '');

        if ($accessToken === '') {
            return redirect('/')->withErrors([
                'oidc' => 'Access token OIDC tidak tersedia.',
            ]);
        }

        if ($idToken === '') {
            return redirect('/')->withErrors([
                'oidc' => 'ID token OIDC tidak tersedia.',
            ]);
        }

        $idTokenClaims = $this->validateIdToken($idToken, $clientId, $expectedNonce);

        if (! is_array($idTokenClaims)) {
            return redirect('/')->withErrors([
                'oidc' => 'Validasi klaim ID token OIDC gagal.',
            ]);
        }

        $userinfoEndpoint = (string) config('keycloak.endpoints.userinfo', '');

        if ($userinfoEndpoint === '') {
            return redirect('/')->withErrors([
                'oidc' => 'Userinfo endpoint Keycloak belum dikonfigurasi.',
            ]);
        }

        $userinfoResponse = Http::acceptJson()
            ->withToken($accessToken)
            ->timeout((int) config('keycloak.http_timeout', 10))
            ->get($userinfoEndpoint);

        if (! $userinfoResponse->successful()) {
            return redirect('/')->withErrors([
                'oidc' => 'Gagal mengambil profil user dari Keycloak.',
            ]);
        }

        $userinfo = $userinfoResponse->json();

        $keycloakSub = trim((string) data_get($userinfo, 'sub', ''));

        if ($keycloakSub === '') {
            return redirect('/')->withErrors([
                'oidc' => 'Subject user dari Keycloak tidak tersedia.',
            ]);
        }

        $idTokenSub = trim((string) data_get($idTokenClaims, 'sub', ''));

        if ($idTokenSub !== '' && ! hash_equals($idTokenSub, $keycloakSub)) {
            return redirect('/')->withErrors([
                'oidc' => 'Subject ID token tidak konsisten dengan userinfo.',
            ]);
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
            ->where('email', $email)
            ->first();

        if (! $user instanceof User) {
            $user = User::query()
                ->where('keycloak_sub', $keycloakSub)
                ->first();
        }

        if ($user instanceof User) {
            $user->forceFill([
                'name' => $name,
                'email' => $email,
                'keycloak_sub' => $keycloakSub,
            ])->save();
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'keycloak_sub' => $keycloakSub,
                'password' => Hash::make(Str::random(40)),
            ]);
        }

        Auth::login($user);

        $request->session()->regenerate();
        $request->session()->put('oidc_tokens', [
            'access_token' => $accessToken,
            'refresh_token' => (string) data_get($tokenData, 'refresh_token', ''),
            'id_token' => $idToken,
            'expires_in' => (int) data_get($tokenData, 'expires_in', 0),
        ]);

        return redirect()->intended('/');
    }

    public function logout(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect('/');
        }

        $keycloakSub = $this->resolveAuthenticatedKeycloakSub();

        $logoutEndpoint = (string) config('keycloak.endpoints.logout', '');
        $postLogoutRedirectUri = (string) config('keycloak.post_logout_redirect_uri', '');
        $clientId = (string) config('keycloak.client_id', '');
        $idTokenHint = (string) data_get($request->session()->get('oidc_tokens', []), 'id_token', '');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($keycloakSub !== '') {
            $this->revokeLocalSessionsByKeycloakSub($keycloakSub);
            $this->propagateCrossServiceLogoutSync($keycloakSub);
        }

        if ($logoutEndpoint === '' || $postLogoutRedirectUri === '') {
            return redirect('/');
        }

        $query = [
            'post_logout_redirect_uri' => $postLogoutRedirectUri,
        ];

        if ($clientId !== '') {
            $query['client_id'] = $clientId;
        }

        if ($idTokenHint !== '') {
            $query['id_token_hint'] = $idTokenHint;
        }

        return redirect()->away($logoutEndpoint.'?'.http_build_query($query));
    }

    private function resolveAuthenticatedKeycloakSub(): string
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return '';
        }

        return trim((string) $user->keycloak_sub);
    }

    private function revokeLocalSessionsByKeycloakSub(string $keycloakSub): void
    {
        $user = User::query()
            ->where('keycloak_sub', $keycloakSub)
            ->first();

        if (! $user instanceof User) {
            return;
        }

        DB::table('sessions')
            ->where('user_id', $user->getKey())
            ->delete();
    }

    private function propagateCrossServiceLogoutSync(string $keycloakSub): void
    {
        $targets = (array) config('services.logout_sync.targets', []);
        $timeout = max(1, (int) config('services.logout_sync.timeout', 5));

        foreach ($targets as $target) {
            if (! is_array($target)) {
                continue;
            }

            $targetUrl = trim((string) data_get($target, 'url', ''));
            $apiKey = trim((string) data_get($target, 'api_key', ''));

            if ($targetUrl === '' || $apiKey === '' || $this->isCurrentApplicationUrl($targetUrl)) {
                continue;
            }

            try {
                Http::acceptJson()
                    ->timeout($timeout)
                    ->withHeaders([
                        'x-api-key' => $apiKey,
                    ])
                    ->post($targetUrl, [
                        'keycloak_sub' => $keycloakSub,
                    ]);
            } catch (Throwable $exception) {
                Log::warning('Failed to propagate cross-service logout sync request.', [
                    'target' => $targetUrl,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function isCurrentApplicationUrl(string $targetUrl): bool
    {
        $appUrl = trim((string) config('app.url', ''));

        if ($appUrl === '') {
            return false;
        }

        $targetParts = parse_url($targetUrl);
        $appParts = parse_url($appUrl);

        if (! is_array($targetParts) || ! is_array($appParts)) {
            return false;
        }

        $targetHost = strtolower((string) ($targetParts['host'] ?? ''));
        $appHost = strtolower((string) ($appParts['host'] ?? ''));

        if ($targetHost === '' || $appHost === '' || $targetHost !== $appHost) {
            return false;
        }

        return $this->resolveUrlPort($targetParts) === $this->resolveUrlPort($appParts);
    }

    /**
     * @param array<string, mixed> $urlParts
     */
    private function resolveUrlPort(array $urlParts): int
    {
        if (isset($urlParts['port']) && is_numeric($urlParts['port'])) {
            return (int) $urlParts['port'];
        }

        $scheme = strtolower((string) ($urlParts['scheme'] ?? 'http'));

        return $scheme === 'https' ? 443 : 80;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function validateIdToken(string $idToken, string $clientId, string $expectedNonce): ?array
    {
        $claims = $this->parseJwtClaims($idToken);

        if (! is_array($claims)) {
            return null;
        }

        $issuer = rtrim((string) config('keycloak.issuer', ''), '/');
        $tokenIssuer = rtrim((string) data_get($claims, 'iss', ''), '/');

        if ($issuer !== '' && $tokenIssuer === '') {
            return null;
        }

        if ($issuer !== '' && $tokenIssuer !== '' && ! hash_equals($issuer, $tokenIssuer)) {
            return null;
        }

        $audience = data_get($claims, 'aud');

        if (! $this->isAudienceValid($audience, $clientId)) {
            return null;
        }

        $exp = data_get($claims, 'exp');

        if (! is_numeric($exp)) {
            return null;
        }

        $expiresAt = (int) $exp;

        if ($expiresAt < (time() - 60)) {
            return null;
        }

        $nonce = trim((string) data_get($claims, 'nonce', ''));

        if ($expectedNonce === '' || $nonce === '' || ! hash_equals($expectedNonce, $nonce)) {
            return null;
        }

        $replayId = trim((string) data_get($claims, 'jti', ''));

        if ($replayId === '') {
            $replayId = hash('sha256', $idToken);
        }

        $cacheKey = 'oidc:id_token:seen:'.sha1($replayId);
        $ttlSeconds = max(60, $expiresAt - time());

        if (! Cache::add($cacheKey, true, now()->addSeconds($ttlSeconds))) {
            return null;
        }

        return $claims;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseJwtClaims(string $jwt): ?array
    {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            return null;
        }

        $decodedPayload = $this->base64UrlDecode($parts[1]);

        if ($decodedPayload === null) {
            return null;
        }

        $claims = json_decode($decodedPayload, true);

        return is_array($claims) ? $claims : null;
    }

    private function isAudienceValid(mixed $audience, string $clientId): bool
    {
        if ($clientId === '') {
            return false;
        }

        if (is_string($audience)) {
            return hash_equals($audience, $clientId);
        }

        if (is_array($audience)) {
            foreach ($audience as $audienceEntry) {
                if (is_string($audienceEntry) && hash_equals($audienceEntry, $clientId)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function base64UrlDecode(string $value): ?string
    {
        $remainder = strlen($value) % 4;

        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return is_string($decoded) ? $decoded : null;
    }
}
