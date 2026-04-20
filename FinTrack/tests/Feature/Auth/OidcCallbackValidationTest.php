<?php

namespace Tests\Feature\Auth;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OidcCallbackValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'keycloak.enabled' => true,
            'keycloak.client_id' => 'fintrack-web',
            'keycloak.redirect_uri' => 'http://127.0.0.1:8001/auth/oidc/callback',
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
            'aud' => 'fintrack-web',
            'sub' => 'kc-user-1',
            'nonce' => 'wrong-nonce',
            'exp' => time() + 600,
            'jti' => 'nonce-fail-token',
        ]);

        Http::fake([
            'http://keycloak.test/token' => Http::response([
                'access_token' => 'access-1',
                'id_token' => $idToken,
                'refresh_token' => 'refresh-1',
                'expires_in' => 600,
            ], 200),
            'http://keycloak.test/userinfo' => Http::response([
                'sub' => 'kc-user-1',
                'email' => 'user1@example.com',
                'name' => 'User One',
            ], 200),
        ]);

        $response = $this
            ->withSession([
                'oidc_state' => 'state-1',
                'oidc_nonce' => 'expected-nonce',
            ])
            ->get('/auth/oidc/callback?state=state-1&code=valid-code');

        $response
            ->assertRedirect('/')
            ->assertSessionHasErrors('oidc');
    }

    public function test_callback_rejects_invalid_issuer_claim(): void
    {
        $idToken = $this->buildOidcIdToken([
            'iss' => 'http://wrong-issuer/realms/fintech',
            'aud' => 'fintrack-web',
            'sub' => 'kc-user-2',
            'nonce' => 'nonce-2',
            'exp' => time() + 600,
            'jti' => 'issuer-fail-token',
        ]);

        Http::fake([
            'http://keycloak.test/token' => Http::response([
                'access_token' => 'access-2',
                'id_token' => $idToken,
                'refresh_token' => 'refresh-2',
                'expires_in' => 600,
            ], 200),
            'http://keycloak.test/userinfo' => Http::response([
                'sub' => 'kc-user-2',
                'email' => 'user2@example.com',
                'name' => 'User Two',
            ], 200),
        ]);

        $response = $this
            ->withSession([
                'oidc_state' => 'state-2',
                'oidc_nonce' => 'nonce-2',
            ])
            ->get('/auth/oidc/callback?state=state-2&code=valid-code');

        $response
            ->assertRedirect('/')
            ->assertSessionHasErrors('oidc');
    }

    public function test_callback_rejects_replayed_id_token_based_on_jti(): void
    {
        $idToken = $this->buildOidcIdToken([
            'iss' => 'http://keycloak.test/realms/fintech',
            'aud' => 'fintrack-web',
            'sub' => 'kc-user-3',
            'nonce' => 'nonce-3',
            'exp' => time() + 600,
            'jti' => 'replay-token-jti',
        ]);

        Http::fake([
            'http://keycloak.test/token' => Http::response([
                'access_token' => 'access-3',
                'id_token' => $idToken,
                'refresh_token' => 'refresh-3',
                'expires_in' => 600,
            ], 200),
            'http://keycloak.test/userinfo' => Http::response([], 200),
        ]);

        $first = $this
            ->withSession([
                'oidc_state' => 'state-3a',
                'oidc_nonce' => 'nonce-3',
            ])
            ->get('/auth/oidc/callback?state=state-3a&code=valid-code');

        $first
            ->assertRedirect('/')
            ->assertSessionHasErrors('oidc');

        $second = $this
            ->withSession([
                'oidc_state' => 'state-3b',
                'oidc_nonce' => 'nonce-3',
            ])
            ->get('/auth/oidc/callback?state=state-3b&code=valid-code');

        $second
            ->assertRedirect('/')
            ->assertSessionHasErrors('oidc');

        $userinfoCalls = collect(Http::recorded())
            ->filter(fn (array $entry): bool => $entry[0]->url() === 'http://keycloak.test/userinfo')
            ->count();

        $this->assertSame(1, $userinfoCalls);
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
