<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GroqPlannerService
{
    public function generatePlan(array $data): array
    {
        $prompt = 'Buatkan rencana keuangan dan strategi investasi sederhana berdasarkan data berikut: '
            .json_encode($data, JSON_UNESCAPED_UNICODE);

        $apiKey = (string) config('services.groq.api_key');
        $model = (string) config('services.groq.model', 'llama-3.1-8b-instant');
        $baseUrl = (string) config('services.groq.base_url', 'https://api.groq.com/openai/v1');

        if ($apiKey === '') {
            $fallbackRisk = $this->fallbackRiskLevel($data);

            return [
                'investment_recommendation' => $this->fallbackRecommendation($fallbackRisk),
                'risk_level' => $fallbackRisk,
                'prompt' => $prompt,
                'raw_response' => null,
                'response_payload' => [
                    'note' => 'GROQ_API_KEY belum dikonfigurasi.',
                ],
                'model' => $model,
            ];
        }

        $response = Http::baseUrl($baseUrl)
            ->withToken($apiKey)
            ->acceptJson()
            ->timeout(30)
            ->post('/chat/completions', [
                'model' => $model,
                'temperature' => 0.2,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Anda adalah asisten perencanaan keuangan. Jawaban harus JSON valid.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt."\n\nKembalikan JSON valid dengan key: investment_recommendation, risk_level.",
                    ],
                ],
            ]);

        $messageContent = data_get($response->json(), 'choices.0.message.content', '');
        $rawContent = $this->normalizeMessageContent($messageContent);
        $parsed = $this->parseJson($rawContent);

        $riskLevel = $this->normalizeRiskLevel($this->toText($parsed['risk_level'] ?? ''));
        if ($riskLevel === null) {
            $riskLevel = $this->fallbackRiskLevel($data);
        }

        $recommendation = trim($this->toText($parsed['investment_recommendation'] ?? ''));
        if ($recommendation === '') {
            $recommendation = $this->fallbackRecommendation($riskLevel);
        }

        if (! $response->successful()) {
            $parsed['http_error'] = [
                'status' => $response->status(),
                'body' => $response->body(),
            ];
        }

        return [
            'investment_recommendation' => $recommendation,
            'risk_level' => $riskLevel,
            'prompt' => $prompt,
            'raw_response' => $rawContent !== '' ? $rawContent : $response->body(),
            'response_payload' => $parsed,
            'model' => $model,
        ];
    }

    private function parseJson(string $content): array
    {
        $decoded = json_decode($content, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $content, $matches) === 1) {
            $decoded = json_decode($matches[0], true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function fallbackRiskLevel(array $data): string
    {
        $income = max(1, (int) ($data['total_income'] ?? 0));
        $savingAmount = (int) ($data['saving_amount'] ?? 0);

        if ($savingAmount <= 0) {
            return 'high';
        }

        $ratio = $savingAmount / $income;

        if ($ratio < 0.10) {
            return 'high';
        }

        if ($ratio < 0.25) {
            return 'medium';
        }

        return 'low';
    }

    private function fallbackRecommendation(string $riskLevel): string
    {
        return match ($riskLevel) {
            'high' => 'Fokus dana darurat dan reksa dana pasar uang.',
            'medium' => 'Kombinasi reksa dana pasar uang dan pendapatan tetap.',
            default => 'Reksa dana pasar uang atau indeks untuk jangka menengah-panjang.',
        };
    }

    private function normalizeRiskLevel(string $riskLevel): ?string
    {
        $normalized = strtolower(trim($riskLevel));

        if (in_array($normalized, ['low', 'rendah'], true)) {
            return 'low';
        }

        if (in_array($normalized, ['medium', 'sedang', 'menengah'], true)) {
            return 'medium';
        }

        if (in_array($normalized, ['high', 'tinggi'], true)) {
            return 'high';
        }

        return null;
    }

    private function normalizeMessageContent(mixed $messageContent): string
    {
        if (is_string($messageContent)) {
            return $messageContent;
        }

        if (is_array($messageContent)) {
            $parts = [];

            foreach ($messageContent as $part) {
                $text = $this->toText($part);

                if ($text !== '') {
                    $parts[] = $text;
                }
            }

            if ($parts !== []) {
                return implode("\n", $parts);
            }

            return json_encode($messageContent, JSON_UNESCAPED_UNICODE) ?: '';
        }

        return $this->toText($messageContent);
    }

    private function toText(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            if (array_key_exists('text', $value)) {
                return $this->toText($value['text']);
            }

            if (array_key_exists('content', $value)) {
                return $this->toText($value['content']);
            }

            $parts = [];

            foreach ($value as $item) {
                $text = $this->toText($item);

                if ($text !== '') {
                    $parts[] = $text;
                }
            }

            if ($parts !== []) {
                return implode(' ', $parts);
            }

            return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '';
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }

            return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '';
        }

        return '';
    }
}
