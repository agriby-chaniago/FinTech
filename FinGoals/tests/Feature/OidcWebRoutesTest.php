<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OidcWebRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_guest_to_oidc_when_enabled(): void
    {
        config([
            'keycloak.enabled' => true,
        ]);

        $response = $this->get('/');

        $response->assertRedirect('/auth/oidc/redirect');
    }

    public function test_root_falls_back_to_welcome_when_oidc_disabled(): void
    {
        config([
            'keycloak.enabled' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('welcome');
    }

    public function test_oidc_logout_route_available_for_authenticated_user(): void
    {
        config([
            'keycloak.endpoints.logout' => '',
            'keycloak.post_logout_redirect_uri' => '',
        ]);

        /** @var User $user */
        $user = User::factory()->createOne();

        $response = $this
            ->actingAs($user)
            ->post('/auth/oidc/logout');

        $response->assertRedirect('/');
    }
}
