<?php

namespace App\Services;

use App\Models\AiPlan;
use App\Models\FinancialPlan;
use Illuminate\Support\Facades\DB;

class FinancialPlanningService
{
    public function __construct(
        private readonly GroqPlannerService $groqPlannerService
    ) {
    }

    public function createPlan(array $input): array
    {
        $totalIncome = (int) $input['total_income'];
        $totalExpense = (int) $input['total_expense'];
        $savingAmount = $totalIncome - $totalExpense;
        $savingPercentage = (float) ($input['saving_percentage'] ?? 20);
        $targetSavingPlan = (int) round(($totalIncome * $savingPercentage) / 100);
        $savingPlan = max(0, min(max(0, $savingAmount), $targetSavingPlan));

        $aiResult = $this->groqPlannerService->generatePlan([
            'user_id' => (int) $input['user_id'],
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'saving_amount' => $savingAmount,
            'saving_percentage' => $savingPercentage,
            'saving_plan' => $savingPlan,
            'top_category' => (string) $input['top_category'],
            'insight' => (string) $input['insight'],
        ]);

        $financialPlan = DB::transaction(function () use ($input, $totalIncome, $totalExpense, $savingAmount, $savingPercentage, $savingPlan, $aiResult): FinancialPlan {
            $financialPlan = FinancialPlan::create([
                'user_id' => (int) $input['user_id'],
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'saving_amount' => $savingAmount,
                'saving_percentage' => $savingPercentage,
                'saving_plan' => $savingPlan,
                'top_category' => (string) $input['top_category'],
                'insight' => (string) $input['insight'],
                'investment_recommendation' => (string) $aiResult['investment_recommendation'],
                'risk_level' => (string) $aiResult['risk_level'],
            ]);

            AiPlan::create([
                'user_id' => (int) $input['user_id'],
                'financial_plan_id' => $financialPlan->id,
                'prompt' => (string) $aiResult['prompt'],
                'raw_response' => $aiResult['raw_response'],
                'response_payload' => $aiResult['response_payload'],
                'provider' => 'groq',
                'model' => (string) $aiResult['model'],
            ]);

            return $financialPlan;
        });

        return [
            'financial_plan_id' => $financialPlan->id,
            'saving_plan' => $savingPlan,
            'saving_amount' => $savingAmount,
            'saving_percentage' => $savingPercentage,
            'investment_recommendation' => (string) $aiResult['investment_recommendation'],
            'risk_level' => (string) $aiResult['risk_level'],
        ];
    }
}
