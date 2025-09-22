<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PaymentStatusChanged;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use App\Services\PaymentPlanService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandlePaymentStatusChanged implements ShouldQueue
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
        private readonly NotificationService $notificationService,
        private readonly PaymentPlanService $paymentPlanService,
    ) {}

    public function handle(PaymentStatusChanged $event): void
    {
        // Record status change log
        $this->activityLogService->logStatusChanged(
            $event->purchase,
            $event->oldStatus,
            $event->newStatus,
            ['total_paid' => $event->totalPaid]
        );

        // Send notification
        $this->notificationService->sendPaymentStatusNotification(
            $event->purchase,
            $event->oldStatus,
            $event->newStatus,
            $event->totalPaid
        );

        // Update payment plan status
        $this->paymentPlanService->updatePaymentPlanStatus(
            $event->purchase,
            $event->totalPaid
        );

        // Perform corresponding operations according to different states
        match ($event->newStatus) {
            'paid' => $this->handlePaid($event),
            'partially_paid' => $this->handlePartiallyPaid($event),
            'overdue' => $this->handleOverdue($event),
            default => null,
        };
    }

    /**
     * Processing paid status
     */
    private function handlePaid(PaymentStatusChanged $event): void
    {
        // Already here PaymentPlanService Status updates for payment plan were processed
    }

    /**
     * Processing partial payment status
     */
    private function handlePartiallyPaid(PaymentStatusChanged $event): void
    {
        // Already here PaymentPlanService Status updates for payment plan were processed
    }

    /**
     * Handle overdue status
     */
    private function handleOverdue(PaymentStatusChanged $event): void
    {
        // Already here PaymentPlanService Status updates for payment plan were processed
    }
} 