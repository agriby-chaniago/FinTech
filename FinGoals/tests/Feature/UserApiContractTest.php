<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_goals_endpoint_requires_authenticated_principal(): void
    {
        config([
            'services.investment_planner.api_key' => 'planner-secret',
            'keycloak.auth_mode' => 'hybrid',
        ]);

        $response = $this
            ->withHeaders(['x-api-key' => 'planner-secret'])
            ->getJson('/api/goals');

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthorized. Authenticated user required.',
            ]);
    }

    public function test_user_plan_endpoint_requires_authenticated_principal(): void
    {
        config([
            'services.investment_planner.api_key' => 'planner-secret',
            'services.groq.api_key' => '',
            'keycloak.auth_mode' => 'hybrid',
        ]);

        $response = $this
            ->withHeaders(['x-api-key' => 'planner-secret'])
            ->postJson('/api/user/plan', [
                'total_income' => 5000000,
                'total_expense' => 2500000,
                'top_category' => 'food',
                'insight' => 'Arus kas stabil',
            ]);

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthorized. Authenticated user required.',
            ]);
    }

    public function test_user_plan_endpoint_uses_authenticated_user_context(): void
    {
        config([
            'services.groq.api_key' => '',
            'keycloak.auth_mode' => 'hybrid',
        ]);

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/api/user/plan', [
                'total_income' => 8000000,
                'total_expense' => 3000000,
                'top_category' => 'housing',
                'insight' => 'Surplus bulanan cukup konsisten',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('financial_plans', [
            'user_id' => $user->id,
            'total_income' => 8000000,
            'top_category' => 'housing',
        ]);
    }

    public function test_user_plan_endpoint_rejects_mismatched_user_id(): void
    {
        config([
            'services.groq.api_key' => '',
            'keycloak.auth_mode' => 'hybrid',
        ]);

        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/api/user/plan', [
                'user_id' => $otherUser->id,
                'total_income' => 6000000,
                'total_expense' => 2000000,
                'top_category' => 'transport',
                'insight' => 'Masih perlu optimasi',
            ]);

        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => 'user_id tidak sesuai dengan akun login.',
            ]);
    }

    public function test_user_goals_endpoint_scopes_data_to_authenticated_user(): void
    {
        config([
            'keycloak.auth_mode' => 'hybrid',
        ]);

        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Goal::query()->create([
            'user_id' => $user->id,
            'goal_name' => 'Dana darurat',
            'target_amount' => 10000000,
            'deadline' => '2026-12-31',
        ]);

        Goal::query()->create([
            'user_id' => $otherUser->id,
            'goal_name' => 'Liburan',
            'target_amount' => 5000000,
            'deadline' => '2026-11-30',
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson('/api/user/goals');

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.user_id', $user->id)
            ->assertJsonPath('0.goal_name', 'Dana darurat');
    }

    public function test_user_goals_endpoint_rejects_mismatched_user_id_query(): void
    {
        config([
            'keycloak.auth_mode' => 'hybrid',
        ]);

        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->getJson('/api/user/goals?user_id='.$otherUser->id);

        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => 'user_id tidak sesuai dengan akun login.',
            ]);
    }

    public function test_internal_plan_endpoint_remains_api_key_driven(): void
    {
        config([
            'services.investment_planner.api_key' => 'planner-secret',
            'services.groq.api_key' => '',
        ]);

        $user = User::factory()->create();

        $response = $this
            ->withHeaders(['x-api-key' => 'planner-secret'])
            ->postJson('/api/internal/plan', [
                'user_id' => $user->id,
                'total_income' => 7000000,
                'total_expense' => 2500000,
                'top_category' => 'food',
                'insight' => 'Kondisi keuangan aman',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('financial_plans', [
            'user_id' => $user->id,
            'total_income' => 7000000,
            'top_category' => 'food',
        ]);
    }
}
