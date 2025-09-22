<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\QualityInspectionCreated;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleQualityInspectionCreated implements ShouldQueue
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
        private readonly NotificationService $notificationService
    ) {}

    public function handle(QualityInspectionCreated $event): void
    {
        // Record activity logs
        $this->activityLogService->log(
            $event->inspection,
            'created',
            'Created a quality inspection record'
        );

        // Send notification
        $this->notificationService->sendQualityInspectionCreatedNotification(
            $event->inspection
        );
    }
} 