<?php

namespace App\Console\Commands;

use App\Services\FintrackAutoAnalyzeService;
use Illuminate\Console\Command;
use Throwable;

class FintrackAutoAnalyzeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fintrack:auto-analyze
        {--user_id= : Optional user id override}
        {--since= : Optional since token override}
        {--include_summary : Include upstream summary}
        {--no-saved-since : Ignore saved since token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto pull transactions from FinTrack feed and run financial analysis';

    public function __construct(private readonly FintrackAutoAnalyzeService $fintrackAutoAnalyzeService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $optionUserId = $this->option('user_id');
        $optionSince = $this->option('since');

        $userId = is_numeric($optionUserId) ? (int) $optionUserId : null;
        $since = is_string($optionSince) && trim($optionSince) !== '' ? trim($optionSince) : null;

        $includeSummary = (bool) $this->option('include_summary');
        $useSavedSince = ! (bool) $this->option('no-saved-since');

        try {
            $result = $this->fintrackAutoAnalyzeService->run(
                $userId,
                $since,
                $includeSummary,
                $useSavedSince
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info($result['message']);
        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
