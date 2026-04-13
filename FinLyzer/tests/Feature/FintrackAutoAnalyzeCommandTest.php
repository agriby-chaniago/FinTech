<?php

namespace Tests\Feature;

use App\Services\FintrackFeedSyncStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FintrackAutoAnalyzeCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_runs_and_uses_saved_since(): void
    {
        config([
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
                    ['amount' => 150000, 'category' => 'food', 'type' => 'expense'],
                    ['amount' => 2500000, 'category' => 'salary', 'type' => 'income'],
                ],
                'meta' => [
                    'next_since' => 'SYNC_TOKEN_2',
                ],
            ], 200),
        ]);

        $this->artisan('fintrack:auto-analyze')
            ->expectsOutputToContain('Analisis otomatis berhasil.')
            ->assertExitCode(0);

        Http::assertSent(function ($request) {
            return $request->hasHeader('x-api-key', 'fintrack1')
                && str_contains((string) $request->url(), '/api/service2/users/2/transactions-feed')
                && str_contains((string) $request->url(), 'since=SYNC_TOKEN_1')
                && str_contains((string) $request->url(), 'include_summary=0');
        });

        $this->assertSame(
            'SYNC_TOKEN_2',
            app(FintrackFeedSyncStateService::class)->getSince(2)
        );
    }

    public function test_command_returns_failure_when_feed_fails(): void
    {
        config([
            'services.groq.api_key' => '',
            'services.fintrack_feed.base_url' => 'http://fintrack.local',
            'services.fintrack_feed.path' => '/api/service2/users/{user_id}/transactions-feed',
            'services.fintrack_feed.api_key' => 'fintrack1',
            'services.fintrack_feed.api_key_header' => 'x-api-key',
            'services.fintrack_feed.default_user_id' => 2,
            'services.fintrack_feed.use_saved_since' => false,
        ]);

        Http::fake([
            'http://fintrack.local/*' => Http::response([
                'message' => 'upstream unavailable',
            ], 503),
        ]);

        $this->artisan('fintrack:auto-analyze')
            ->expectsOutputToContain('Gagal mengambil transaksi dari FinTrack feed.')
            ->assertExitCode(1);
    }
}
