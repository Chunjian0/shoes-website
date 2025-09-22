<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PurchaseStatusChanged;
use App\Models\Warehouse;
use App\Services\ActivityLogService;
use App\Services\StockService;
use App\Services\NotificationService;
use App\Services\PaymentPlanService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandlePurchaseStatusChanged implements ShouldQueue
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
        private readonly StockService $stockService,
        private readonly NotificationService $notificationService,
        private readonly PaymentPlanService $paymentPlanService,
    ) {}

    public function handle(PurchaseStatusChanged $event): void
    {
        // Record status change log
        $this->activityLogService->logStatusChanged(
            $event->purchase,
            $event->oldStatus,
            $event->newStatus
        );

        // Send notification
        $this->notificationService->sendPurchaseStatusNotification(
            $event->purchase,
            $event->oldStatus,
            $event->newStatus
        );

        // Execute the corresponding operation according to different states
        match ($event->newStatus) {
            'approved' => $this->handleApproved($event),
            'rejected' => $this->handleRejected($event),
            'cancelled' => $this->handleCancelled($event),
            'received' => $this->handleReceived($event),
            default => null,
        };
    }

    /**
     * Treatment of review and passing status
     */
    private function handleApproved(PurchaseStatusChanged $event): void
    {
        // Generate payment plan
        $this->paymentPlanService->generatePaymentPlan($event->purchase);
    }

    /**
     * Refusal state
     */
    private function handleRejected(PurchaseStatusChanged $event): void
    {
        // Cancelment payment plan
        $this->paymentPlanService->cancelPaymentPlan($event->purchase);
    }

    /**
     * Process cancellation state
     */
    private function handleCancelled(PurchaseStatusChanged $event): void
    {
        // Cancelment payment plan
        $this->paymentPlanService->cancelPaymentPlan($event->purchase);
    }

    /**
     * Treatment of the receipt to complete
     */
    private function handleReceived(PurchaseStatusChanged $event): void
    {
        // Get the default warehouse
        $warehouse = Warehouse::query()
            ->where('status', true)
            ->first();

        if (!$warehouse) {
            throw new \RuntimeException('No available warehouse');
        }

        // Process the warehouse
        $this->stockService->handlePurchaseReceived($event->purchase, $warehouse);
    }
}
