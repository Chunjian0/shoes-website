<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PaymentPlanService;
use Illuminate\Console\Command;

class CheckOverduePayments extends Command
{
    protected $signature = 'payments:check-overdue';
    protected $description = 'Check and update the status of late payments';

    public function __construct(
        private readonly PaymentPlanService $paymentPlanService
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Start checking for late payments...');
        
        try {
            $this->paymentPlanService->checkOverduePayments();
            $this->info('The late payment inspection is completed');
        } catch (\Exception $e) {
            $this->error('An error occurred while checking for late payments:' . $e->getMessage());
        }
    }
} 