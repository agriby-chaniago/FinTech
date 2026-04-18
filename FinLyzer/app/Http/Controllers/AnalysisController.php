<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyzeRequest;
use App\Http\Requests\AnalyzeAutoRequest;
use App\Models\AnalysisReport;
use App\Models\User;
use App\Services\FintrackAutoAnalyzeService;
use App\Services\FintrackFeedSyncStateService;
use App\Services\FinancialAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Throwable;

class AnalysisController extends Controller
{
    public function __construct(
        private readonly FinancialAnalysisService $financialAnalysisService,
        private readonly FintrackAutoAnalyzeService $fintrackAutoAnalyzeService,
        private readonly FintrackFeedSyncStateService $fintrackFeedSyncStateService
    ) {
    }

    public function analyze(AnalyzeRequest $request): JsonResponse
    {
        $result = $this->financialAnalysisService->analyze(
            $request->userId(),
            $request->transactions()
        );

        return response()->json($result);
    }

    public function analyzeAuto(AnalyzeAutoRequest $request): JsonResponse
    {
        try {
            $result = $this->fintrackAutoAnalyzeService->run(
                $request->resolvedUserId(),
                $request->since(),
                $request->includeSummary(),
                $request->useSavedSince()
            );

            $resolvedEmail = $request->resolvedEmail();

            if (is_array($result['source'] ?? null) && is_string($resolvedEmail) && $resolvedEmail !== '') {
                $result['source']['user_email'] = $resolvedEmail;
            }
        } catch (Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 502);
        }

