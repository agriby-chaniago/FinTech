<?php

use App\Models\User;

beforeEach(function () {
    config([
        'keycloak.enabled' => false,
        'keycloak.auth_mode' => 'legacy',
    ]);
});

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('users in oidc mode logout through keycloak end session endpoint', function () {
    $user = User::factory()->create();

    config([
        'keycloak.enabled' => true,
        'keycloak.auth_mode' => 'oidc',
        'keycloak.client_id' => 'fintrack-web',
        'keycloak.endpoints.logout' => 'http://127.0.0.1:8080/realms/fintech/protocol/openid-connect/logout',
        'keycloak.post_logout_redirect_uri' => 'http://127.0.0.1:8001',
    ]);

    $response = $this
        ->actingAs($user)
        ->withSession([
            'oidc_tokens' => [
                'id_token' => 'id-token-hint',
            ],
        ])
        ->post('/logout');

    $expectedRedirect = 'http://127.0.0.1:8080/realms/fintech/protocol/openid-connect/logout?'.http_build_query([
        'post_logout_redirect_uri' => 'http://127.0.0.1:8001',
        'client_id' => 'fintrack-web',
        'id_token_hint' => 'id-token-hint',
    ]);

    $this->assertGuest();
    $response->assertRedirect($expectedRedirect);
});
