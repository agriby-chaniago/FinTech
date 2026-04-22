<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyzeRequest;
use App\Http\Requests\AnalyzeAutoRequest;
use App\Models\AnalysisReport;
use App\Models\User;
use App\Services\FintrackAutoAnalyzeService;
use App\Services\FintrackFeedSyncStateService;
use App\Services\FinancialAnalysisService;
use Carbon\CarbonImmutable;
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
            $resolvedUserId = $request->resolvedUserId();
            $resolvedEmail = $request->resolvedEmail();
            $resolvedName = null;
            $resolvedKeycloakSub = null;

            $authenticatedUser = Auth::user();

            if ($authenticatedUser instanceof User) {
                $resolvedName = trim((string) $authenticatedUser->name);

                $keycloakSub = trim((string) $authenticatedUser->keycloak_sub);

                if ($keycloakSub !== '') {
                    $resolvedKeycloakSub = $keycloakSub;
                }
            } elseif (is_string($resolvedEmail) && $resolvedEmail !== '') {
                $resolvedUser = User::query()
                    ->whereRaw('LOWER(email) = ?', [$resolvedEmail])
                    ->first();

                if ($resolvedUser instanceof User) {
                    $resolvedName = trim((string) $resolvedUser->name);

                    $keycloakSub = trim((string) $resolvedUser->keycloak_sub);

                    if ($keycloakSub !== '') {
                        $resolvedKeycloakSub = $keycloakSub;
                    }
                }
            }

            if (is_numeric($resolvedUserId) && ($resolvedEmail === null || $resolvedName === null || $resolvedKeycloakSub === null)) {
                $resolvedUser = User::query()->find((int) $resolvedUserId);

                if ($resolvedUser instanceof User) {
                    if ($resolvedEmail === null) {
                        $candidateEmail = strtolower(trim((string) $resolvedUser->email));

                        if ($candidateEmail !== '') {
                            $resolvedEmail = $candidateEmail;
                        }
                    }

                    if ($resolvedName === null) {
                        $candidateName = trim((string) $resolvedUser->name);

                        if ($candidateName !== '') {
                            $resolvedName = $candidateName;
                        }
                    }

                    if ($resolvedKeycloakSub === null) {
                        $candidateKeycloakSub = trim((string) $resolvedUser->keycloak_sub);

                        if ($candidateKeycloakSub !== '') {
                            $resolvedKeycloakSub = $candidateKeycloakSub;
                        }
                    }
                }
            }

            $result = $this->fintrackAutoAnalyzeService->run(
                (int) $resolvedUserId,
                $request->since(),
                $request->includeSummary(),
                $request->useSavedSince(),
                null,
                null,
                $resolvedKeycloakSub,
                $resolvedEmail
            );

            if (is_array($result['source'] ?? null) && is_string($resolvedEmail) && $resolvedEmail !== '') {
                $result['source']['user_email'] = $resolvedEmail;
            }

            if (is_array($result['source'] ?? null) && is_string($resolvedName) && $resolvedName !== '') {
                $result['source']['user_name'] = $resolvedName;
            }

            if (is_array($result['source'] ?? null) && is_string($resolvedKeycloakSub) && $resolvedKeycloakSub !== '') {
                $result['source']['user_keycloak_sub'] = $resolvedKeycloakSub;
            }
        } catch (Throwable $exception) {
            $responsePayload = [
                'message' => $this->resolveAnalyzeGatewayMessage($exception),
            ];

            if ((bool) config('app.debug')) {
                $responsePayload['detail'] = $exception->getMessage();
            }

            return response()->json([
                ...$responsePayload,
            ], 502);
        }

        return response()->json($result);
    }

    public function analyzeAutoRun(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ], [
            'date_from.date_format' => 'Format tanggal mulai harus YYYY-MM-DD.',
            'date_to.date_format' => 'Format tanggal selesai harus YYYY-MM-DD.',
            'date_to.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;

        $resolvedSince = null;
        $useSavedSince = true;

        if (is_string($dateFrom) || is_string($dateTo)) {
            $useSavedSince = false;
        }

        if (is_string($dateFrom) && $dateFrom !== '') {
            $resolvedSince = CarbonImmutable::createFromFormat('Y-m-d', $dateFrom, 'UTC')
                ->startOfDay()
                ->toIso8601String();
        }

        if (is_string($dateFrom) && $dateFrom !== '' && is_string($dateTo) && $dateTo !== '') {
            $startDate = CarbonImmutable::createFromFormat('Y-m-d', $dateFrom, 'UTC')->startOfDay();
            $endDate = CarbonImmutable::createFromFormat('Y-m-d', $dateTo, 'UTC')->endOfDay();
            $rangeDays = $startDate->diffInDays($endDate) + 1;

            if ($rangeDays > 366) {
                return response()->json([
                    'message' => 'Rentang tanggal maksimal 366 hari agar analisis tetap cepat dan stabil.',
                    'errors' => [
                        'date_to' => ['Rentang tanggal terlalu panjang. Silakan pilih maksimal 366 hari.'],
                    ],
                ], 422);
            }
        }

        try {
            $authenticatedUser = Auth::user();
            $authenticatedUserId = $authenticatedUser instanceof User ? (int) $authenticatedUser->id : Auth::id();

            $resolvedEmail = null;
            $resolvedName = null;
            $resolvedKeycloakSub = null;

            if ($authenticatedUser instanceof User) {
                $resolvedEmail = strtolower(trim((string) $authenticatedUser->email));
                $resolvedName = trim((string) $authenticatedUser->name);

                $keycloakSub = trim((string) $authenticatedUser->keycloak_sub);

                if ($keycloakSub !== '') {
                    $resolvedKeycloakSub = $keycloakSub;
                }
            }

            $result = $this->fintrackAutoAnalyzeService->run(
                is_numeric($authenticatedUserId) ? (int) $authenticatedUserId : null,
                $resolvedSince,
                true,
                $useSavedSince,
                is_string($dateFrom) ? $dateFrom : null,
                is_string($dateTo) ? $dateTo : null,
                $resolvedKeycloakSub,
                $resolvedEmail
            );

            if (! ($authenticatedUser instanceof User)) {
                $resolvedUserId = data_get($result, 'source.user_id');

                if (is_numeric($resolvedUserId)) {
                    $resolvedUser = User::query()->find((int) $resolvedUserId);

                    if ($resolvedUser instanceof User) {
                        $resolvedEmail = strtolower(trim((string) $resolvedUser->email));
                        $resolvedName = trim((string) $resolvedUser->name);

                        $keycloakSub = trim((string) $resolvedUser->keycloak_sub);

                        if ($keycloakSub !== '') {
                            $resolvedKeycloakSub = $keycloakSub;
                        }
                    }
                }
            }

            if (is_array($result['source'] ?? null) && is_string($resolvedEmail) && $resolvedEmail !== '') {
                $result['source']['user_email'] = $resolvedEmail;
            }

            if (is_array($result['source'] ?? null) && is_string($resolvedName) && $resolvedName !== '') {
                $result['source']['user_name'] = $resolvedName;
            }

            if (is_array($result['source'] ?? null) && is_string($resolvedKeycloakSub) && $resolvedKeycloakSub !== '') {
                $result['source']['user_keycloak_sub'] = $resolvedKeycloakSub;
            }

            if (is_array($result['source'] ?? null)) {
                if (is_string($dateFrom) && $dateFrom !== '') {
                    $result['source']['date_from'] = $dateFrom;
                }

                if (is_string($dateTo) && $dateTo !== '') {
                    $result['source']['date_to'] = $dateTo;
                }
            }
        } catch (Throwable $exception) {
            $responsePayload = [
                'message' => $this->resolveAnalyzeGatewayMessage($exception),
            ];

            if ((bool) config('app.debug')) {
                $responsePayload['detail'] = $exception->getMessage();
            }

            return response()->json([
                ...$responsePayload,
            ], 502);
        }

        return response()->json($result);
    }

    private function resolveAnalyzeGatewayMessage(Throwable $exception): string
    {
        $normalizedMessage = strtolower(trim($exception->getMessage()));

        if (
            str_contains($normalizedMessage, 'failed to connect')
            || str_contains($normalizedMessage, 'curl error 7')
        ) {
            return 'Service FinTrack belum aktif. Jalankan service FinTrack terlebih dahulu, lalu coba lagi.';
        }

        if (str_contains($normalizedMessage, 'fintrack_feed_base_url mengarah ke service ini sendiri')) {
            return 'Konfigurasi FINTRACK_FEED_BASE_URL masih menunjuk ke FinLyzer. Arahkan ke host/port FinTrack yang benar.';
        }

        if (
            str_contains($normalizedMessage, 'invalid service api key')
            || str_contains($normalizedMessage, 'status:401')
        ) {
            return 'Koneksi antar-service ditolak oleh FinTrack (API key tidak valid). Samakan FINTRACK_FEED_API_KEY di FinLyzer dengan SERVICE2_PULL_API_KEY atau INTER_SERVICE_API_KEY di FinTrack.';
        }

        if (
            str_contains($normalizedMessage, 'status:404')
            && (
                str_contains($normalizedMessage, 'no query results for model [app\\models\\user]')
                || str_contains($normalizedMessage, 'user tidak ditemukan di fintrack')
            )
        ) {
            return 'Akun belum tersedia di FinTrack. Login ke FinTrack sekali agar akun tersinkron, lalu coba lagi.';
        }

        if (
            str_contains($normalizedMessage, 'status:400')
            && (
                str_contains($normalizedMessage, 'invalid request parameters')
                || str_contains($normalizedMessage, 'since parameter')
            )
        ) {
            return 'Parameter sinkronisasi ke FinTrack tidak valid. Periksa format since/date yang dikirim.';
        }

        if (
            str_contains($normalizedMessage, 'konfigurasi fintrack feed belum lengkap')
            || str_contains($normalizedMessage, 'fintrack_feed_base_url')
            || str_contains($normalizedMessage, 'fintrack_feed_api_key')
        ) {
            return 'Konfigurasi koneksi FinTrack belum lengkap. Periksa FINTRACK_FEED_BASE_URL dan FINTRACK_FEED_API_KEY.';
        }

        if (str_contains($normalizedMessage, 'gagal mengambil transaksi dari fintrack feed')) {
            return 'Gagal mengambil transaksi dari FinTrack feed.';
        }

        return 'Terjadi kendala saat mengambil data dari FinTrack. Silakan coba lagi beberapa saat lagi.';
    }

    public function latestForServiceC(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'min:1'],
            'user_email' => ['nullable', 'string', 'email:rfc', 'max:255'],
            'keycloak_sub' => ['nullable', 'string', 'max:255'],
        ]);

        $query = AnalysisReport::query()
            ->with([
                'categoryBreakdowns:id,analysis_report_id,category,amount,percentage',
                'aiInsight:id,analysis_report_id,provider,model,insight',
            ])
            ->orderByDesc('id');

        $userId = $validated['user_id'] ?? null;
        $userEmail = strtolower(trim((string) ($validated['user_email'] ?? '')));
        $keycloakSub = trim((string) ($validated['keycloak_sub'] ?? ''));

        $authenticatedUserId = Auth::id();

        if (is_numeric($authenticatedUserId)) {
            if (is_numeric($userId) && (int) $userId !== (int) $authenticatedUserId) {
                return response()->json([
                    'message' => 'user_id tidak sesuai dengan akun login.',
                ], 403);
            }

            $query->where('user_id', (int) $authenticatedUserId);
        } else {
            $resolvedUserId = null;

            if ($keycloakSub !== '') {
                $resolvedUserBySub = User::query()
                    ->where('keycloak_sub', $keycloakSub)
                    ->first();

                if ($resolvedUserBySub instanceof User) {
                    $resolvedUserId = (int) $resolvedUserBySub->id;
                }
            }

            if (! is_numeric($resolvedUserId) && $userEmail !== '') {
                $resolvedUserByEmail = User::query()
                    ->whereRaw('LOWER(email) = ?', [$userEmail])
                    ->first();

                if ($resolvedUserByEmail instanceof User) {
                    $resolvedUserId = (int) $resolvedUserByEmail->id;
                }
            }

            if (! is_numeric($resolvedUserId) && is_numeric($userId)) {
                $resolvedUserId = (int) $userId;
            }

            if (! is_numeric($resolvedUserId)) {
                return response()->json([
                    'message' => 'Parameter user tidak lengkap. Sertakan user_id, user_email, atau keycloak_sub.',
                ], 422);
            }

            $query->where('user_id', (int) $resolvedUserId);
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
        $userEmail = strtolower(trim((string) data_get($payload, 'source_sync.user_email', data_get($payload, 'source.user_email', ''))));
        $keycloakSub = trim((string) data_get($payload, 'source_sync.keycloak_sub', data_get($payload, 'source.user_keycloak_sub', '')));

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

        if ($userEmail !== '' && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            $plannerPayload['user_email'] = $userEmail;
        }

        if ($keycloakSub !== '') {
            $plannerPayload['keycloak_sub'] = $keycloakSub;
        }

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