        return response()->json($result);
    }

    public function analyzeAutoRun(): JsonResponse
    {
        try {
            $authenticatedUser = Auth::user();
            $authenticatedUserId = $authenticatedUser instanceof User ? (int) $authenticatedUser->id : Auth::id();

            $result = $this->fintrackAutoAnalyzeService->run(
                is_numeric($authenticatedUserId) ? (int) $authenticatedUserId : null
            );

            $resolvedEmail = null;

            if ($authenticatedUser instanceof User) {
                $resolvedEmail = strtolower(trim((string) $authenticatedUser->email));
            } else {
                $resolvedUserId = data_get($result, 'source.user_id');

                if (is_numeric($resolvedUserId)) {
                    $resolvedUser = User::query()->find((int) $resolvedUserId);

                    if ($resolvedUser instanceof User) {
                        $resolvedEmail = strtolower(trim((string) $resolvedUser->email));
                    }
                }
            }

            if (is_array($result['source'] ?? null) && is_string($resolvedEmail) && $resolvedEmail !== '') {
                $result['source']['user_email'] = $resolvedEmail;
            }
        } catch (Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 502);
        }

        return response()->json($result);
    }

    public function latestForServiceC(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = AnalysisReport::query()
            ->with([
                'categoryBreakdowns:id,analysis_report_id,category,amount,percentage',
                'aiInsight:id,analysis_report_id,provider,model,insight',
            ])
            ->orderByDesc('id');

        $userId = $validated['user_id'] ?? null;

        $authenticatedUserId = Auth::id();

        if (is_numeric($authenticatedUserId)) {
            if (is_numeric($userId) && (int) $userId !== (int) $authenticatedUserId) {
                return response()->json([
                    'message' => 'user_id tidak sesuai dengan akun login.',
                ], 403);
            }

            $query->where('user_id', (int) $authenticatedUserId);
        } elseif (is_numeric($userId)) {
            $query->where('user_id', (int) $userId);
        }

        $report = $query->first();

        if (! $report instanceof AnalysisReport) {
            return response()->json([
                'message' => 'Belum ada hasil analisis yang bisa diambil Service C.',
            ], 404);
        }

        $resolvedUserId = (int) $report->user_id;
        $totalIncome = (float) $report->total_income;
        $totalExpense = (float) $report->total_expense;
        $netBalance = round($totalIncome - $totalExpense, 2);
        $savingsRate = $totalIncome > 0
            ? round(($netBalance / $totalIncome) * 100, 2)
            : 0.0;
        $financialHealth = $netBalance < 0
            ? 'Defisit'
            : ($savingsRate >= 20 ? 'Sehat' : 'Perlu perhatian');

        return response()->json([
            'message' => 'Payload terbaru untuk Service C berhasil diambil.',
            'data' => [
                'run_id' => (int) $report->id,
                'executed_at' => $report->created_at?->toIso8601String(),
                'source_sync' => [
                    'user_id' => $resolvedUserId,
                    'fetched_transactions' => (int) $report->transaction_count,
                    'next_since' => $this->fintrackFeedSyncStateService->getSince($resolvedUserId),
                ],
                'metrics' => [
                    'total_income' => $totalIncome,
                    'total_expense' => $totalExpense,
                    'transaction_count' => (int) $report->transaction_count,
                    'top_category' => $report->top_category,
                    'net_balance' => $netBalance,
                    'savings_rate' => $savingsRate,
                    'financial_health' => $financialHealth,
                    'summary' => $netBalance < 0
                        ? 'Pengeluaran lebih besar dari pemasukan. Perlu pengendalian biaya prioritas.'
                        : ($savingsRate >= 20
                            ? 'Arus kas positif dengan rasio tabungan yang sehat.'
                            : 'Arus kas positif, namun rasio tabungan masih perlu ditingkatkan.'),
                ],
                'category_breakdown' => $report->categoryBreakdowns
                    ->map(fn ($item): array => [
                        'category' => (string) $item->category,
                        'amount' => (float) $item->amount,
                        'percentage' => (float) $item->percentage,
                    ])
                    ->values()
                    ->all(),
                'ai_insight' => [
                    'provider' => $report->aiInsight?->provider,
                    'model' => $report->aiInsight?->model,
                    'text' => $report->aiInsight?->insight,
                ],
            ],
        ]);
    }

    public function sendToServiceC(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payload' => ['required', 'array'],
        ]);

        $payload = $validated['payload'];

        $userId = data_get($payload, 'source_sync.user_id');
        $totalIncome = data_get($payload, 'metrics.total_income');
        $totalExpense = data_get($payload, 'metrics.total_expense');
        $topCategory = trim((string) data_get($payload, 'metrics.top_category', ''));
        $savingsRate = data_get($payload, 'metrics.savings_rate');

        if (! is_numeric($userId) || (int) $userId <= 0) {
            return response()->json([
                'message' => 'Payload tidak valid: source_sync.user_id wajib angka > 0.',
            ], 422);
        }

        if (! is_numeric($totalIncome) || ! is_numeric($totalExpense)) {
            return response()->json([
                'message' => 'Payload tidak valid: metrics.total_income dan metrics.total_expense wajib angka.',
            ], 422);
        }

        if ($topCategory === '') {
            $topCategory = 'uncategorized';
        }

        $insight = trim((string) data_get($payload, 'ai_insight', ''));

        if ($insight === '') {
            $insight = trim((string) data_get($payload, 'metrics.summary', ''));
        }

        if ($insight === '') {
            $insight = trim((string) data_get($payload, 'message', 'Analisis dari FinLyzer.'));
        }

        $plannerPayload = [
            'user_id' => (int) $userId,
            'total_income' => (int) round((float) $totalIncome),
            'total_expense' => (int) round((float) $totalExpense),
            'top_category' => $topCategory,
            'insight' => $insight,
        ];

        if (is_numeric($savingsRate)) {
            $normalizedSavingsRate = (float) $savingsRate;

            if ($normalizedSavingsRate >= 1 && $normalizedSavingsRate <= 100) {
                $plannerPayload['saving_percentage'] = round($normalizedSavingsRate, 2);
            }
        }

        $baseUrl = trim((string) config('services.service_c_planner.base_url', ''));
        $path = trim((string) config('services.service_c_planner.path', '/api/internal/plan'));
        $apiKey = trim((string) config('services.service_c_planner.api_key', ''));
        $apiKeyHeader = trim((string) config('services.service_c_planner.api_key_header', 'x-api-key'));
        $timeout = (int) config('services.service_c_planner.timeout', 10);

        if ($baseUrl === '' || $apiKey === '') {
            return response()->json([
                'message' => 'Konfigurasi Service C belum lengkap. Periksa SERVICE_C_PLANNER_BASE_URL dan SERVICE_C_PLANNER_API_KEY.',
            ], 500);
        }

        $url = rtrim($baseUrl, '/').'/'.ltrim($path, '/');

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->withHeaders([
                    'Accept' => 'application/json',
                    $apiKeyHeader => $apiKey,
                ])
                ->post($url, $plannerPayload);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'Gagal menghubungi Service C.',
                'error' => $exception->getMessage(),
            ], 502);
        }

        $serviceCResponse = $response->json();

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Service C menolak payload.',
                'service_c_status' => $response->status(),
                'service_c_response' => is_array($serviceCResponse)
                    ? $serviceCResponse
                    : ['raw' => $response->body()],
                'request_payload' => $plannerPayload,
            ], 502);
        }

        return response()->json([
            'message' => 'Payload berhasil dikirim ke Service C.',
            'service_c_status' => $response->status(),
            'service_c_response' => is_array($serviceCResponse)
                ? $serviceCResponse
                : ['raw' => $response->body()],
            'request_payload' => $plannerPayload,
        ]);
    }
}
