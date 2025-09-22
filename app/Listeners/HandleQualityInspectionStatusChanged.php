<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\QualityInspectionStatusChanged;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleQualityInspectionStatusChanged implements ShouldQueue
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
        private readonly NotificationService $notificationService
    ) {}

    public function handle(QualityInspectionStatusChanged $event): void
    {
        // Record activity logs
        $this->activityLogService->log(
            $event->inspection,
            'status_changed',
            "Quality inspection status from {$event->oldStatus} Change to {$event->newStatus}"
        );

        // Send notification
        $this->notificationService->sendQualityInspectionStatusChangedNotification(
            $event->inspection,
            $event->oldStatus,
            $event->newStatus
        );

        // Perform corresponding operations according to different states
        match($event->newStatus) {
            'passed' => $this->handlePassed($event),
            'failed' => $this->handleFailed($event),
            default => null,
        };
    }

    /**
     * Processing inspection status
     */
    private function handlePassed(QualityInspectionStatusChanged $event): void
    {
        // Update inventory status to available
        foreach ($event->inspection->items as $item) {
            if ($item->passed_quantity > 0) {
                // TODO: Update inventory status
            }
        }
    }

    /**
     * Processing test fails
     */
    private function handleFailed(QualityInspectionStatusChanged $event): void
    {
        // Create a return order
        if ($event->inspection->items->sum('failed_quantity') > 0) {
            // TODO: Create a return order
        }
    }
} 