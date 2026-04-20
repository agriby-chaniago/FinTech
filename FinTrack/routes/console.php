<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('auth:purge-local-users {--force : Delete records instead of dry-run} {--chunk=200 : Batch size for deletion}', function () {
    $chunkSize = max(1, (int) $this->option('chunk'));
    $query = User::query()->whereNull('keycloak_sub');

    $count = (clone $query)->count();

    if ($count === 0) {
        $this->info('No local users without keycloak_sub were found.');

        return 0;
    }

    $sampleIds = (clone $query)
        ->orderBy('id')
        ->limit(10)
        ->pluck('id')
        ->all();

    $this->warn("Found {$count} users without keycloak_sub.");
    $this->line('Sample IDs: '.implode(', ', $sampleIds));

    if (! (bool) $this->option('force')) {
        $this->info('Dry-run only. Re-run with --force to perform deletion.');

        return 0;
    }

    $deleted = 0;

    DB::transaction(function () use ($chunkSize, &$deleted): void {
        User::query()
            ->whereNull('keycloak_sub')
            ->select('id')
            ->orderBy('id')
            ->chunkById($chunkSize, function ($rows) use (&$deleted): void {
                $ids = $rows->pluck('id')->all();

                if ($ids === []) {
                    return;
                }

                $deleted += User::query()
                    ->whereIn('id', $ids)
                    ->delete();
            });
    });

    $this->info("Deleted {$deleted} users without keycloak_sub.");

    return 0;
})->purpose('Dry-run or purge local users that are not mapped to Keycloak');
