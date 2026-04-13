<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OidcCallbackValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'keycloak.enabled' => true,
            'keycloak.client_id' => 'finlyzer-web',
            'keycloak.redirect_uri' => 'http://127.0.0.1:8002/auth/oidc/callback',
            'keycloak.issuer' => 'http://keycloak.test/realms/fintech',
            'keycloak.endpoints.token' => 'http://keycloak.test/token',
            'keycloak.endpoints.userinfo' => 'http://keycloak.test/userinfo',
            'keycloak.http_timeout' => 10,
        ]);
    }

    public function test_callback_rejects_invalid_nonce_claim(): void
    {
        $idToken = $this->buildOidcIdToken([
            'iss' => 'http://keycloak.test/realms/fintech',
            'aud' => 'finlyzer-web',
            'sub' => 'kc-lyzer-1',
            'nonce' => 'wrong-nonce',
            'exp' => time() + 600,
            'jti' => 'lyzer-nonce-fail',
        ]);

        Http::fake([
            'http://keycloak.test/token' => Http::response([
                'access_token' => 'lyzer-access-1',
                'id_token' => $idToken,
                'refresh_token' => 'lyzer-refresh-1',
                'expires_in' => 600,
            ], 200),
            'http://keycloak.test/userinfo' => Http::response([
                'sub' => 'kc-lyzer-1',
                'email' => 'lyzer1@example.com',
                'name' => 'Lyzer One',
            ], 200),
        ]);

        $response = $this
            ->withSession([
                'oidc_state' => 'lyzer-state-1',
                'oidc_nonce' => 'expected-nonce',
            ])
            ->get('/auth/oidc/callback?state=lyzer-state-1&code=valid-code');

        $response
            ->assertRedirect('/')
            ->assertSessionHasErrors('oidc');
    }

    public function test_callback_rejects_invalid_issuer_claim(): void
    {
        $idToken = $this->buildOidcIdToken([
            'iss' => 'http://wrong-issuer/realms/fintech',
            'aud' => 'finlyzer-web',
            'sub' => 'kc-lyzer-2',
            'nonce' => 'nonce-lyzer-2',
            'exp' => time() + 600,
            'jti' => 'lyzer-issuer-fail',
        ]);

        Http::fake([
            'http://keycloak.test/token' => Http::response([
                'access_token' => 'lyzer-access-2',
                'id_token' => $idToken,
                'refresh_token' => 'lyzer-refresh-2',
                'expires_in' => 600,
            ], 200),
            'http://keycloak.test/userinfo' => Http::response([
                'sub' => 'kc-lyzer-2',
                'email' => 'lyzer2@example.com',
                'name' => 'Lyzer Two',
            ], 200),
        ]);

        $response = $this
            ->withSession([
                'oidc_state' => 'lyzer-state-2',
                'oidc_nonce' => 'nonce-lyzer-2',
            ])
            ->get('/auth/oidc/callback?state=lyzer-state-2&code=valid-code');

        $response
            ->assertRedirect('/')
            ->assertSessionHasErrors('oidc');
    }

    /**
     * @param array<string, mixed> $claims
     */
    private function buildOidcIdToken(array $claims): string
    {
        $header = ['alg' => 'none', 'typ' => 'JWT'];

        $encode = static function (array $payload): string {
            $json = json_encode($payload, JSON_UNESCAPED_SLASHES);

            return rtrim(strtr(base64_encode($json === false ? '{}' : $json), '+/', '-_'), '=');
        };

        return $encode($header).'.'.$encode($claims).'.';
    }
}
