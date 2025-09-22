<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the payment record list
     */
    public function viewAny(User $user, Purchase $purchase): bool
    {
        return $user->can('view_payments');
    }

    /**
     * Determine whether the user can view the specified payment history
     */
    public function view(User $user, Payment $payment): bool
    {
        return $user->can('view_payments');
    }

    /**
     * Determine whether a user can create a payment record
     */
    public function create(User $user, Purchase $purchase): bool
    {
        if (!$user->can('create_payments')) {
            return false;
        }

        // Payment history can be created only if purchase orders that have not completed payments
        return !in_array($purchase->payment_status->value, ['paid', 'refunded']);
    }

    /**
     * Determine whether the user can delete the specified payment record
     */
    public function delete(User $user, Payment $payment): bool
    {
        if (!$user->can('delete_payments')) {
            return false;
        }

        // Only the payment history of the day can be deleted
        return $payment->created_at->isToday();
    }
} 