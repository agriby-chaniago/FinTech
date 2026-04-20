<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class ServiceBLatestAnalysisService
{
    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function fetchLatest(array $context = []): array
    {
        $baseUrl = trim((string) config('services.service_b_analyzer.base_url', ''));
        $latestPath = trim((string) config('services.service_b_analyzer.latest_path', '/api/user/analyze/auto/latest'));
        $timeout = (int) config('services.service_b_analyzer.timeout', 10);
        $accessToken = trim((string) data_get($context, 'access_token', ''));

        if ($baseUrl === '') {
            return [
                'ok' => false,
                'message' => 'Koneksi ke Service B belum dikonfigurasi.',
            ];
        }

        if ($accessToken === '') {
            return [
                'ok' => false,
                'message' => 'Sesi login tidak valid. Silakan login ulang lewat Keycloak.',
            ];
        }

        $url = rtrim($baseUrl, '/').'/'.ltrim($latestPath, '/');

        try {
            $response = Http::acceptJson()
                ->withToken($accessToken)
                ->timeout($timeout)
                ->get($url);
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'message' => 'Tidak dapat terhubung ke Service B.',
            ];
        }

        if (! $response->successful()) {
            $responseMessage = trim((string) data_get($response->json(), 'message', ''));

            return [
                'ok' => false,
                'message' => $responseMessage !== ''
                    ? $responseMessage
                    : 'Data analisis terbaru dari Service B belum tersedia.',
            ];
        }

        $payload = $response->json();
        $data = data_get($payload, 'data');

        if (! is_array($data) || ! is_array(data_get($data, 'metrics'))) {
            return [
                'ok' => false,
                'message' => 'Format data analisis dari Service B tidak valid.',
            ];
        }

        return [
            'ok' => true,
            'message' => 'OK',
            'data' => $data,
        ];
    }

    /**
     * @param array<string, mixed> $analysisData
     * @return array<string, mixed>
     */
    public function toPlannerPayload(array $analysisData, int $userId): array
    {
        $metrics = is_array(data_get($analysisData, 'metrics'))
            ? (array) data_get($analysisData, 'metrics')
            : [];

        $summary = trim((string) data_get($analysisData, 'ai_insight.text', ''));

        if ($summary === '') {
            $summary = trim((string) data_get($metrics, 'summary', ''));
        }

        if ($summary === '') {
            $summary = 'Analisis otomatis terbaru dari Service B.';
        }

        $topCategory = trim((string) data_get($metrics, 'top_category', ''));

        if ($topCategory === '') {
            $topCategory = 'uncategorized';
        }

        $payload = [
            'user_id' => $userId,
            'total_income' => (int) round((float) data_get($metrics, 'total_income', 0)),
            'total_expense' => (int) round((float) data_get($metrics, 'total_expense', 0)),
            'top_category' => $topCategory,
            'insight' => $summary,
        ];

        $savingsRate = data_get($metrics, 'savings_rate');

        if (is_numeric($savingsRate)) {
            $normalizedSavingsRate = (float) $savingsRate;

            if ($normalizedSavingsRate >= 1 && $normalizedSavingsRate <= 100) {
                $payload['saving_percentage'] = round($normalizedSavingsRate, 2);
            }
        }

        return $payload;
    }
}
