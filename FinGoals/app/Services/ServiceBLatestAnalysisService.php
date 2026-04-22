<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
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
        $internalLatestPath = trim((string) config('services.service_b_analyzer.internal_latest_path', '/api/internal/analyze/auto/latest'));
        $apiKey = trim((string) config('services.service_b_analyzer.api_key', ''));
        $apiKeyHeader = trim((string) config('services.service_b_analyzer.api_key_header', 'x-api-key'));
        $timeout = (int) config('services.service_b_analyzer.timeout', 10);
        $accessToken = trim((string) data_get($context, 'access_token', ''));

        $userId = is_numeric(data_get($context, 'user_id'))
            ? (int) data_get($context, 'user_id')
            : null;
        $userEmail = strtolower(trim((string) data_get($context, 'user_email', '')));
        $keycloakSub = trim((string) data_get($context, 'keycloak_sub', ''));

        if ($baseUrl === '') {
            return [
                'ok' => false,
                'message' => 'Koneksi ke Service B belum dikonfigurasi.',
            ];
        }

        $query = [];

        if (is_int($userId) && $userId > 0) {
            $query['user_id'] = $userId;
        }

        if ($userEmail !== '' && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            $query['user_email'] = $userEmail;
        }

        if ($keycloakSub !== '') {
            $query['keycloak_sub'] = $keycloakSub;
        }

        $userScopedUrl = rtrim($baseUrl, '/').'/'.ltrim($latestPath, '/');

        $bearerResponse = null;

        if ($accessToken !== '') {
            try {
                $bearerResponse = Http::acceptJson()
                    ->withToken($accessToken)
                    ->timeout($timeout)
                    ->get($userScopedUrl, $query);
            } catch (Throwable) {
                $bearerResponse = null;
            }

            if ($bearerResponse instanceof Response && $bearerResponse->successful()) {
                return $this->normalizeLatestPayload($bearerResponse);
            }

            // Keep the original upstream error when bearer fails for reasons other than token/auth.
            if ($bearerResponse instanceof Response && $bearerResponse->status() !== 401) {
                return [
                    'ok' => false,
                    'message' => $this->resolveErrorMessage(
                        $bearerResponse,
                        'Data analisis terbaru dari Service B belum tersedia.'
                    ),
                ];
            }
        }

        if ($apiKey === '') {
            if ($accessToken === '') {
                return [
                    'ok' => false,
                    'message' => 'Sesi login tidak valid. Silakan login ulang lewat Keycloak.',
                ];
            }

            return [
                'ok' => false,
                'message' => 'Token OIDC tidak valid dan API key Service B belum dikonfigurasi.',
            ];
        }

        $internalUrl = rtrim($baseUrl, '/').'/'.ltrim($internalLatestPath, '/');

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'Accept' => 'application/json',
                    $apiKeyHeader => $apiKey,
                ])
                ->timeout($timeout)
                ->get($internalUrl, $query);
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'message' => 'Tidak dapat terhubung ke Service B.',
            ];
        }

        if (! $response->successful()) {
            return [
                'ok' => false,
                'message' => $this->resolveErrorMessage(
                    $response,
                    'Data analisis terbaru dari Service B belum tersedia.'
                ),
            ];
        }

        return $this->normalizeLatestPayload($response);
    }

    private function normalizeLatestPayload(Response $response): array
    {

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

    private function resolveErrorMessage(Response $response, string $defaultMessage): string
    {
        $responseMessage = trim((string) data_get($response->json(), 'message', ''));

        if ($responseMessage !== '') {
            return $responseMessage;
        }

        return $defaultMessage;
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
