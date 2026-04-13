<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FinancialPlanningService;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct(
        private readonly FinancialPlanningService $financialPlanningService
    ) {
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'total_income' => ['required', 'integer', 'min:0'],
            'total_expense' => ['required', 'integer', 'min:0'],
            'top_category' => ['required', 'string', 'max:255'],
            'insight' => ['required', 'string'],
            'saving_percentage' => ['nullable', 'numeric', 'min:1', 'max:100'],
        ]);

        $result = $this->financialPlanningService->createPlan($validated);

        return response()->json([
            'saving_plan' => $result['saving_plan'],
            'investment_recommendation' => $result['investment_recommendation'],
            'risk_level' => $result['risk_level'],
        ], 201);
    }
}
