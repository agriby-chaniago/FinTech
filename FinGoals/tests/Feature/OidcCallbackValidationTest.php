<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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
            'keycloak.client_id' => 'fingoals-web',
            'keycloak.redirect_uri' => 'http://127.0.0.1:8003/auth/oidc/callback',
            'keycloak.issuer' => 'http://keycloak.test/realms/fintech',
            'keycloak.endpoints.token' => 'http://keycloak.test/token',
            'keycloak.endpoints.userinfo' => 'http://keycloak.test/userinfo',
            'keycloak.http_timeout' => 10,
        ]);
    }

    public function test_callback_rejects_invalid_state(): void
    {
        $response = $this
            ->withSession([
                'oidc_state' => 'expected-state',
                'oidc_nonce' => 'nonce-any',
            ])
            ->get('/auth/oidc/callback?state=tampered-state&code=valid-code');

        $response
            ->assertRedirect('/planner')
            ->assertSessionHasErrors('oidc');

        Http::assertNothingSent();
    }

    public function test_callback_rejects_invalid_nonce_claim(): void
    {
        $idToken = $this->buildOidcIdToken([
            'iss' => 'http://keycloak.test/realms/fintech',
            'aud' => 'fingoals-web',
            'sub' => 'kc-goals-1',
            'nonce' => 'wrong-nonce',
            'exp' => time() + 600,
            'jti' => 'goals-nonce-fail',
        ]);

        Http::fake([
            'http://keycloak.test/token' => Http::response([
                'access_token' => 'goals-access-1',
                'id_token' => $idToken,
                'refresh_token' => 'goals-refresh-1',
                'expires_in' => 600,
            ], 200),
            'http://keycloak.test/userinfo' => Http::response([
                'sub' => 'kc-goals-1',
                'email' => 'goals1@example.com',
                'name' => 'Goals One',
            ], 200),
        ]);

        $response = $this
            ->withSession([
                'oidc_state' => 'goals-state-1',
                'oidc_nonce' => 'expected-nonce',
            ])
            ->get('/auth/oidc/callback?state=goals-state-1&code=valid-code');

        $response
            ->assertRedirect('/planner')
            ->assertSessionHasErrors('oidc');
    }

    public function test_callback_rejects_invalid_issuer_claim(): void
    {
        $idToken = $this->buildOidcIdToken([
            'iss' => 'http://wrong-issuer/realms/fintech',
            'aud' => 'fingoals-web',
            'sub' => 'kc-goals-2',
            'nonce' => 'nonce-goals-2',
            'exp' => time() + 600,
            'jti' => 'goals-issuer-fail',
        ]);

        Http::fake([
            'http://keycloak.test/token' => Http::response([
                'access_token' => 'goals-access-2',
                'id_token' => $idToken,
                'refresh_token' => 'goals-refresh-2',
                'expires_in' => 600,
            ], 200),
            'http://keycloak.test/userinfo' => Http::response([
                'sub' => 'kc-goals-2',
                'email' => 'goals2@example.com',
                'name' => 'Goals Two',
            ], 200),
        ]);

        $response = $this
            ->withSession([
                'oidc_state' => 'goals-state-2',
                'oidc_nonce' => 'nonce-goals-2',
            ])
            ->get('/auth/oidc/callback?state=goals-state-2&code=valid-code');

        $response
            ->assertRedirect('/planner')
            ->assertSessionHasErrors('oidc');
    }

    public function test_callback_rejects_invalid_audience_claim(): void
    {
        $idToken = $this->buildOidcIdToken([
            'iss' => 'http://keycloak.test/realms/fintech',
            'aud' => 'other-client',
            'sub' => 'kc-goals-3',
            'nonce' => 'nonce-goals-3',
            'exp' => time() + 600,
            'jti' => 'goals-audience-fail',
        ]);

        Http::fake([
            'http://keycloak.test/token' => Http::response([
                'access_token' => 'goals-access-3',
                'id_token' => $idToken,
                'refresh_token' => 'goals-refresh-3',
                'expires_in' => 600,
            ], 200),
            'http://keycloak.test/userinfo' => Http::response([
                'sub' => 'kc-goals-3',
                'email' => 'goals3@example.com',
                'name' => 'Goals Three',
            ], 200),
        ]);

        $response = $this
            ->withSession([
                'oidc_state' => 'goals-state-3',
                'oidc_nonce' => 'nonce-goals-3',
            ])
            ->get('/auth/oidc/callback?state=goals-state-3&code=valid-code');

        $response
            ->assertRedirect('/planner')
            ->assertSessionHasErrors('oidc');
    }

    public function test_callback_rejects_expired_id_token(): void
    {
        $idToken = $this->buildOidcIdToken([
            'iss' => 'http://keycloak.test/realms/fintech',
            'aud' => 'fingoals-web',
            'sub' => 'kc-goals-4',
            'nonce' => 'nonce-goals-4',
            'exp' => time() - 120,
            'jti' => 'goals-expired-token',
        ]);

        Http::fake([
            'http://keycloak.test/token' => Http::response([
                'access_token' => 'goals-access-4',
                'id_token' => $idToken,
                'refresh_token' => 'goals-refresh-4',
                'expires_in' => 600,
            ], 200),
            'http://keycloak.test/userinfo' => Http::response([
                'sub' => 'kc-goals-4',
                'email' => 'goals4@example.com',
                'name' => 'Goals Four',
            ], 200),
        ]);

        $response = $this
            ->withSession([
                'oidc_state' => 'goals-state-4',
                'oidc_nonce' => 'nonce-goals-4',
            ])
            ->get('/auth/oidc/callback?state=goals-state-4&code=valid-code');

        $response
            ->assertRedirect('/planner')
            ->assertSessionHasErrors('oidc');
    }

    public function test_callback_rejects_replayed_id_token(): void
    {
        $replayJti = 'goals-replay-jti';
        Cache::put('oidc:id_token:seen:'.sha1($replayJti), true, now()->addMinutes(10));

        $idToken = $this->buildOidcIdToken([
            'iss' => 'http://keycloak.test/realms/fintech',
            'aud' => 'fingoals-web',
            'sub' => 'kc-goals-5',
            'nonce' => 'nonce-goals-5',
            'exp' => time() + 600,
            'jti' => $replayJti,
        ]);

        Http::fake([
            'http://keycloak.test/token' => Http::response([
                'access_token' => 'goals-access-5',
                'id_token' => $idToken,
                'refresh_token' => 'goals-refresh-5',
                'expires_in' => 600,
            ], 200),
            'http://keycloak.test/userinfo' => Http::response([
                'sub' => 'kc-goals-5',
                'email' => 'goals5@example.com',
                'name' => 'Goals Five',
            ], 200),
        ]);

        $response = $this
            ->withSession([
                'oidc_state' => 'goals-state-5',
                'oidc_nonce' => 'nonce-goals-5',
            ])
            ->get('/auth/oidc/callback?state=goals-state-5&code=valid-code');

        $response
            ->assertRedirect('/planner')
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
