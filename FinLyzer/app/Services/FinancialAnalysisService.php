<?php

namespace App\Services;

use App\Models\AnalysisReport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinancialAnalysisService
{
    private const TYPE_INCOME = 'income';
    private const TYPE_EXPENSE = 'expense';
    private const INSIGHT_PROVIDER = 'groq';

    public function __construct(private readonly GroqInsightService $groqInsightService)
    {
    }

    /**
     * @param array<int, array{amount: mixed, category: mixed, type: mixed}> $transactions
<<<<<<< HEAD
        * @return array{total_income: float, total_expense: float, transaction_count: int, top_category: ?string, category_breakdown: array<string, float>, net_balance: float, savings_rate: float, financial_health: string, summary: string, insight: string}
=======
     * @return array{total_income: float, total_expense: float, transaction_count: int, top_category: ?string, category_breakdown: array<string, float>, insight: string}
>>>>>>> 95acdce (Fix nested repo issue)
     */
    public function analyze(int $userId, array $transactions): array
    {
        $normalizedTransactions = $this->normalizeTransactions($transactions);
        $metrics = $this->calculateMetrics($normalizedTransactions);

        $analysisData = [
            'user_id' => $userId,
            'total_income' => $metrics['total_income'],
            'total_expense' => $metrics['total_expense'],
            'transaction_count' => $metrics['transaction_count'],
            'top_category' => $metrics['top_category'],
            'category_breakdown' => $metrics['category_breakdown'],
<<<<<<< HEAD
            'net_balance' => $metrics['net_balance'],
            'savings_rate' => $metrics['savings_rate'],
            'financial_health' => $metrics['financial_health'],
            'summary' => $metrics['summary'],
=======
>>>>>>> 95acdce (Fix nested repo issue)
        ];

        return DB::transaction(function () use ($analysisData, $metrics, $normalizedTransactions, $userId): array {
            $analysisReport = $this->persistAnalysisReport($userId, $normalizedTransactions, $metrics);

            $this->persistCategoryBreakdowns(
                $analysisReport,
                $metrics['expense_by_category'],
                $metrics['category_breakdown']
            );

            $insightResult = $this->groqInsightService->generateInsight($analysisData);

            $analysisReport->aiInsight()->create([
                'provider' => self::INSIGHT_PROVIDER,
                'model' => $insightResult['model'],
                'prompt' => $insightResult['prompt'],
                'insight' => $insightResult['insight'],
            ]);

            return $this->buildResponse($metrics, $insightResult['insight']);
        });
    }

    /**
     * @param array<int, array{amount: mixed, category: mixed, type: mixed}> $transactions
     * @return Collection<int, array{amount: float, category: string, type: string}>
     */
    private function normalizeTransactions(array $transactions): Collection
    {
        return collect($transactions)
            ->map(function (array $transaction): array {
                return [
                    'amount' => (float) $transaction['amount'],
                    'category' => strtolower(trim((string) $transaction['category'])),
                    'type' => strtolower(trim((string) $transaction['type'])),
                ];
            })
            ->values();
    }

    /**
     * @param Collection<int, array{amount: float, category: string, type: string}> $transactions
<<<<<<< HEAD
        * @return array{total_income: float, total_expense: float, transaction_count: int, top_category: ?string, category_breakdown: array<string, float>, net_balance: float, savings_rate: float, financial_health: string, summary: string, expense_by_category: array<string, float>}
=======
     * @return array{total_income: float, total_expense: float, transaction_count: int, top_category: ?string, category_breakdown: array<string, float>, expense_by_category: array<string, float>}
>>>>>>> 95acdce (Fix nested repo issue)
     */
    private function calculateMetrics(Collection $transactions): array
    {
        $totalIncome = round((float) $transactions
            ->where('type', self::TYPE_INCOME)
            ->sum('amount'), 2);

        $totalExpense = round((float) $transactions
            ->where('type', self::TYPE_EXPENSE)
            ->sum('amount'), 2);

        $transactionCount = $transactions->count();

        /** @var array<string, float> $expenseByCategory */
        $expenseByCategory = $this->expenseByCategory($transactions)
            ->mapWithKeys(fn (float $amount, string $category): array => [(string) $category => round($amount, 2)])
            ->all();

        $topCategory = array_key_first($expenseByCategory);

        /** @var array<string, float> $categoryBreakdown */
        $categoryBreakdown = collect($expenseByCategory)
            ->map(fn (float $amount): float => $totalExpense > 0
                ? round(($amount / $totalExpense) * 100, 2)
                : 0.0)
            ->all();

<<<<<<< HEAD
        $netBalance = round($totalIncome - $totalExpense, 2);
        $savingsRate = $totalIncome > 0
            ? round(($netBalance / $totalIncome) * 100, 2)
            : 0.0;

        $financialHealth = $this->resolveFinancialHealth($netBalance, $savingsRate);
        $summary = $this->buildSummary($totalIncome, $totalExpense, $netBalance, $savingsRate, $financialHealth);

=======
>>>>>>> 95acdce (Fix nested repo issue)
        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'transaction_count' => $transactionCount,
            'top_category' => $topCategory,
            'category_breakdown' => $categoryBreakdown,
<<<<<<< HEAD
            'net_balance' => $netBalance,
            'savings_rate' => $savingsRate,
            'financial_health' => $financialHealth,
            'summary' => $summary,
=======
>>>>>>> 95acdce (Fix nested repo issue)
            'expense_by_category' => $expenseByCategory,
        ];
    }

    /**
     * @param Collection<int, array{amount: float, category: string, type: string}> $normalizedTransactions
     * @param array{total_income: float, total_expense: float, transaction_count: int, top_category: ?string, category_breakdown: array<string, float>, expense_by_category: array<string, float>} $metrics
     */
    private function persistAnalysisReport(int $userId, Collection $normalizedTransactions, array $metrics): AnalysisReport
    {
        return AnalysisReport::create([
            'user_id' => $userId,
            'total_income' => $metrics['total_income'],
            'total_expense' => $metrics['total_expense'],
            'transaction_count' => $metrics['transaction_count'],
            'top_category' => $metrics['top_category'],
            'raw_transactions' => $normalizedTransactions->all(),
        ]);
    }

    /**
     * @param array<string, float> $expenseByCategory
     * @param array<string, float> $categoryBreakdown
     */
    private function persistCategoryBreakdowns(
        AnalysisReport $analysisReport,
        array $expenseByCategory,
        array $categoryBreakdown
    ): void {
        foreach ($expenseByCategory as $category => $amount) {
            $analysisReport->categoryBreakdowns()->create([
                'category' => $category,
                'amount' => $amount,
                'percentage' => (float) ($categoryBreakdown[$category] ?? 0),
            ]);
        }
    }

    /**
<<<<<<< HEAD
     * @param array{total_income: float, total_expense: float, transaction_count: int, top_category: ?string, category_breakdown: array<string, float>, net_balance: float, savings_rate: float, financial_health: string, summary: string, expense_by_category: array<string, float>} $metrics
     * @return array{total_income: float, total_expense: float, transaction_count: int, top_category: ?string, category_breakdown: array<string, float>, net_balance: float, savings_rate: float, financial_health: string, summary: string, insight: string}
=======
     * @param array{total_income: float, total_expense: float, transaction_count: int, top_category: ?string, category_breakdown: array<string, float>, expense_by_category: array<string, float>} $metrics
     * @return array{total_income: float, total_expense: float, transaction_count: int, top_category: ?string, category_breakdown: array<string, float>, insight: string}
>>>>>>> 95acdce (Fix nested repo issue)
     */
    private function buildResponse(array $metrics, string $insight): array
    {
        return [
            'total_income' => $metrics['total_income'],
            'total_expense' => $metrics['total_expense'],
            'transaction_count' => $metrics['transaction_count'],
            'top_category' => $metrics['top_category'],
            'category_breakdown' => $metrics['category_breakdown'],
<<<<<<< HEAD
            'net_balance' => $metrics['net_balance'],
            'savings_rate' => $metrics['savings_rate'],
            'financial_health' => $metrics['financial_health'],
            'summary' => $metrics['summary'],
=======
>>>>>>> 95acdce (Fix nested repo issue)
            'insight' => $insight,
        ];
    }

