<?php

use App\Models\Service3PlanResult;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    Config::set('services.service3_callback.api_key', 'service3-secret');
    Config::set('services.inter_service.api_key', 'legacy-secret');
    Config::set('services.inter_service.allow_legacy_fallback', true);
});

it('stores a new service3 plan result callback', function () {
    /** @var User $user */
    $user = User::factory()->createOne();

    $payload = [
        'user_id' => $user->id,
        'correlation_id' => 'corr-001',
        'analysis_id' => 'analysis-001',
        'status' => 'success',
        'summary_text' => 'Budget 30% for investasi rendah risiko.',
        'recommendations' => [
            ['type' => 'investment', 'product' => 'reksa dana pasar uang'],
        ],
        'goals' => [
            ['name' => 'Dana darurat', 'target' => 15000000],
        ],
        'raw_payload' => [
            'source' => 'service3',
            'version' => 'v1',
        ],
        'plan_period_start' => '2026-04-01',
        'plan_period_end' => '2026-04-30',
    ];

    $response = postJson('/api/service3/plans/callback', $payload, [
        'x-api-key' => 'service3-secret',
    ]);

    $response
        ->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.correlation_id', 'corr-001')
        ->assertJsonPath('data.status', 'success');

    expect(Service3PlanResult::query()->count())->toBe(1);

    $record = Service3PlanResult::query()->first();
    expect($record)->not()->toBeNull();
    expect($record->attempt_count)->toBe(1);
    expect($record->summary_text)->toBe('Budget 30% for investasi rendah risiko.');
});

it('updates existing callback record idempotently on retry', function () {
    /** @var User $user */
    $user = User::factory()->createOne();

    $payload = [
        'user_id' => $user->id,
        'correlation_id' => 'corr-dup-001',
        'analysis_id' => 'analysis-dup-001',
        'status' => 'success',
        'summary_text' => 'Versi awal.',
        'recommendations' => [['type' => 'initial']],
        'goals' => [['name' => 'Awal']],
        'raw_payload' => ['attempt' => 1],
        'plan_period_start' => '2026-04-01',
        'plan_period_end' => '2026-04-30',
    ];

    postJson('/api/service3/plans/callback', $payload, [
        'x-api-key' => 'service3-secret',
    ])->assertStatus(201);

    $retryPayload = array_merge($payload, [
        'summary_text' => 'Versi retry terbaru.',
        'raw_payload' => ['attempt' => 2],
    ]);

    $response = postJson('/api/service3/plans/callback', $retryPayload, [
        'x-api-key' => 'service3-secret',
    ]);

    $response
        ->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.summary_text', 'Versi retry terbaru.')
        ->assertJsonPath('data.attempt_count', 2);

    expect(Service3PlanResult::query()->count())->toBe(1);
});

it('rejects callback with invalid service key', function () {
    /** @var User $user */
    $user = User::factory()->createOne();

    $payload = [
        'user_id' => $user->id,
        'correlation_id' => 'corr-unauthorized',
        'status' => 'success',
        'raw_payload' => ['source' => 'service3'],
    ];

    postJson('/api/service3/plans/callback', $payload, [
        'x-api-key' => 'wrong-key',
    ])
        ->assertStatus(401)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Invalid service API key.');
});

it('rejects callback with invalid payload', function () {
    $response = postJson('/api/service3/plans/callback', [
        'status' => 'success',
    ], [
        'x-api-key' => 'service3-secret',
    ]);

    $response
        ->assertStatus(400)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Invalid request parameters.');
});

