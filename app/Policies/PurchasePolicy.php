<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the purchase order list
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view purchases');
    }

    /**
     * Determine whether the user can view the specified purchase order
     */
    public function view(User $user, Purchase $purchase): bool
    {
        return $user->can('view purchases');
    }

    /**
     * Determine whether a user can create a purchase order
     */
    public function create(User $user): bool
    {
        return $user->can('create purchases');
    }

    /**
     * Determine whether the user can update the specified purchase order
     */
    public function update(User $user, Purchase $purchase): bool
    {
        if (!$user->can('edit purchases')) {
            return false;
        }

        return $purchase->isEditable();
    }

    /**
     * Determine whether the user can delete the specified purchase order
     */
    public function delete(User $user, Purchase $purchase): bool
    {
        if (!$user->can('delete purchases')) {
            return false;
        }

        return $purchase->purchase_status === 'draft';
    }

    /**
     * Determine whether the user can cancel the specified purchase order
     */
    public function cancel(User $user, Purchase $purchase): bool
    {
        if (!$user->can('cancel purchases')) {
            return false;
        }

        return $purchase->isCancellable();
    }

    /**
     * Determine whether the user can confirm the receipt
     */
    public function confirmReceived(User $user, Purchase $purchase): bool
    {
        if (!$user->can('confirm purchases')) {
            return false;
        }

        return $purchase->purchase_status->value === 'approved' || $purchase->purchase_status->value === 'partially_received';
    }

    /**
     * Determine whether the user can review the purchase order
     */
    public function approve(User $user, Purchase $purchase): bool
    {
        if (!$user->can('approve purchases')) {
            return false;
        }

        return $purchase->purchase_status->value === 'pending';
    }

    /**
     * Determine whether the user can reject a purchase order
     */
    public function reject(User $user, Purchase $purchase): bool
    {
        if (!$user->can('reject purchases')) {
            return false;
        }

        return $purchase->purchase_status->value === 'pending';
    }
} 