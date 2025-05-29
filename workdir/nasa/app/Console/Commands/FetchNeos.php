<?php

namespace App\Console\Commands;

use App\Services\NASA\NearEarthObjectsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FetchNeos extends Command
{
    protected $signature = 'neo:fetch {--from= : Start date (YYYY-MM-DD)} {--to= : End date (YYYY-MM-DD)}';
    protected $description = 'Fetch and store close approach NEOs for a date range. Uses yesterday if no date range is provided.';

    protected NearEarthObjectsService $nearEarthObjectsService;

    public function __construct(NearEarthObjectsService $nearEarthObjectsService)
    {
        parent::__construct();
        $this->nearEarthObjectsService = $nearEarthObjectsService;
    }

    public function handle(): void
    {
        $startDate = $this->option('from') ?: Carbon::yesterday()->toDateString();
        $endDate = $this->option('to') ?: $startDate;

        $this->info(sprintf('fetching for date range: %s to %s', $startDate, $endDate));

        $results = $this->nearEarthObjectsService->fetch($startDate, $endDate);

        $this->info('fetching completed');

        if (empty($results['new'])) {
            $this->info('No unregistered near earth objects found');
            return;
        }

        $this->info('analysing data...');

        $analysis = $this->nearEarthObjectsService->analyse($startDate, $endDate);

        if ($analysis) {
            $this->info('Analysis completed');
        } else {
            $this->info('Nothing to analyze');
        }
    }
}
