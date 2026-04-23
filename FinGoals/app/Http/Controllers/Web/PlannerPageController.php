<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FinancialPlan;
use App\Models\Goal;
use App\Models\User;
use App\Services\FinancialPlanningService;
use App\Services\ServiceBLatestAnalysisService;
use App\Services\Service3CallbackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlannerPageController extends Controller
{
    public function __construct(
        private readonly FinancialPlanningService $financialPlanningService,
        private readonly ServiceBLatestAnalysisService $serviceBLatestAnalysisService,
        private readonly Service3CallbackService $service3CallbackService
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

            $analysisResult = $this->serviceBLatestAnalysisService->fetchLatest(
                $this->buildAnalyzerContext($request, $authenticatedUser)
            );

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

        $analysisResult = $this->serviceBLatestAnalysisService->fetchLatest(
            $this->buildAnalyzerContext($request, $authenticatedUser)
        );

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

        $this->service3CallbackService->sendPlanResult(
            $this->buildService3CallbackPayload($plannerPayload, $result, $authenticatedUser)
        );

        return redirect()
            ->route('web.planner.index')
            ->with('status', 'Rencana otomatis berhasil dibuat dari data terbaru Service B.')
            ->with('plan_result', $result);
    }

    private function resolveAccessToken(Request $request): string
    {
        return trim((string) data_get($request->session()->get('oidc_tokens', []), 'access_token', ''));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAnalyzerContext(Request $request, User $authenticatedUser): array
    {
        return [
            'access_token' => $this->resolveAccessToken($request),
            'user_id' => (int) $authenticatedUser->id,
            'user_email' => strtolower(trim((string) $authenticatedUser->email)),
            'keycloak_sub' => trim((string) $authenticatedUser->keycloak_sub),
        ];
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

    /**
     * @param array<string, mixed> $plannerPayload
     * @param array<string, mixed> $result
     * @param User $authenticatedUser
     * @return array<string, mixed>
     */
    private function buildService3CallbackPayload(array $plannerPayload, array $result, User $authenticatedUser): array
    {
        $resolvedEmail = strtolower(trim((string) $authenticatedUser->email));
        $resolvedKeycloakSub = trim((string) $authenticatedUser->keycloak_sub);
        $goalTargets = $this->resolveGoalTargets((int) $authenticatedUser->id);

        $summaryText = sprintf(
            'Saving plan %d dengan rekomendasi %s (risk: %s).',
            (int) $result['saving_plan'],
            (string) $result['investment_recommendation'],
            (string) $result['risk_level']
        );

        return [
            'user_id' => (int) $plannerPayload['user_id'],
            'user_email' => $resolvedEmail !== '' ? $resolvedEmail : null,
            'keycloak_sub' => $resolvedKeycloakSub !== '' ? $resolvedKeycloakSub : null,
            'correlation_id' => 'web-plan-'.(int) $result['financial_plan_id'],
            'analysis_id' => null,
            'status' => 'success',
            'summary_text' => $summaryText,
            'recommendations' => [
                [
                    'type' => 'investment_recommendation',
                    'value' => (string) $result['investment_recommendation'],
                    'risk_level' => (string) $result['risk_level'],
                    'saving_plan' => (int) $result['saving_plan'],
                ],
            ],
            'goals' => $goalTargets,
            'raw_payload' => [
                'source' => 'fingoals-web',
                'financial_plan_id' => (int) $result['financial_plan_id'],
                'request' => [
                    'user_id' => (int) $plannerPayload['user_id'],
                    'user_email' => $resolvedEmail !== '' ? $resolvedEmail : null,
                    'keycloak_sub' => $resolvedKeycloakSub !== '' ? $resolvedKeycloakSub : null,
                    'total_income' => (int) $plannerPayload['total_income'],
                    'total_expense' => (int) $plannerPayload['total_expense'],
                    'top_category' => (string) $plannerPayload['top_category'],
                    'insight' => (string) $plannerPayload['insight'],
                    'saving_percentage' => isset($plannerPayload['saving_percentage'])
                        ? (float) $plannerPayload['saving_percentage']
                        : null,
                ],
                'goals' => $goalTargets,
                'result' => [
                    'saving_plan' => (int) $result['saving_plan'],
                    'saving_amount' => (int) $result['saving_amount'],
                    'saving_percentage' => (float) $result['saving_percentage'],
                    'investment_recommendation' => (string) $result['investment_recommendation'],
                    'risk_level' => (string) $result['risk_level'],
                ],
            ],
            'plan_period_start' => null,
            'plan_period_end' => null,
        ];
    }

    /**
     * @return array<int, array{name: string, target: int, timeline_months: int}>
     */
    private function resolveGoalTargets(int $userId): array
    {
        return Goal::query()
            ->where('user_id', $userId)
            ->orderBy('deadline')
            ->limit(5)
            ->get()
            ->map(function (Goal $goal): array {
                $deadline = $goal->deadline;
                $timelineMonths = 0;

                if ($deadline !== null) {
                    $timelineMonths = max(0, (int) ceil(now()->startOfDay()->diffInMonths($deadline->startOfDay(), false)));
                }

                return [
                    'name' => (string) $goal->goal_name,
                    'target' => (int) $goal->target_amount,
                    'timeline_months' => $timelineMonths,
                ];
            })
            ->values()
            ->all();
    }
}
