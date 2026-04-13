<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyzeRequest;
use App\Http\Requests\AnalyzeAutoRequest;
use App\Models\AnalysisReport;
use App\Services\FintrackAutoAnalyzeService;
use App\Services\FintrackFeedSyncStateService;
use App\Services\FinancialAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $authenticatedUserId = Auth::id();

            $result = $this->fintrackAutoAnalyzeService->run(
                is_numeric($authenticatedUserId) ? (int) $authenticatedUserId : null
            );
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
}
