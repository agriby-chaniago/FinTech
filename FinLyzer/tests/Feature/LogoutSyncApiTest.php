<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class LogoutSyncApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_sync_revokes_local_sessions_for_matching_keycloak_sub(): void
    {
        config([
            'services.analyzer.api_key' => 'sync-secret',
            'services.analyzer.accepted_headers' => ['x-api-key'],
        ]);

        $targetUser = User::factory()->create([
            'keycloak_sub' => 'kc-sync-lyzer',
        ]);

        $otherUser = User::factory()->create([
            'keycloak_sub' => 'kc-other-lyzer',
        ]);

        DB::table('sessions')->insert([
            [
                'id' => Str::random(40),
                'user_id' => $targetUser->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'phpunit',
                'payload' => 'payload-a',
                'last_activity' => time(),
            ],
            [
                'id' => Str::random(40),
                'user_id' => $targetUser->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'phpunit',
                'payload' => 'payload-b',
                'last_activity' => time(),
            ],
            [
                'id' => Str::random(40),
                'user_id' => $otherUser->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'phpunit',
                'payload' => 'payload-c',
                'last_activity' => time(),
            ],
        ]);

        $response = $this
            ->withHeader('x-api-key', 'sync-secret')
            ->postJson('/api/internal/auth/logout-sync', [
                'keycloak_sub' => 'kc-sync-lyzer',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('revoked_sessions', 2);

        $this->assertDatabaseCount('sessions', 1);
        $this->assertDatabaseHas('sessions', [
            'user_id' => $otherUser->id,
        ]);
    }

    public function test_logout_sync_rejects_invalid_api_key(): void
    {
        config([
            'services.analyzer.api_key' => 'sync-secret',
            'services.analyzer.accepted_headers' => ['x-api-key'],
        ]);

        $response = $this
            ->withHeader('x-api-key', 'invalid')
            ->postJson('/api/internal/auth/logout-sync', [
                'keycloak_sub' => 'kc-sync-lyzer',
            ]);

        $response->assertUnauthorized();
    }
}
