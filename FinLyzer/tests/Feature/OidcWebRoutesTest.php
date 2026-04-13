<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OidcWebRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_oidc_redirect_route_exists_for_guest(): void
    {
        config([
            'keycloak.enabled' => false,
        ]);

        $response = $this->get('/auth/oidc/redirect');

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('oidc');
    }

    public function test_oidc_logout_requires_authentication(): void
    {
        $response = $this->post('/auth/oidc/logout');

        $response->assertRedirect('/');
    }

    public function test_oidc_logout_route_available_for_authenticated_user(): void
    {
        config([
            'keycloak.endpoints.logout' => '',
            'keycloak.post_logout_redirect_uri' => '',
        ]);

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/auth/oidc/logout');

        $response->assertRedirect('/');
    }
}
