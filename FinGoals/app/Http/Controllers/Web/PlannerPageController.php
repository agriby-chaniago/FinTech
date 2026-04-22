<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FinancialPlan;
use App\Models\User;
use App\Services\FinancialPlanningService;
use App\Services\ServiceBLatestAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlannerPageController extends Controller
{
    public function __construct(
        private readonly FinancialPlanningService $financialPlanningService,
        private readonly ServiceBLatestAnalysisService $serviceBLatestAnalysisService
    ) {
    }

    public function index(Request $request): View
    {
        $authenticatedUser = $this->authenticatedUser();
        $analysisSnapshot = null;
        $analysisMessage = null;
        $recentPlans = collect();

        if ($authenticatedUser instanceof User) {
            $recentPlans = FinancialPlan::query()
                ->where('user_id', (int) $authenticatedUser->id)
                ->latest()
                ->limit(8)
                ->get();

            $analysisResult = $this->serviceBLatestAnalysisService->fetchLatest([
                'access_token' => $this->resolveAccessToken($request),
            ]);

            if (($analysisResult['ok'] ?? false) === true && is_array($analysisResult['data'] ?? null)) {
                $analysisSnapshot = $this->normalizeAnalysisSnapshot($analysisResult['data']);
            } else {
                $analysisMessage = (string) ($analysisResult['message'] ?? 'Data analisis terbaru belum tersedia.');
            }
        } else {
            $analysisMessage = 'Sesi login tidak valid. Silakan login ulang melalui Keycloak.';
        }

        return view('planner.index', [
            'analysisSnapshot' => $analysisSnapshot,
            'analysisMessage' => $analysisMessage,
            'recentPlans' => $recentPlans,
            'planResult' => session('plan_result'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $authenticatedUser = $this->authenticatedUser();

        if (! $authenticatedUser instanceof User) {
            return redirect()->route('oidc.redirect');
        }

        $analysisResult = $this->serviceBLatestAnalysisService->fetchLatest([
            'access_token' => $this->resolveAccessToken($request),
        ]);

        if (($analysisResult['ok'] ?? false) !== true || ! is_array($analysisResult['data'] ?? null)) {
            return redirect()
                ->route('web.planner.index')
                ->withErrors([
                    'planner' => (string) ($analysisResult['message'] ?? 'Data analisis dari Service B belum tersedia.'),
                ]);
        }

        $plannerPayload = $this->serviceBLatestAnalysisService->toPlannerPayload(
            $analysisResult['data'],
            (int) $authenticatedUser->id
        );

        if (
            (int) data_get($plannerPayload, 'total_income', 0) <= 0
            && (int) data_get($plannerPayload, 'total_expense', 0) <= 0
        ) {
            return redirect()
                ->route('web.planner.index')
                ->withErrors([
                    'planner' => 'Data analisis dari Service B belum cukup untuk membuat rencana.',
                ]);
        }

        $result = $this->financialPlanningService->createPlan($plannerPayload);

        return redirect()
            ->route('web.planner.index')
            ->with('status', 'Rencana otomatis berhasil dibuat dari data terbaru Service B.')
            ->with('plan_result', $result);
    }

    private function resolveAccessToken(Request $request): string
    {
        return trim((string) data_get($request->session()->get('oidc_tokens', []), 'access_token', ''));
    }

    private function authenticatedUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    /**
     * @param array<string, mixed> $analysisData
     * @return array<string, mixed>
     */
    private function normalizeAnalysisSnapshot(array $analysisData): array
    {
        $summary = trim((string) data_get($analysisData, 'ai_insight.text', ''));

        if ($summary === '') {
            $summary = trim((string) data_get($analysisData, 'metrics.summary', ''));
        }

        if ($summary === '') {
            $summary = 'Belum ada ringkasan analisis otomatis.';
        }

        $summary = $this->formatSummaryText($summary);

        return [
            'executed_at' => (string) data_get($analysisData, 'executed_at', ''),
            'total_income' => (int) round((float) data_get($analysisData, 'metrics.total_income', 0)),
            'total_expense' => (int) round((float) data_get($analysisData, 'metrics.total_expense', 0)),
            'transaction_count' => (int) data_get($analysisData, 'metrics.transaction_count', 0),
            'top_category' => (string) data_get($analysisData, 'metrics.top_category', 'uncategorized'),
            'net_balance' => (int) round((float) data_get($analysisData, 'metrics.net_balance', 0)),
            'savings_rate' => (float) data_get($analysisData, 'metrics.savings_rate', 0),
            'financial_health' => (string) data_get($analysisData, 'metrics.financial_health', 'Perlu perhatian'),
            'summary' => $summary,
        ];
    }

    private function formatSummaryText(string $summary): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $summary);
        $normalized = preg_replace('/\*\*(.*?)\*\*/u', '$1', $normalized) ?? $normalized;
        $normalized = preg_replace('/`{1,3}([^`]+)`{1,3}/u', '$1', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s*(Saran:|Actionable:)/u', "\n\n$1", $normalized) ?? $normalized;
        $normalized = preg_replace('/(^|\n)\s*[-*]\s+/u', '$1• ', $normalized) ?? $normalized;
        $normalized = preg_replace('/[ \t]{2,}/u', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\n{3,}/u', "\n\n", $normalized) ?? $normalized;

        return trim($normalized);
    }
}
