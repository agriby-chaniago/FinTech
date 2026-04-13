<?php

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use function Pest\Laravel\getJson;

beforeEach(function () {
    Config::set('services.inter_service.api_key', 'gateway-secret');
    Config::set('services.inter_service.allow_legacy_fallback', true);
    Config::set('services.analyzer.url', 'http://localhost:8002/api/internal/analyze');
    Config::set('services.analyzer.api_key', 'analyzer-secret');
    Config::set('services.planner.url', 'http://localhost:8003/api/internal/plan');
    Config::set('services.planner.api_key', 'planner-secret');
});

it('generates finance summary through internal analyzer and planner endpoints', function () {
    $user = User::factory()->create();
    $token = $user->createToken('finance-summary-token')->plainTextToken;

    Transaction::create([
        'user_id' => $user->id,
        'amount' => 8000000,
        'description' => 'Gaji bulanan',
        'category' => 'salary',
        'type' => 'income',
        'transaction_date' => '2026-04-13',
        'tanggal' => '2026-04-13',
        'kategori' => 'pemasukan',
        'deskripsi' => 'Gaji bulanan',
        'nominal' => 8000000,
    ]);

    Transaction::create([
        'user_id' => $user->id,
        'amount' => 2500000,
        'description' => 'Sewa bulanan',
        'category' => 'housing',
        'type' => 'expense',
        'transaction_date' => '2026-04-12',
        'tanggal' => '2026-04-12',
        'kategori' => 'pengeluaran',
        'deskripsi' => 'Sewa bulanan',
        'nominal' => 2500000,
    ]);

    Http::fake([
        'http://localhost:8002/api/internal/analyze' => Http::response([
            'total_income' => 8000000,
            'total_expense' => 2500000,
            'transaction_count' => 2,
            'top_category' => 'housing',
            'summary' => 'Arus kas positif.',
            'insight' => 'Pertahankan dana darurat dan evaluasi pengeluaran tetap.',
        ], 200),
        'http://localhost:8003/api/internal/plan' => Http::response([
            'saving_plan' => 2200000,
            'investment_recommendation' => 'Reksa dana pasar uang.',
            'risk_level' => 'low',
        ], 201),
    ]);

    $response = getJson('/api/finance/summary?saving_percentage=30', [
        'Authorization' => "Bearer {$token}",
        'x-api-key' => 'gateway-secret',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Finance summary generated successfully.')
        ->assertJsonPath('data.analysis.total_income', 8000000)
        ->assertJsonPath('data.analysis.total_expense', 2500000)
        ->assertJsonPath('data.plan.saving_plan', 2200000)
        ->assertJsonPath('data.plan.risk_level', 'low');

    $recorded = Http::recorded();

    expect($recorded)->toHaveCount(2);

    $analyzerRequest = collect($recorded)
        ->map(fn (array $entry) => $entry[0])
        ->first(fn ($request) => str_contains((string) $request->url(), '/api/internal/analyze'));

    expect($analyzerRequest)->not()->toBeNull();

    $analyzerPayload = $analyzerRequest->data();

    expect($analyzerRequest->method())->toBe('POST');
    expect((string) ($analyzerRequest->header('x-api-key')[0] ?? ''))->toBe('analyzer-secret');
    expect((int) ($analyzerPayload['user_id'] ?? 0))->toBe($user->id);
    expect(collect($analyzerPayload['transactions'] ?? [])->count())->toBe(2);

    $plannerRequest = collect($recorded)
        ->map(fn (array $entry) => $entry[0])
        ->first(fn ($request) => str_contains((string) $request->url(), '/api/internal/plan'));

    expect($plannerRequest)->not()->toBeNull();

    $plannerPayload = $plannerRequest->data();

    expect($plannerRequest->method())->toBe('POST');
    expect((string) ($plannerRequest->header('x-api-key')[0] ?? ''))->toBe('planner-secret');
    expect((int) ($plannerPayload['user_id'] ?? 0))->toBe($user->id);
    expect((int) ($plannerPayload['total_income'] ?? -1))->toBe(8000000);
    expect((int) ($plannerPayload['total_expense'] ?? -1))->toBe(2500000);
    expect((string) ($plannerPayload['top_category'] ?? ''))->toBe('housing');
    expect((string) ($plannerPayload['insight'] ?? ''))->toBe('Pertahankan dana darurat dan evaluasi pengeluaran tetap.');
    expect((float) ($plannerPayload['saving_percentage'] ?? -1))->toBe(30.0);
    expect(array_key_exists('transactions', $plannerPayload))->toBeFalse();
    expect(array_key_exists('analysis', $plannerPayload))->toBeFalse();
});

it('requires valid service api key on finance summary route', function () {
    $user = User::factory()->create();
    $token = $user->createToken('finance-summary-token')->plainTextToken;

    getJson('/api/finance/summary', [
        'Authorization' => "Bearer {$token}",
        'x-api-key' => 'wrong-key',
    ])
        ->assertStatus(401)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Invalid service API key.');
});
