<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FinancialPlanningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    public function __construct(
        private readonly FinancialPlanningService $financialPlanningService
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
}