it('resolves callback user by keycloak_sub when local user_id mismatches', function () {
    /** @var User $wrongUser */
    $wrongUser = User::factory()->createOne([
        'email' => 'wrong-callback@example.com',
        'keycloak_sub' => 'kc-wrong-callback',
    ]);

    /** @var User $correctUser */
    $correctUser = User::factory()->createOne([
        'email' => 'correct-callback@example.com',
        'keycloak_sub' => 'kc-correct-callback',
    ]);

    $payload = [
        'user_id' => $wrongUser->id,
        'user_email' => $correctUser->email,
        'keycloak_sub' => $correctUser->keycloak_sub,
        'correlation_id' => 'corr-identity-hint-001',
        'status' => 'success',
        'summary_text' => 'Should map to correct user via keycloak_sub.',
        'raw_payload' => [
            'source' => 'service3',
            'identity' => 'hint-priority',
        ],
    ];

    postJson('/api/service3/plans/callback', $payload, [
        'x-api-key' => 'service3-secret',
    ])
        ->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user_id', $correctUser->id);

    $record = Service3PlanResult::query()
        ->where('correlation_id', 'corr-identity-hint-001')
        ->first();

    expect($record)->not()->toBeNull();
    expect($record?->user_id)->toBe($correctUser->id);
});

it('allows user to fetch own service3 plan results', function () {
    /** @var User $user */
    $user = User::factory()->createOne();

    actingAs($user);

    Service3PlanResult::create([
        'user_id' => $user->id,
        'correlation_id' => 'corr-list-001',
        'analysis_id' => 'analysis-list-001',
        'status' => 'success',
        'summary_text' => 'List result.',
        'recommendations' => [['name' => 'A']],
        'goals' => [['name' => 'B']],
        'raw_payload' => ['source' => 'service3'],
        'plan_period_start' => '2026-04-01',
        'plan_period_end' => '2026-04-30',
        'attempt_count' => 1,
        'last_attempted_at' => now(),
    ]);

    $response = getJson("/api/users/{$user->id}/service3/plans");

    $response
        ->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.user_id', $user->id)
        ->assertJsonPath('meta.total_items', 1)
        ->assertJsonPath('data.0.correlation_id', 'corr-list-001');
});

it('forbids user from fetching another user results', function () {
    /** @var User $owner */
    $owner = User::factory()->createOne();
    /** @var User $other */
    $other = User::factory()->createOne();

    Service3PlanResult::create([
        'user_id' => $other->id,
        'correlation_id' => 'corr-forbidden-001',
        'analysis_id' => 'analysis-forbidden-001',
        'status' => 'success',
        'summary_text' => 'Forbidden result.',
        'recommendations' => [['name' => 'A']],
        'goals' => [['name' => 'B']],
        'raw_payload' => ['source' => 'service3'],
        'attempt_count' => 1,
        'last_attempted_at' => now(),
    ]);

    actingAs($owner);

    getJson("/api/users/{$other->id}/service3/plans")
        ->assertStatus(403)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Forbidden.');
});

it('returns latest service3 plan result', function () {
    /** @var User $user */
    $user = User::factory()->createOne();

    actingAs($user);

    Service3PlanResult::create([
        'user_id' => $user->id,
        'correlation_id' => 'corr-latest-001',
        'analysis_id' => 'analysis-latest-001',
        'status' => 'success',
        'summary_text' => 'Older result.',
        'recommendations' => [['name' => 'Old']],
        'goals' => [['name' => 'Old']],
        'raw_payload' => ['version' => 1],
        'attempt_count' => 1,
        'last_attempted_at' => now(),
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    Service3PlanResult::create([
        'user_id' => $user->id,
        'correlation_id' => 'corr-latest-002',
        'analysis_id' => 'analysis-latest-002',
        'status' => 'success',
        'summary_text' => 'Newest result.',
        'recommendations' => [['name' => 'New']],
        'goals' => [['name' => 'New']],
        'raw_payload' => ['version' => 2],
        'attempt_count' => 1,
        'last_attempted_at' => now(),
    ]);

    getJson("/api/users/{$user->id}/service3/plans/latest")
        ->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.correlation_id', 'corr-latest-002')
        ->assertJsonPath('data.summary_text', 'Newest result.');
});
