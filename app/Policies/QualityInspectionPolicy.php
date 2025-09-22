<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\QualityInspection;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QualityInspectionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any quality inspections.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view quality inspections');
    }

    /**
     * Determine whether the user can view quality inspection.
     */
    public function view(User $user, QualityInspection $qualityInspection): bool
    {
        return $user->can('view quality inspections');
    }

    /**
     * Determine whether the user can create a quality inspection.
     */
    public function create(User $user): bool
    {
        return $user->can('create quality inspections');
    }

    /**
     * Determine whether the user can update the quality inspection.
     */
    public function update(User $user, QualityInspection $qualityInspection): bool
    {
        if ($qualityInspection->status !== 'pending') {
            return false;
        }
        return $user->can('edit quality inspections');
    }

    /**
     * Determine whether the user can delete the quality inspection.
     */
    public function delete(User $user, QualityInspection $qualityInspection): bool
    {
        if ($qualityInspection->status !== 'pending') {
            return false;
        }
        return $user->can('delete quality inspections');
    }

    /**
     * Determine whether the user can pass the quality inspection.
     */
    public function approve(User $user, QualityInspection $qualityInspection): bool
    {
        if ($qualityInspection->status !== 'pending') {
            return false;
        }
        return $user->can('approve quality inspections');
    }

    /**
     * Determine whether the user can refuse quality inspection.
     */
    public function reject(User $user, QualityInspection $qualityInspection): bool
    {
        if ($qualityInspection->status !== 'pending') {
            return false;
        }
        return $user->can('reject quality inspections');
    }
} 