<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OidcController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (! (bool) config('keycloak.enabled', false)) {
            return redirect('/')->withErrors([
                'oidc' => 'OIDC belum diaktifkan pada environment ini.',
            ]);
        }

        $authorizationEndpoint = (string) config('keycloak.endpoints.authorization', '');
        $clientId = (string) config('keycloak.client_id', '');
        $redirectUri = (string) config('keycloak.redirect_uri', '');

        if ($authorizationEndpoint === '' || $clientId === '' || $redirectUri === '') {
            return redirect('/')->withErrors([
                'oidc' => 'Konfigurasi Keycloak belum lengkap.',
            ]);
        }

        $state = Str::random(40);
        $nonce = Str::random(40);

        $request->session()->put('oidc_state', $state);
        $request->session()->put('oidc_nonce', $nonce);

        $scopes = (array) config('keycloak.scopes', ['openid', 'profile', 'email']);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'state' => $state,
            'nonce' => $nonce,
        ]);

        return redirect()->away($authorizationEndpoint.'?'.$query);
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
        $logoutEndpoint = (string) config('keycloak.endpoints.logout', '');
        $postLogoutRedirectUri = (string) config('keycloak.post_logout_redirect_uri', '');
        $clientId = (string) config('keycloak.client_id', '');
        $idTokenHint = (string) data_get($request->session()->get('oidc_tokens', []), 'id_token', '');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

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
