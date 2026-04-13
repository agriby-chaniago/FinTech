<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FintrackFeedSyncStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AnalyzeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_analyze_requires_valid_api_key(): void
    {
        config([
            'services.analyzer.api_key' => 'my-secret-key',
            'services.groq.api_key' => '',
        ]);

        $payload = [
            'user_id' => 1,
            'transactions' => [
                ['amount' => 50000, 'category' => 'food', 'type' => 'expense'],
            ],
        ];

        $response = $this->postJson('/api/analyze', $payload);

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Invalid API key. Use x-api-key or service2_api_key header.',
            ]);
    }

    public function test_analyze_returns_result_and_persists_report(): void
    {
        config([
            'services.analyzer.api_key' => 'my-secret-key',
            'services.groq.api_key' => '',
        ]);

        $payload = [
            'user_id' => 1,
            'transactions' => [
                ['amount' => 3000000, 'category' => 'salary', 'type' => 'income'],
                ['amount' => 1000000, 'category' => 'food', 'type' => 'expense'],
                ['amount' => 500000, 'category' => 'transport', 'type' => 'expense'],
            ],
        ];

        $response = $this
            ->withHeaders(['x-api-key' => 'my-secret-key'])
            ->postJson('/api/analyze', $payload);

        $response->assertOk();
        $response->assertJsonStructure([
            'total_income',
            'total_expense',
            'transaction_count',
            'top_category',
            'category_breakdown',
            'insight',
        ]);

        $json = $response->json();

        $this->assertSame(3000000.0, (float) $json['total_income']);
        $this->assertSame(1500000.0, (float) $json['total_expense']);
        $this->assertSame(3, (int) $json['transaction_count']);
        $this->assertSame('food', $json['top_category']);
        $this->assertSame(66.67, (float) $json['category_breakdown']['food']);
        $this->assertSame(33.33, (float) $json['category_breakdown']['transport']);

        $this->assertDatabaseHas('analysis_reports', [
            'user_id' => 1,
            'transaction_count' => 3,
            'top_category' => 'food',
        ]);

        $this->assertDatabaseCount('category_breakdowns', 2);
        $this->assertDatabaseCount('ai_insights', 1);
    }

    public function test_analyze_accepts_service2_api_key_header(): void
    {
        config([
            'services.analyzer.api_key' => 'my-secret-key',
            'services.groq.api_key' => '',
        ]);

        $payload = [
            'user_id' => 99,
            'transactions' => [
                ['amount' => 2500000, 'category' => 'salary', 'type' => 'income'],
                ['amount' => 750000, 'category' => 'rent', 'type' => 'expense'],
            ],
        ];

        $response = $this
            ->withHeaders(['service2_api_key' => 'my-secret-key'])
            ->postJson('/api/analyze', $payload);

        $response
            ->assertOk()
            ->assertJsonPath('transaction_count', 2)
            ->assertJsonPath('top_category', 'rent');
    }

    public function test_analyze_auto_fetches_transactions_from_fintrack_feed(): void
    {
        config([
            'services.analyzer.api_key' => 'my-secret-key',
            'services.groq.api_key' => '',
            'services.fintrack_feed.base_url' => 'http://fintrack.local',
            'services.fintrack_feed.path' => '/api/service2/users/{user_id}/transactions-feed',
            'services.fintrack_feed.api_key' => 'fintrack1',
            'services.fintrack_feed.api_key_header' => 'x-api-key',
        ]);

        Http::fake([
            'http://fintrack.local/*' => Http::response([
                'data' => [
                    ['amount' => 50000, 'category' => 'food', 'type' => 'expense'],
                    ['amount' => 3000000, 'category' => 'salary', 'type' => 'income'],
                ],
                'meta' => [
                    'next_since' => '2026-04-13T10:00:00Z',
                ],
            ], 200),
        ]);

        $response = $this
            ->withHeaders(['x-api-key' => 'my-secret-key'])
            ->postJson('/api/analyze/auto', [
                'user_id' => 2,
                'since' => '2026-04-12T00:00:00Z',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('source.user_id', 2)
            ->assertJsonPath('source.fetched_transactions', 2)
            ->assertJsonPath('source.next_since', '2026-04-13T10:00:00Z')
            ->assertJsonPath('analysis.total_income', 3000000)
            ->assertJsonPath('analysis.total_expense', 50000)
            ->assertJsonPath('analysis.top_category', 'food');

        Http::assertSent(function ($request) {
            return $request->hasHeader('x-api-key', 'fintrack1')
                && str_contains((string) $request->url(), 'include_summary=0')
                && str_contains((string) $request->url(), 'since=2026-04-12T00%3A00%3A00Z');
        });
    }

    public function test_analyze_auto_returns_bad_gateway_when_feed_fails(): void
    {
        config([
            'services.analyzer.api_key' => 'my-secret-key',
            'services.groq.api_key' => '',
            'services.fintrack_feed.base_url' => 'http://fintrack.local',
            'services.fintrack_feed.path' => '/api/service2/users/{user_id}/transactions-feed',
            'services.fintrack_feed.api_key' => 'fintrack1',
            'services.fintrack_feed.api_key_header' => 'x-api-key',
        ]);

        Http::fake([
            'http://fintrack.local/*' => Http::response([
                'message' => 'upstream unavailable',
            ], 503),
        ]);

        $response = $this
            ->withHeaders(['x-api-key' => 'my-secret-key'])
            ->postJson('/api/analyze/auto', [
                'user_id' => 2,
            ]);

        $response
            ->assertStatus(502)
            ->assertJson([
                'message' => 'Gagal mengambil transaksi dari FinTrack feed.',
            ]);
    }

    public function test_analyze_auto_uses_default_user_and_saved_since_token(): void
    {
        config([
            'services.analyzer.api_key' => 'my-secret-key',
            'services.groq.api_key' => '',
            'services.fintrack_feed.base_url' => 'http://fintrack.local',
            'services.fintrack_feed.path' => '/api/service2/users/{user_id}/transactions-feed',
            'services.fintrack_feed.api_key' => 'fintrack1',
            'services.fintrack_feed.api_key_header' => 'x-api-key',
            'services.fintrack_feed.default_user_id' => 2,
            'services.fintrack_feed.use_saved_since' => true,
            'services.fintrack_feed.since_cache_prefix' => 'test_since_',
        ]);

        app(FintrackFeedSyncStateService::class)->saveSince(2, 'SYNC_TOKEN_1');

        Http::fake([
            'http://fintrack.local/*' => Http::response([
                'data' => [
                    ['amount' => 125000, 'category' => 'food', 'type' => 'expense'],
                    ['amount' => 2500000, 'category' => 'salary', 'type' => 'income'],
                ],
                'meta' => [
                    'next_since' => 'SYNC_TOKEN_2',
                ],
            ], 200),
        ]);

        $response = $this
            ->withHeaders(['x-api-key' => 'my-secret-key'])
            ->postJson('/api/analyze/auto', []);

        $response
            ->assertOk()
            ->assertJsonPath('source.user_id', 2)
            ->assertJsonPath('source.since_used', 'SYNC_TOKEN_1')
            ->assertJsonPath('source.since_source', 'saved')
            ->assertJsonPath('source.next_since', 'SYNC_TOKEN_2');

        $this->assertSame(
            'SYNC_TOKEN_2',
            app(FintrackFeedSyncStateService::class)->getSince(2)
        );
    }

    public function test_analyze_auto_run_works_with_api_key_only(): void
    {
        config([
            'services.analyzer.api_key' => 'my-secret-key',
            'services.groq.api_key' => '',
            'services.fintrack_feed.base_url' => 'http://fintrack.local',
            'services.fintrack_feed.path' => '/api/service2/users/{user_id}/transactions-feed',
            'services.fintrack_feed.api_key' => 'fintrack1',
            'services.fintrack_feed.api_key_header' => 'x-api-key',
            'services.fintrack_feed.default_user_id' => 2,
            'services.fintrack_feed.use_saved_since' => true,
            'services.fintrack_feed.since_cache_prefix' => 'test_since_',
        ]);

        Http::fake([
            'http://fintrack.local/*' => Http::response([
                'data' => [
                    ['amount' => 1000000, 'category' => 'salary', 'type' => 'income'],
                    ['amount' => 150000, 'category' => 'rent', 'type' => 'expense'],
                ],
                'meta' => [
                    'next_since' => 'AUTO_RUN_TOKEN_1',
                ],
            ], 200),
        ]);

        $response = $this
            ->withHeaders(['x-api-key' => 'my-secret-key'])
            ->postJson('/api/analyze/auto/run', []);

        $response
            ->assertOk()
            ->assertJsonPath('source.user_id', 2)
            ->assertJsonPath('source.since_source', 'none')
            ->assertJsonPath('analysis.top_category', 'rent');
    }

    public function test_internal_prefixed_analyze_endpoint_accepts_api_key(): void
    {
        config([
            'services.analyzer.api_key' => 'my-secret-key',
            'services.groq.api_key' => '',
        ]);

        $payload = [
            'user_id' => 21,
            'transactions' => [
                ['amount' => 2000000, 'category' => 'salary', 'type' => 'income'],
                ['amount' => 400000, 'category' => 'food', 'type' => 'expense'],
            ],
        ];

        $response = $this
            ->withHeaders(['x-api-key' => 'my-secret-key'])
            ->postJson('/api/internal/analyze', $payload);

        $response
            ->assertOk()
            ->assertJsonPath('transaction_count', 2)
            ->assertJsonPath('top_category', 'food');
    }

    public function test_user_latest_endpoint_requires_authenticated_principal(): void
    {
        config([
            'services.analyzer.api_key' => 'my-secret-key',
            'services.groq.api_key' => '',
            'keycloak.auth_mode' => 'hybrid',
        ]);

        $response = $this
            ->withHeaders(['x-api-key' => 'my-secret-key'])
            ->getJson('/api/user/analyze/auto/latest?user_id=10');

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthorized. Authenticated user required.',
            ]);
    }

    public function test_user_latest_endpoint_returns_data_for_authenticated_user(): void
    {
        config([
            'services.analyzer.api_key' => 'my-secret-key',
            'services.groq.api_key' => '',
            'services.fintrack_feed.since_cache_prefix' => 'test_since_',
            'keycloak.auth_mode' => 'hybrid',
        ]);

        $user = User::factory()->create();

        app(FintrackFeedSyncStateService::class)->saveSince($user->id, 'SYNC_USER_TOKEN');

        $this
            ->withHeaders(['x-api-key' => 'my-secret-key'])
            ->postJson('/api/analyze', [
                'user_id' => $user->id,
                'transactions' => [
                    ['amount' => 4500000, 'category' => 'salary', 'type' => 'income'],
                    ['amount' => 1000000, 'category' => 'rent', 'type' => 'expense'],
                ],
            ])
            ->assertOk();

        $response = $this
            ->actingAs($user)
            ->getJson('/api/user/analyze/auto/latest');

        $response
            ->assertOk()
            ->assertJsonPath('data.source_sync.user_id', $user->id)
            ->assertJsonPath('data.source_sync.next_since', 'SYNC_USER_TOKEN');
    }

    public function test_latest_for_service_c_returns_latest_payload(): void
    {
        config([
            'services.analyzer.api_key' => 'my-secret-key',
            'services.groq.api_key' => '',
            'services.fintrack_feed.since_cache_prefix' => 'test_since_',
        ]);

        app(FintrackFeedSyncStateService::class)->saveSince(10, 'SYNC_TOKEN_10');

        $this
            ->withHeaders(['x-api-key' => 'my-secret-key'])
            ->postJson('/api/analyze', [
                'user_id' => 10,
                'transactions' => [
                    ['amount' => 5000000, 'category' => 'salary', 'type' => 'income'],
                    ['amount' => 900000, 'category' => 'rent', 'type' => 'expense'],
                    ['amount' => 300000, 'category' => 'food', 'type' => 'expense'],
                ],
            ])
            ->assertOk();

        $response = $this
            ->withHeaders(['x-api-key' => 'my-secret-key'])
            ->getJson('/api/analyze/auto/latest?user_id=10');

        $response
            ->assertOk()
            ->assertJsonPath('data.source_sync.user_id', 10)
            ->assertJsonPath('data.source_sync.next_since', 'SYNC_TOKEN_10')
            ->assertJsonPath('data.metrics.total_income', 5000000)
            ->assertJsonPath('data.metrics.total_expense', 1200000)
            ->assertJsonPath('data.metrics.top_category', 'rent')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'run_id',
                    'executed_at',
                    'source_sync' => [
                        'user_id',
                        'fetched_transactions',
                        'next_since',
                    ],
                    'metrics' => [
                        'total_income',
                        'total_expense',
                        'transaction_count',
                        'top_category',
                    ],
                    'category_breakdown',
                    'ai_insight' => [
                        'provider',
                        'model',
                        'text',
                    ],
                ],
            ]);
    }

    public function test_latest_for_service_c_returns_not_found_when_no_report(): void
    {
        config([
            'services.analyzer.api_key' => 'my-secret-key',
            'services.groq.api_key' => '',
        ]);

        $this
            ->withHeaders(['x-api-key' => 'my-secret-key'])
            ->getJson('/api/analyze/auto/latest?user_id=99')
            ->assertStatus(404)
            ->assertJson([
                'message' => 'Belum ada hasil analisis yang bisa diambil Service C.',
            ]);
    }
}
