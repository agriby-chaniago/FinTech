<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
}
