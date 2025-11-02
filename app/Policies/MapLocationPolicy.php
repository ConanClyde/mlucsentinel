<?php

namespace App\Policies;

use App\Enums\UserType;
use App\Models\MapLocation;
use App\Models\User;

class MapLocationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Anyone authenticated can view map locations
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MapLocation $mapLocation): bool
    {
        // Anyone authenticated can view individual locations
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins can create map locations
        return $user->user_type->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MapLocation $mapLocation): bool
    {
        // Only admins can update map locations
        return $user->user_type->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MapLocation $mapLocation): bool
    {
        // Only admins can delete map locations
        return $user->user_type->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MapLocation $mapLocation): bool
    {
        // Only admins can restore map locations
        return $user->user_type->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MapLocation $mapLocation): bool
    {
        // Only global admins can force delete
        return $user->user_type === UserType::GlobalAdministrator;
    }

    /**
     * Determine whether the user can toggle the active status.
     */
    public function toggleActive(User $user, MapLocation $mapLocation): bool
    {
        // Only admins can toggle active status
        return $user->user_type->isAdmin();
    }

    /**
     * Determine whether the user can download the location sticker.
     */
    public function downloadSticker(User $user, MapLocation $mapLocation): bool
    {
        // Admins and security personnel can download location stickers
        return $user->user_type->isAdmin() || $user->user_type === UserType::Security;
    }

    /**
     * Determine whether the user can check in at this location.
     */
    public function checkIn(User $user, MapLocation $mapLocation): bool
    {
        // Only security personnel can check in at locations
        // And only at active locations
        return $user->user_type === UserType::Security && $mapLocation->is_active;
    }
}
