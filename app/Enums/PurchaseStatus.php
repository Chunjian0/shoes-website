<?php

declare(strict_types=1);

namespace App\Enums;

enum PurchaseStatus: string
{
    case DRAFT = 'draft';           // draft
    case PENDING = 'pending';       // Pending review
    case APPROVED = 'approved';     // Reviewed
    case REJECTED = 'rejected';     // Rejected
    case ORDERED = 'ordered';       // Ordered
    case PARTIALLY_RECEIVED = 'partially_received';  // Partial arrivals
    case RECEIVED = 'received';     // Arrived
    case COMPLETED = 'completed';   // Completed
    case CANCELLED = 'cancelled';   // Canceled

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'draft',
            self::PENDING => 'Pending review',
            self::APPROVED => 'Reviewed',
            self::REJECTED => 'Rejected',
            self::ORDERED => 'Ordered',
            self::PARTIALLY_RECEIVED => 'Partially Received',
            self::RECEIVED => 'Received',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Canceled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::PENDING => 'yellow',
            self::APPROVED => 'blue',
            self::REJECTED => 'red',
            self::ORDERED => 'green',
            self::PARTIALLY_RECEIVED => 'purple',
            self::RECEIVED => 'green',
            self::COMPLETED => 'green',
            self::CANCELLED => 'red',
        };
    }
} 