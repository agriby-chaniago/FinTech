<?php

namespace Tests\Feature;

use Tests\TestCase;

class WebLoginFlowTest extends TestCase
{
    public function test_guest_is_redirected_to_login_page_in_oidc_mode(): void
    {
        config([
            'keycloak.auth_mode' => 'oidc',
            'keycloak.enabled' => true,
        ]);

        $response = $this->get('/planner');

        $response->assertRedirect('/login');
    }

    public function test_login_page_shows_keycloak_button_when_oidc_is_enabled(): void
    {
        config([
            'keycloak.auth_mode' => 'oidc',
            'keycloak.enabled' => true,
        ]);

        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSee('Masuk dengan Keycloak');
    }

    public function test_login_page_shows_keycloak_warning_when_disabled(): void
    {
        config([
            'keycloak.auth_mode' => 'oidc',
            'keycloak.enabled' => false,
        ]);

        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSeeText('KEYCLOAK_ENABLED masih false. Aktifkan Keycloak di .env FinGoals sebelum login.');
    }
}
