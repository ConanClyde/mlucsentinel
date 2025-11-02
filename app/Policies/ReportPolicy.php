<?php

namespace App\Policies;

use App\Enums\UserType;
use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admins and reporters can view reports
        return $user->user_type->isAdmin() || $user->user_type->canReport();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Report $report): bool
    {
        // User can view reports they created, reports assigned to them, or if they're admin
        return $user->id === $report->reported_by
            || $user->id === $report->assigned_to
            || $user->user_type->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only users with reporting capability can create reports
        return $user->user_type->canReport();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Report $report): bool
    {
        // Only the reporter can update before submission, or admins can always update
        if ($user->user_type->isAdmin()) {
            return true;
        }

        // Reporter can only update their own reports that are still pending
        return $user->id === $report->reported_by && $report->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Report $report): bool
    {
        // Only admins can delete reports
        return $user->user_type->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Report $report): bool
    {
        // Only admins can restore reports
        return $user->user_type->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Report $report): bool
    {
        // Only global admins can force delete
        return $user->user_type === UserType::GlobalAdministrator;
    }

    /**
     * Determine whether the user can update the report status.
     */
    public function updateStatus(User $user, Report $report): bool
    {
        // Only admins assigned to the report or global admins can update status
        return $user->user_type === UserType::GlobalAdministrator
            || ($user->user_type === UserType::Administrator && $user->id === $report->assigned_to);
    }

    /**
     * Determine whether the user can view the evidence.
     */
    public function viewEvidence(User $user, Report $report): bool
    {
        // Same as view - user can view evidence if they can view the report
        return $this->view($user, $report);
    }
}
