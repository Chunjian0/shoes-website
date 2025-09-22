<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PurchaseStatusChanged;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Contracts\Queue\ShouldQueue;

class PurchaseStatusChangedListener implements ShouldQueue
{
    public function __construct(
        private readonly StockService $stockService
    ) {}

    /**
     * Handling event
     */
    public function handle(PurchaseStatusChanged $event): void
    {
        // Execute the corresponding operation according to different states
        match ($event->newStatus) {
            'received' => $this->handleReceived($event),
            default => null,
        };
    }

    /**
     * Handling the delivery status
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