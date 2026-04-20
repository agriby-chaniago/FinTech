<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FintrackFeedService
{
    private const TYPE_INCOME = 'income';
    private const TYPE_EXPENSE = 'expense';

    /**
     * @return array{transactions: array<int, array{amount: float, category: string, type: string}>, fetched_count: int, next_since: ?string, since_used: ?string}
     */
    public function fetchTransactions(
        int $userId,
        ?string $since = null,
        bool $includeSummary = false,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array
    {
        $baseUrl = trim((string) config('services.fintrack_feed.base_url'));
        $apiKey = trim((string) config('services.fintrack_feed.api_key'));
        $apiKeyHeader = trim((string) config('services.fintrack_feed.api_key_header', 'x-api-key'));
        $pathTemplate = trim((string) config('services.fintrack_feed.path', '/api/service2/users/{user_id}/transactions-feed'));
        $timeout = (int) config('services.fintrack_feed.timeout', 20);
        $retryTimes = (int) config('services.fintrack_feed.retry_times', 2);
        $retrySleepMs = (int) config('services.fintrack_feed.retry_sleep_ms', 300);

        $this->guardAgainstSelfCall($baseUrl);

        if ($baseUrl === '' || $apiKey === '') {
            throw new RuntimeException('Konfigurasi FinTrack feed belum lengkap. Periksa FINTRACK_FEED_BASE_URL dan FINTRACK_FEED_API_KEY.');
        }

        $path = str_replace('{user_id}', (string) $userId, $pathTemplate);
        $url = rtrim($baseUrl, '/').'/'.ltrim($path, '/');

        $query = [];

        if ($since !== null) {
            $query['since'] = $since;
        }

        if ($dateFrom !== null) {
            $query['date_from'] = $dateFrom;
        }

        if ($dateTo !== null) {
            $query['date_to'] = $dateTo;
        }

        if (! $includeSummary) {
            $query['include_summary'] = 0;
        }

        $response = Http::timeout($timeout)
            ->retry($retryTimes, $retrySleepMs, null, false)
            ->acceptJson()
            ->withHeaders([
                'Accept' => 'application/json',
                $apiKeyHeader => $apiKey,
            ])
            ->get($url, $query);

        if ($response->failed()) {
            Log::warning('FinTrack feed request failed.', [
                'status' => $response->status(),
                'url' => $url,
                'response_body' => mb_substr($response->body(), 0, 600),
            ]);

            throw new RuntimeException('Gagal mengambil transaksi dari FinTrack feed.');
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('Response FinTrack feed tidak valid.');
        }

        $rawTransactions = $this->extractRawTransactions($payload);

        $filteredTransactions = $this->filterTransactionsByDateRange(
            $rawTransactions,
            $dateFrom,
            $dateTo
        );

        /** @var array<int, array{amount: float, category: string, type: string}> $transactions */
        $transactions = collect($filteredTransactions)
            ->map(fn (array $transaction): ?array => $this->normalizeTransaction($transaction))
            ->filter(fn (?array $transaction): bool => $transaction !== null)
            ->values()
            ->all();

        return [
            'transactions' => $transactions,
            'fetched_count' => count($transactions),
            'next_since' => $this->extractNextSince($payload),
            'since_used' => $since,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $transactions
     * @return array<int, array<string, mixed>>
     */
    private function filterTransactionsByDateRange(array $transactions, ?string $dateFrom, ?string $dateTo): array
    {
        $startTimestamp = $this->resolveRangeTimestamp($dateFrom, true);
        $endTimestamp = $this->resolveRangeTimestamp($dateTo, false);

        if ($startTimestamp === null && $endTimestamp === null) {
            return $transactions;
        }

        /** @var array<int, array<string, mixed>> $filtered */
        $filtered = collect($transactions)
            ->filter(function (array $transaction) use ($startTimestamp, $endTimestamp): bool {
                $transactionTimestamp = $this->extractTransactionTimestamp($transaction);

                if ($transactionTimestamp === null) {
                    return false;
                }

                if ($startTimestamp !== null && $transactionTimestamp < $startTimestamp) {
                    return false;
                }

                if ($endTimestamp !== null && $transactionTimestamp > $endTimestamp) {
                    return false;
                }

                return true;
            })
            ->values()
            ->all();

        return $filtered;
    }

    private function resolveRangeTimestamp(?string $dateValue, bool $isStart): ?int
    {
        if (! is_string($dateValue) || trim($dateValue) === '') {
            return null;
        }

        $suffix = $isStart ? ' 00:00:00' : ' 23:59:59';
        $timestamp = strtotime(trim($dateValue).$suffix);

        return $timestamp !== false ? $timestamp : null;
    }

    /**
     * @param array<string, mixed> $transaction
     */
    private function extractTransactionTimestamp(array $transaction): ?int
    {
        foreach (['transaction_date', 'date', 'tanggal', 'updated_at', 'created_at'] as $key) {
            $value = $transaction[$key] ?? null;

            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $timestamp = strtotime(trim($value));

            if ($timestamp !== false) {
                return $timestamp;
            }
        }

        return null;
    }

    private function guardAgainstSelfCall(string $baseUrl): void
    {
        $appUrl = trim((string) config('app.url', ''));

        if ($baseUrl === '' || $appUrl === '') {
            return;
        }

        $base = parse_url($baseUrl);
        $app = parse_url($appUrl);

        if (! is_array($base) || ! is_array($app)) {
            return;
        }

        $baseHost = strtolower((string) ($base['host'] ?? ''));
        $appHost = strtolower((string) ($app['host'] ?? ''));

        if ($baseHost === '' || $appHost === '' || $baseHost !== $appHost) {
            return;
        }

        $baseScheme = strtolower((string) ($base['scheme'] ?? 'http'));
        $appScheme = strtolower((string) ($app['scheme'] ?? 'http'));

        $basePort = (int) ($base['port'] ?? ($baseScheme === 'https' ? 443 : 80));
        $appPort = (int) ($app['port'] ?? ($appScheme === 'https' ? 443 : 80));

        if ($basePort === $appPort) {
            throw new RuntimeException('FINTRACK_FEED_BASE_URL mengarah ke service ini sendiri. Gunakan host/port Service 1 yang berbeda.');
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<int, array<string, mixed>>
     */
    private function extractRawTransactions(array $payload): array
    {
        $candidates = [
            data_get($payload, 'transactions'),
            data_get($payload, 'data.transactions'),
            data_get($payload, 'data.items'),
            data_get($payload, 'data'),
            data_get($payload, 'result.transactions'),
            data_get($payload, 'result.items'),
            data_get($payload, 'result'),
        ];

        foreach ($candidates as $candidate) {
            if (! is_array($candidate) || ! array_is_list($candidate)) {
                continue;
            }

            /** @var array<int, array<string, mixed>> $rows */
            $rows = collect($candidate)
                ->filter(fn (mixed $row): bool => is_array($row))
                ->values()
                ->all();

            return $rows;
        }

        return [];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractNextSince(array $payload): ?string
    {
        $nextSince = data_get($payload, 'meta.next_since');

        if (! is_string($nextSince) || trim($nextSince) === '') {
            $nextSince = data_get($payload, 'next_since');
        }

        if (! is_string($nextSince)) {
            return null;
        }

        $trimmed = trim($nextSince);

        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * @param array<string, mixed> $transaction
     * @return array{amount: float, category: string, type: string}|null
     */
    private function normalizeTransaction(array $transaction): ?array
    {
        $amount = $this->extractAmount($transaction);

        if ($amount === null) {
            return null;
        }

        $type = $this->extractType($transaction, $amount);

        if ($type === null) {
            return null;
        }

        $category = $this->extractCategory($transaction);

        return [
            'amount' => round(abs($amount), 2),
            'category' => $category !== '' ? strtolower($category) : 'uncategorized',
            'type' => $type,
        ];
    }

    /**
     * @param array<string, mixed> $transaction
     */
    private function extractAmount(array $transaction): ?float
    {
        foreach (['amount', 'nominal', 'value', 'transaction_amount', 'total'] as $key) {
            if (array_key_exists($key, $transaction) && is_numeric($transaction[$key])) {
                return (float) $transaction[$key];
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $transaction
     */
    private function extractCategory(array $transaction): string
    {
        foreach (['category', 'category_name', 'group', 'label'] as $key) {
            $value = $transaction[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $transaction
     */
    private function extractType(array $transaction, float $amount): ?string
    {
        foreach (['type', 'transaction_type', 'flow', 'direction', 'kind'] as $key) {
            $raw = $transaction[$key] ?? null;

            if (! is_string($raw)) {
                continue;
            }

            $normalized = strtolower(trim($raw));

            if (in_array($normalized, ['income', 'in', 'incoming', 'credit', 'pemasukan'], true)) {
                return self::TYPE_INCOME;
            }

            if (in_array($normalized, ['expense', 'out', 'outgoing', 'debit', 'pengeluaran'], true)) {
                return self::TYPE_EXPENSE;
            }
        }

        return $amount < 0 ? self::TYPE_EXPENSE : self::TYPE_INCOME;
    }
}
