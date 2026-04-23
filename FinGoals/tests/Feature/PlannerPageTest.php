<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Request;
use Tests\TestCase;

class PlannerPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_planner_page_uses_internal_fallback_when_bearer_token_is_invalid(): void
    {
        config([
            'keycloak.auth_mode' => 'hybrid',
            'services.service_b_analyzer.base_url' => 'http://finlyzer.local',
            'services.service_b_analyzer.latest_path' => '/api/user/analyze/auto/latest',
            'services.service_b_analyzer.internal_latest_path' => '/api/internal/analyze/auto/latest',
            'services.service_b_analyzer.api_key' => 'fintrack1',
            'services.service_b_analyzer.api_key_header' => 'x-api-key',
        ]);

        $user = User::factory()->create([
            'email' => 'putraputri@gmail.com',
            'keycloak_sub' => '4e86e9ad-c5c1-4e90-8ad8-c82fc2c0a389',
        ]);

        Http::fake([
            'http://finlyzer.local/api/user/analyze/auto/latest*' => Http::response([
                'message' => 'Unauthorized. Invalid OIDC bearer token.',
            ], 401),
            'http://finlyzer.local/api/internal/analyze/auto/latest*' => Http::response([
                'message' => 'Payload terbaru untuk Service C berhasil diambil.',
                'data' => [
                    'executed_at' => '2026-04-22T11:15:37+00:00',
                    'metrics' => [
                        'total_income' => 1250000,
                        'total_expense' => 810000,
                        'transaction_count' => 4,
                        'top_category' => 'transport',
                        'net_balance' => 440000,
                        'savings_rate' => 35.2,
                        'financial_health' => 'Sehat',
                        'summary' => 'Arus kas positif dengan rasio tabungan yang sehat.',
                    ],
                    'category_breakdown' => [
                        [
                            'category' => 'transport',
                            'amount' => 800000,
                            'percentage' => 98.77,
                        ],
                        [
                            'category' => 'makan',
                            'amount' => 10000,
                            'percentage' => 1.23,
                        ],
                    ],
                    'ai_insight' => [
                        'text' => 'Kondisi keuangan sehat.',
                    ],
                    'source_sync' => [
                        'user_id' => $user->id,
                        'fetched_transactions' => 4,
                        'next_since' => null,
                    ],
                ],
            ], 200),
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession([
                'oidc_tokens' => [
                    'access_token' => 'invalid-bearer-token',
                ],
            ])
            ->get('/planner');

        $response
            ->assertOk()
            ->assertSee('Rencana Otomatis Anda', false)
            ->assertSee('Total Income', false)
            ->assertSee('Rp 1.250.000', false);

        Http::assertSentCount(2);
    }

    public function test_planner_store_sends_callback_to_service_a(): void
    {
        config([
            'keycloak.auth_mode' => 'hybrid',
            'services.groq.api_key' => '',
            'services.service_b_analyzer.base_url' => 'http://finlyzer.local',
            'services.service_b_analyzer.latest_path' => '/api/user/analyze/auto/latest',
            'services.service_b_analyzer.internal_latest_path' => '/api/internal/analyze/auto/latest',
            'services.service_b_analyzer.api_key' => 'fintrack1',
            'services.service_b_analyzer.api_key_header' => 'x-api-key',
            'services.service1_callback.enabled' => true,
            'services.service1_callback.url' => 'http://fintrack.local/api/service3/plans/callback',
            'services.service1_callback.api_key' => 'fintrack3',
        ]);

        $user = User::factory()->create([
            'email' => 'putraputri@gmail.com',
            'keycloak_sub' => '4e86e9ad-c5c1-4e90-8ad8-c82fc2c0a389',
        ]);

        Http::fake([
            'http://finlyzer.local/api/user/analyze/auto/latest*' => Http::response([
                'message' => 'Unauthorized. Invalid OIDC bearer token.',
            ], 401),
            'http://finlyzer.local/api/internal/analyze/auto/latest*' => Http::response([
                'message' => 'Payload terbaru untuk Service C berhasil diambil.',
                'data' => [
                    'metrics' => [
                        'total_income' => 1250000,
                        'total_expense' => 810000,
                        'top_category' => 'transport',
                    ],
                    'ai_insight' => [
                        'text' => 'Kondisi keuangan sehat.',
                    ],
                ],
            ], 200),
            'http://fintrack.local/api/service3/plans/callback' => Http::response([
                'success' => true,
            ], 201),
        ]);

        $this
            ->actingAs($user)
            ->withSession([
                'oidc_tokens' => [
                    'access_token' => 'invalid-bearer-token',
                ],
            ])
            ->post('/planner')
            ->assertRedirect('/planner');

        Http::assertSent(function (Request $request): bool {
            if ($request->url() !== 'http://fintrack.local/api/service3/plans/callback') {
                return false;
            }

            $data = $request->data();

            return $request->hasHeader('x-api-key', 'fintrack3')
                && (int) data_get($data, 'user_id') > 0
                && (string) data_get($data, 'user_email') === 'putraputri@gmail.com'
                && (string) data_get($data, 'keycloak_sub') === '4e86e9ad-c5c1-4e90-8ad8-c82fc2c0a389'
                && (string) data_get($data, 'status') === 'success'
                && is_string(data_get($data, 'correlation_id'))
                && str_starts_with((string) data_get($data, 'correlation_id'), 'web-plan-')
                && is_array(data_get($data, 'recommendations'));
        });
    }
}
