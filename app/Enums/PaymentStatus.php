<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'unpaid';                 // Unpaid
    case PARTIALLY_PAID = 'partially_paid';  // Partial payment
    case PAID = 'paid';                     // Payed
    case OVERDUE = 'overdue';               // Overdue
    case REFUNDED = 'refunded';             // Refunded

    public function label(): string
    {
        return match($this) {
            self::UNPAID => 'Unpaid',
            self::PARTIALLY_PAID => 'Partial payment',
            self::PAID => 'Payed',
            self::OVERDUE => 'Overdue',
            self::REFUNDED => 'Refunded',
        };
    }
} 