<<<<<<< HEAD
    private function resolveFinancialHealth(float $netBalance, float $savingsRate): string
    {
        if ($netBalance < 0) {
            return 'Defisit';
        }

        if ($savingsRate >= 20) {
            return 'Sehat';
        }

        return 'Perlu perhatian';
    }

    private function buildSummary(
        float $totalIncome,
        float $totalExpense,
        float $netBalance,
        float $savingsRate,
        string $financialHealth
    ): string {
        if ($totalIncome <= 0 && $totalExpense <= 0) {
            return 'Belum ada transaksi yang cukup untuk dianalisis.';
        }

        if ($netBalance < 0) {
            return 'Pengeluaran lebih besar dari pemasukan. Fokuskan penurunan biaya prioritas agar kembali surplus.';
        }

        if ($financialHealth === 'Sehat') {
            return sprintf(
                'Arus kas positif dengan rasio tabungan %.2f%%. Kondisi keuangan cukup sehat dan dapat dipertahankan.',
                $savingsRate
            );
        }

        return sprintf(
            'Arus kas masih positif, tetapi rasio tabungan baru %.2f%%. Coba tambah porsi tabungan bulanan agar lebih aman.',
            $savingsRate
        );
    }

=======
>>>>>>> 95acdce (Fix nested repo issue)
    /**
     * @param Collection<int, array{amount: float, category: string, type: string}> $transactions
     * @return Collection<string, float>
     */
    private function expenseByCategory(Collection $transactions): Collection
    {
        return $transactions
            ->where('type', self::TYPE_EXPENSE)
            ->groupBy('category')
            ->map(fn (Collection $group): float => (float) $group->sum('amount'))
            ->sortDesc();
    }
}
