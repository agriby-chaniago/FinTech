<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\User;
use App\Services\FinancialPlanningService;
use App\Services\Service3CallbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PlanController extends Controller
{
    public function __construct(
        private readonly FinancialPlanningService $financialPlanningService,
        private readonly Service3CallbackService $service3CallbackService
    ) {
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => $this->userIdRules(),
            'user_email' => ['nullable', 'string', 'email:rfc', 'max:255'],
            'keycloak_sub' => ['nullable', 'string', 'max:255'],
            'total_income' => ['required', 'integer', 'min:0'],
            'total_expense' => ['required', 'integer', 'min:0'],
            'top_category' => ['required', 'string', 'max:255'],
            'insight' => ['required', 'string'],
            'saving_percentage' => ['nullable', 'numeric', 'min:1', 'max:100'],
        ]);

        $validated['user_id'] = $this->resolveUserId($request, $validated['user_id'] ?? null);

        $result = $this->financialPlanningService->createPlan($validated);

        if ($this->isInternalPlannerRequest($request)) {
            $this->service3CallbackService->sendPlanResult(
                $this->buildService3CallbackPayload($request, $validated, $result)
            );
        }

        return response()->json([
            'saving_plan' => $result['saving_plan'],
            'investment_recommendation' => $result['investment_recommendation'],
            'risk_level' => $result['risk_level'],
        ], 201);
    }

    /**
     * @return array<int, string>
     */
    private function userIdRules(): array
    {
        $requiredRule = Auth::check() ? 'nullable' : 'required';

        return [$requiredRule, 'integer', 'min:1'];
    }

    private function resolveUserId(Request $request, mixed $requestedUserId): int
    {
        $authenticatedUserId = Auth::id();

        if (is_numeric($authenticatedUserId)) {
            $resolvedUserId = (int) $authenticatedUserId;

            if (is_numeric($requestedUserId) && (int) $requestedUserId !== $resolvedUserId) {
                abort(403, 'user_id tidak sesuai dengan akun login.');
            }

            return $resolvedUserId;
        }

        $resolvedHintUser = $this->resolveHintUser(
            trim((string) $request->input('keycloak_sub', '')),
            strtolower(trim((string) $request->input('user_email', '')))
        );

        if ($resolvedHintUser instanceof User) {
            return (int) $resolvedHintUser->id;
        }

        if (is_numeric($requestedUserId)) {
            $resolvedRequestedUser = User::query()->find((int) $requestedUserId);

            if ($resolvedRequestedUser instanceof User) {
                return (int) $resolvedRequestedUser->id;
            }
        }

        throw ValidationException::withMessages([
            'user_id' => ['user_id tidak ditemukan di Service C. Sertakan user_email atau keycloak_sub yang valid.'],
        ]);

    }

    private function resolveHintUser(string $keycloakSub, string $userEmail): ?User
    {
        if ($keycloakSub !== '') {
            $resolvedBySub = User::query()
                ->where('keycloak_sub', $keycloakSub)
                ->first();

            if ($resolvedBySub instanceof User) {
                return $resolvedBySub;
            }
        }

        if ($userEmail !== '') {
            $resolvedByEmail = User::query()
                ->whereRaw('LOWER(email) = ?', [$userEmail])
                ->first();

            if ($resolvedByEmail instanceof User) {
                return $resolvedByEmail;
            }
        }

        return null;
    }

    private function isInternalPlannerRequest(Request $request): bool
    {
        return $request->is('api/plan') || $request->is('api/internal/plan');
    }

    /**
     * @param array<string, mixed> $validated
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function buildService3CallbackPayload(Request $request, array $validated, array $result): array
    {
        $resolvedUser = User::query()->find((int) $validated['user_id']);
        $resolvedEmail = strtolower(trim((string) ($validated['user_email'] ?? ($resolvedUser?->email ?? ''))));
        $resolvedKeycloakSub = trim((string) ($validated['keycloak_sub'] ?? ($resolvedUser?->keycloak_sub ?? '')));
        $goalTargets = $this->resolveGoalTargets((int) $validated['user_id']);

        $correlationId = trim((string) $request->input('correlation_id', ''));

        if ($correlationId === '') {
            $correlationId = trim((string) $request->header('x-correlation-id', ''));
        }

        if ($correlationId === '') {
            $correlationId = 'plan-'.$result['financial_plan_id'];
        }

        $analysisId = trim((string) $request->input('analysis_id', ''));

        if ($analysisId === '') {
            $analysisId = null;
        }

        $summaryText = sprintf(
            'Saving plan %d dengan rekomendasi %s (risk: %s).',
            (int) $result['saving_plan'],
            (string) $result['investment_recommendation'],
            (string) $result['risk_level']
        );

        return [
            'user_id' => (int) $validated['user_id'],
            'user_email' => $resolvedEmail !== '' ? $resolvedEmail : null,
            'keycloak_sub' => $resolvedKeycloakSub !== '' ? $resolvedKeycloakSub : null,
            'correlation_id' => $correlationId,
            'analysis_id' => $analysisId,
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
                'source' => 'fingoals',
                'financial_plan_id' => (int) $result['financial_plan_id'],
                'request' => [
                    'user_id' => (int) $validated['user_id'],
                    'user_email' => $resolvedEmail !== '' ? $resolvedEmail : null,
                    'keycloak_sub' => $resolvedKeycloakSub !== '' ? $resolvedKeycloakSub : null,
                    'total_income' => (int) $validated['total_income'],
                    'total_expense' => (int) $validated['total_expense'],
                    'top_category' => (string) $validated['top_category'],
                    'insight' => (string) $validated['insight'],
                    'saving_percentage' => array_key_exists('saving_percentage', $validated)
                        ? (float) $validated['saving_percentage']
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
