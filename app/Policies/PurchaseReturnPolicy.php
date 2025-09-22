<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PurchaseReturn;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseReturnPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the purchase return list
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view purchase returns');
    }

    /**
     * Determine whether the user can view the specified purchase returns
     */
    public function view(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->can('view purchase returns');
    }

    /**
     * Determine whether the user can create a purchase return
     */
    public function create(User $user): bool
    {
        return $user->can('create purchase returns');
    }

    /**
     * Determine whether the user can update the specified purchase return
     */
    public function update(User $user, PurchaseReturn $purchaseReturn): bool
    {
        if (!$user->can('edit purchase returns')) {
            return false;
        }

        return $purchaseReturn->status === 'pending';
    }

    /**
     * Determine whether the user can delete the specified purchase return
     */
    public function delete(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return false; // Deletion of purchase returns is not supported yet
    }

    /**
     * Determine whether the user can review and pass the purchase and return
     */
    public function approve(User $user, PurchaseReturn $purchaseReturn): bool
    {
        if (!$user->can('approve purchase returns')) {
            return false;
        }

        return $purchaseReturn->status === 'pending';
    }

    /**
     * Determine whether the user can refuse to purchase and return
     */
    public function reject(User $user, PurchaseReturn $purchaseReturn): bool
    {
        if (!$user->can('reject purchase returns')) {
            return false;
        }

        return $purchaseReturn->status === 'pending';
    }

    /**
     * Determine whether the user can complete the purchase and return
     */
    public function complete(User $user, PurchaseReturn $purchaseReturn): bool
    {
        if (!$user->can('complete purchase returns')) {
            return false;
        }

        return $purchaseReturn->status === 'approved' && $purchaseReturn->refund_status === 'completed';
    }
} 