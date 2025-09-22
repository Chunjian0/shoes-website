<?php

declare(strict_types=1);

namespace App\Enums;

enum QualityInspectionStatus: string
{
    case PENDING = 'pending';   // Pending review
    case PASSED = 'passed';     // pass
    case FAILED = 'failed';     // Not passed

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending review',
            self::PASSED => 'pass',
            self::FAILED => 'Not passed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::PASSED => 'green',
            self::FAILED => 'red',
        };
    }
} 