<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class FinanceSummaryController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $analyzerUrl = (string) config('services.analyzer.url');
        $plannerUrl = (string) config('services.planner.url');
        $analyzerApiKey = (string) config('services.analyzer.api_key');
        $plannerApiKey = (string) config('services.planner.api_key');

        if ($analyzerUrl === '' || $plannerUrl === '') {
            return response()->json([
                'message' => 'Analyzer or Planner URL is not configured.',
            ], 500);
        }

        if ($analyzerApiKey === '' || $plannerApiKey === '') {
            return response()->json([
                'message' => 'Analyzer or Planner API key is not configured.',
            ], 500);
        }

        $user = $request->user();

        $transactions = $user->transactions()
            ->orderByDesc('transaction_date')
            ->get([
                'id',
                'user_id',
                'amount',
                'description',
                'category',
                'type',
                'transaction_date',
                'created_at',
                'updated_at',
            ]);

        try {
            $analyzerResponse = Http::acceptJson()
                ->timeout(15)
                ->withHeaders([
                    'x-api-key' => $analyzerApiKey,
                ])
                ->post($analyzerUrl, [
                    'user_id' => $user->id,
                    'transactions' => $transactions,
                ]);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'Analyzer service is unreachable.',
                'error' => $exception->getMessage(),
            ], 502);
        }

        if (! $analyzerResponse->successful()) {
            return response()->json([
                'message' => 'Analyzer service call failed.',
                'status' => $analyzerResponse->status(),
                'error' => $analyzerResponse->body(),
            ], 502);
        }

        $analysis = $analyzerResponse->json();

        if (! is_array($analysis)) {
            return response()->json([
                'message' => 'Analyzer service returned invalid payload.',
            ], 502);
        }

        $plannerPayload = $this->buildPlannerPayload($request, (int) $user->id, $analysis);

        try {
            $plannerResponse = Http::acceptJson()
                ->timeout(15)
                ->withHeaders([
                    'x-api-key' => $plannerApiKey,
                ])
                ->post($plannerUrl, $plannerPayload);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'Planner service is unreachable.',
                'error' => $exception->getMessage(),
            ], 502);
        }

        if (! $plannerResponse->successful()) {
            return response()->json([
                'message' => 'Planner service call failed.',
                'status' => $plannerResponse->status(),
                'error' => $plannerResponse->body(),
            ], 502);
        }

        return response()->json([
            'message' => 'Finance summary generated successfully.',
            'data' => [
                'transactions' => $transactions,
                'analysis' => $analysis,
                'plan' => $plannerResponse->json(),
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $analysis
     * @return array<string, int|float|string>
     */
    private function buildPlannerPayload(Request $request, int $userId, array $analysis): array
    {
        $topCategory = trim((string) data_get($analysis, 'top_category', 'others'));
        $insight = trim((string) data_get($analysis, 'insight', data_get($analysis, 'summary', '')));

        $payload = [
            'user_id' => $userId,
            'total_income' => (int) round((float) data_get($analysis, 'total_income', 0)),
            'total_expense' => (int) round((float) data_get($analysis, 'total_expense', 0)),
            'top_category' => $topCategory !== '' ? $topCategory : 'others',
            'insight' => $insight !== '' ? $insight : 'Insight tidak tersedia dari analyzer.',
        ];

        if ($request->filled('saving_percentage') && is_numeric($request->input('saving_percentage'))) {
            $payload['saving_percentage'] = (float) $request->input('saving_percentage');
        }

        return $payload;
    }
}
