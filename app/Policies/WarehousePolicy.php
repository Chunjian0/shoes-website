<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Auth\Access\HandlesAuthorization;

class WarehousePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the repository list
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view warehouses');
    }

    /**
     * Determine whether the user can view the specified repository
     */
    public function view(User $user, Warehouse $warehouse): bool
    {
        return $user->can('view warehouses');
    }

    /**
     * Determine whether a user can create a repository
     */
    public function create(User $user): bool
    {
        return $user->can('create warehouses');
    }

    /**
     * Determine whether the user can update the specified repository
     */
    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->can('edit warehouses');
    }

    /**
     * Determine whether the user can delete the specified repository
     */
    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->can('delete warehouses');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Warehouse $warehouse): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Warehouse $warehouse): bool
    {
        //
    }
}
