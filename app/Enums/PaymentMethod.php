<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';                 // cash
    case BANK_TRANSFER = 'bank_transfer'; // Bank transfer
    case CHECK = 'check';               // Cheque
    case CREDIT_CARD = 'credit_card';   // credit card
    case ONLINE = 'online';             // Pay online
    case OTHER = 'other';               // other

    public function label(): string
    {
        return match($this) {
            self::CASH => 'cash',
            self::BANK_TRANSFER => 'Bank transfer',
            self::CHECK => 'Cheque',
            self::CREDIT_CARD => 'credit card',
            self::ONLINE => 'Pay online',
            self::OTHER => 'other',
        };
    }
} 