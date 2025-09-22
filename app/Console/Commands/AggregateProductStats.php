<?php

namespace App\Console\Commands;

use App\Services\ProductAnalyticsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AggregateProductStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:aggregate-stats 
                            {--date= : The date to aggregate stats for (YYYY-MM-DD), defaults to yesterday}
                            {--days= : Number of past days to aggregate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate product display statistics for reporting';

    /**
     * @var ProductAnalyticsService
     */
    protected $analyticsService;

    /**
     * Create a new command instance.
     */
    public function __construct(ProductAnalyticsService $analyticsService)
    {
        parent::__construct();
        $this->analyticsService = $analyticsService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $date = $this->option('date');
        $days = (int) $this->option('days');

        try {
            if ($days > 0) {
                $this->aggregateMultipleDays($days);
            } else {
                $this->aggregateSingleDay($date);
            }

            $duration = round(microtime(true) - $startTime, 2);
            $this->info("Aggregation completed in {$duration} seconds.");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Error during aggregation: {$e->getMessage()}");
            Log::error('Error in stats aggregation command', [
                'exception' => $e,
                'date' => $date,
                'days' => $days,
            ]);
            
            return 1;
        }
    }

    /**
     * Aggregate stats for a single day
     */
    protected function aggregateSingleDay(?string $date): void
    {
        if (!$date) {
            $date = Carbon::yesterday()->toDateString();
        }

        $this->info("Aggregating stats for {$date}...");
        $this->analyticsService->aggregateDailyStats($date);
        $this->info("Stats for {$date} aggregated successfully.");
    }

    /**
     * Aggregate stats for multiple days
     */
    protected function aggregateMultipleDays(int $days): void
    {
        $this->info("Aggregating stats for the past {$days} days...");

        $bar = $this->output->createProgressBar($days);
        $bar->start();

        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::today()->subDays($i + 1)->toDateString();
            $this->analyticsService->aggregateDailyStats($date);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Stats for the past {$days} days aggregated successfully.");
    }
} 