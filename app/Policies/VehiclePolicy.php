<?php

namespace App\Policies;

use App\Enums\UserType;
use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admins can view all vehicles
        return $user->user_type->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vehicle $vehicle): bool
    {
        // User can view their own vehicle or admins can view any
        return $user->id === $vehicle->user_id || $user->user_type->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins can create vehicles
        return $user->user_type->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vehicle $vehicle): bool
    {
        // Only admins can update vehicles
        return $user->user_type->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vehicle $vehicle): bool
    {
        // Only admins can delete vehicles
        return $user->user_type->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Vehicle $vehicle): bool
    {
        // Only admins can restore vehicles
        return $user->user_type->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Vehicle $vehicle): bool
    {
        // Only global admins can force delete
        return $user->user_type === UserType::GlobalAdministrator;
    }

    /**
     * Determine whether the user can download the sticker.
     */
    public function downloadSticker(User $user, Vehicle $vehicle): bool
    {
        // User can download their own sticker, or Marketing admin can download any
        return $user->id === $vehicle->user_id || $user->isMarketingAdmin();
    }
}
