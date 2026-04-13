<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class FintrackFeedSyncStateService
{
    public function getSince(int $userId): ?string
    {
        $key = $this->cacheKey($userId);
        $value = Cache::get($key);

        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed !== '' ? $trimmed : null;
    }

    public function saveSince(int $userId, ?string $since): void
    {
        $key = $this->cacheKey($userId);

        if (! is_string($since) || trim($since) === '') {
            Cache::forget($key);
            return;
        }

        Cache::forever($key, trim($since));
    }

    private function cacheKey(int $userId): string
    {
        $prefix = trim((string) config('services.fintrack_feed.since_cache_prefix', 'fintrack_feed_since_user_'));

        return $prefix.$userId;
    }
}
