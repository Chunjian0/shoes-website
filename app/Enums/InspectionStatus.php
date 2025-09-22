<?php

declare(strict_types=1);

namespace App\Enums;

enum InspectionStatus: string
{
    case PENDING = 'pending';
    case PASSED = 'passed';
    case FAILED = 'failed';
    case PARTIALLY_PASSED = 'partially_passed';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'To be tested',
            self::PASSED => 'pass',
            self::FAILED => 'Not passed',
            self::PARTIALLY_PASSED => 'Partially passed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'gray',
            self::PASSED => 'green',
            self::FAILED => 'red',
            self::PARTIALLY_PASSED => 'yellow',
        };
    }
} 