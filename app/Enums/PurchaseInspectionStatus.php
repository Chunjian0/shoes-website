<?php

declare(strict_types=1);

namespace App\Enums;

enum PurchaseInspectionStatus: string
{
    case PENDING = 'pending';       // To be tested
    case IN_PROGRESS = 'in_progress'; // Inspecting
    case PASSED = 'passed';         // Passed the inspection
    case FAILED = 'failed';         // Failed to pass the inspection
    case PARTIALLY_PASSED = 'partially_passed'; // Partially passed
    case PARTIALLY_INSPECTED = 'partially_inspected'; // Partial inspection

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'To be tested',
            self::IN_PROGRESS => 'Inspecting',
            self::PASSED => 'Passed the inspection',
            self::FAILED => 'Failed to pass the inspection',
            self::PARTIALLY_PASSED => 'Partially passed',
            self::PARTIALLY_INSPECTED => 'Partial inspection',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'gray',
            self::IN_PROGRESS => 'blue',
            self::PASSED => 'green',
            self::FAILED => 'red',
            self::PARTIALLY_PASSED => 'yellow',
            self::PARTIALLY_INSPECTED => 'orange',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PENDING => 'Purchase orders are to be inspected for quality',
            self::IN_PROGRESS => 'Purchase orders are undergoing quality inspection',
            self::PASSED => 'Purchase order has passed quality inspection',
            self::FAILED => 'Purchase order failed quality inspection',
            self::PARTIALLY_PASSED => 'The purchase order part passes quality inspection',
            self::PARTIALLY_INSPECTED => 'Some items in the purchase order have been inspected',
        };
    }
} 