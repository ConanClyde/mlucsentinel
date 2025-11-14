<?php

namespace App\Observers;

use App\Models\AdminRole;
use App\Services\StaticDataCacheService;
use Illuminate\Support\Facades\Cache;

class AdminRoleObserver
{
    /**
     * Handle the AdminRole "created" event.
     */
    public function created(AdminRole $adminRole): void
    {
        $this->clearCache($adminRole);
    }

    /**
     * Handle the AdminRole "updated" event.
     */
    public function updated(AdminRole $adminRole): void
    {
        $this->clearCache($adminRole);
    }

    /**
     * Handle the AdminRole "deleted" event.
     */
    public function deleted(AdminRole $adminRole): void
    {
        $this->clearCache($adminRole);
    }

    /**
     * Handle the AdminRole "restored" event.
     */
    public function restored(AdminRole $adminRole): void
    {
        $this->clearCache($adminRole);
    }

    /**
     * Handle the AdminRole "force deleted" event.
     */
    public function forceDeleted(AdminRole $adminRole): void
    {
        $this->clearCache($adminRole);
    }

    /**
     * Clear all caches related to the admin role.
     */
    protected function clearCache(AdminRole $adminRole): void
    {
        // Clear static data cache
        StaticDataCacheService::clearCacheByModel('AdminRole');

        // Clear privilege caches for this specific role
        Cache::forget("admin_role_{$adminRole->id}_privileges");

        // Clear all privilege check caches for this role
        $privileges = \App\Models\Privilege::pluck('name');
        foreach ($privileges as $privilege) {
            Cache::forget("admin_role_{$adminRole->id}_privilege_{$privilege}");
        }
    }
}
