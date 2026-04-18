<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FinancialPlanningService;
use App\Services\Service3CallbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return [$requiredRule, 'integer', 'exists:users,id'];
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

        if (! is_numeric($requestedUserId)) {
            $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
            ]);
        }

        return (int) $requestedUserId;
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
            'goals' => [],
            'raw_payload' => [
                'source' => 'fingoals',
                'financial_plan_id' => (int) $result['financial_plan_id'],
                'request' => [
                    'user_id' => (int) $validated['user_id'],
                    'total_income' => (int) $validated['total_income'],
                    'total_expense' => (int) $validated['total_expense'],
                    'top_category' => (string) $validated['top_category'],
                    'insight' => (string) $validated['insight'],
                    'saving_percentage' => array_key_exists('saving_percentage', $validated)
                        ? (float) $validated['saving_percentage']
                        : null,
                ],
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
}
