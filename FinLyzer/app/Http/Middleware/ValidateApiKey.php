<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configuredApiKey = trim((string) config('services.analyzer.api_key'));
        $acceptedHeaders = (array) config('services.analyzer.accepted_headers', ['x-api-key']);

        if ($configuredApiKey === '') {
            return new JsonResponse([
                'message' => 'ANALYZER_API_KEY belum dikonfigurasi di server.',
            ], 500);
        }

        $providedApiKey = $this->resolveApiKeyFromHeaders($request, $acceptedHeaders);
        $headerText = implode(' or ', $acceptedHeaders);

        if ($providedApiKey === '' || ! hash_equals($configuredApiKey, $providedApiKey)) {
            return new JsonResponse([
                'message' => sprintf('Invalid API key. Use %s header.', $headerText),
            ], 401);
        }

        return $next($request);
    }

    /**
     * @param array<int, string> $acceptedHeaders
     */
    private function resolveApiKeyFromHeaders(Request $request, array $acceptedHeaders): string
    {
        foreach ($acceptedHeaders as $headerName) {
            $headerValue = trim((string) $request->header($headerName, ''));

            if ($headerValue !== '') {
                return $headerValue;
            }
        }

        return '';
    }
}
