<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class Service3CallbackService
{
    /**
     * @param array<string, mixed> $payload
     */
    public function sendPlanResult(array $payload): void
    {
        $enabled = (bool) config('services.service1_callback.enabled', false);
        $url = trim((string) config('services.service1_callback.url', ''));
        $apiKey = trim((string) config('services.service1_callback.api_key', ''));
        $timeout = (int) config('services.service1_callback.timeout', 10);

        if (! $enabled) {
            return;
        }

        if ($url === '' || $apiKey === '') {
            Log::warning('Service1 callback enabled but configuration is incomplete.', [
                'url_configured' => $url !== '',
                'api_key_configured' => $apiKey !== '',
            ]);

            return;
        }

        try {
            $response = Http::acceptJson()
                ->timeout(max(1, $timeout))
                ->withHeaders([
                    'x-api-key' => $apiKey,
                ])
                ->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('Service1 callback failed.', [
                    'url' => $url,
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'correlation_id' => data_get($payload, 'correlation_id'),
                    'user_id' => data_get($payload, 'user_id'),
                ]);
            }
        } catch (Throwable $exception) {
            Log::warning('Service1 callback unreachable.', [
                'url' => $url,
                'error' => $exception->getMessage(),
                'correlation_id' => data_get($payload, 'correlation_id'),
                'user_id' => data_get($payload, 'user_id'),
            ]);
        }
    }
}
