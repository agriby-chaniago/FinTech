<?php

namespace App\Services;

class FintrackAutoAnalyzeService
{
    public function __construct(
        private readonly FinancialAnalysisService $financialAnalysisService,
        private readonly FintrackFeedService $fintrackFeedService,
        private readonly FintrackFeedSyncStateService $fintrackFeedSyncStateService
    ) {
    }

    /**
     * @return array{message: string, source: array{user_id: int, fetched_transactions: int, since_used: ?string, since_source: string, next_since: ?string, date_from: ?string, date_to: ?string}, analysis: array<string, mixed>|null}
     */
    public function run(
        ?int $userId = null,
        ?string $since = null,
        bool $includeSummary = false,
        bool $useSavedSince = true,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $keycloakSub = null,
        ?string $email = null
    ): array {
        $resolvedUserId = $userId ?? (int) config('services.fintrack_feed.default_user_id', 2);

        $resolvedSince = $since;
        $sinceSource = $resolvedSince !== null ? 'request' : 'none';

        if ($resolvedSince === null && $useSavedSince) {
            $resolvedSince = $this->fintrackFeedSyncStateService->getSince($resolvedUserId);
            $sinceSource = $resolvedSince !== null ? 'saved' : 'none';
        }

        $feed = $this->fintrackFeedService->fetchTransactions(
            $resolvedUserId,
            $resolvedSince,
            $includeSummary,
            $dateFrom,
            $dateTo,
            $keycloakSub,
            $email
        );

        if ($useSavedSince && $dateFrom === null && $dateTo === null) {
            $this->fintrackFeedSyncStateService->saveSince($resolvedUserId, $feed['next_since']);
        }

        $source = [
            'user_id' => $resolvedUserId,
            'fetched_transactions' => $feed['fetched_count'],
            'since_used' => $feed['since_used'],
            'since_source' => $sinceSource,
            'next_since' => $feed['next_since'],
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        if ($feed['fetched_count'] === 0) {
            return [
                'message' => 'Tidak ada transaksi baru dari FinTrack feed.',
                'source' => $source,
                'analysis' => null,
            ];
        }

        $analysis = $this->financialAnalysisService->analyze(
            $resolvedUserId,
            $feed['transactions']
        );

        return [
            'message' => 'Analisis otomatis berhasil.',
            'source' => $source,
            'analysis' => $analysis,
        ];
    }
}
