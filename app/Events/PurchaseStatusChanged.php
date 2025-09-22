<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Purchase;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Purchase $purchase,
        public readonly string $oldStatus,
        public readonly string $newStatus
    ) {}
} 