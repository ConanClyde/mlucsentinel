<?php

namespace App\Observers;

use App\Models\AdminRole;
use App\Services\StaticDataCacheService;

class AdminRoleObserver
{
    /**
     * Handle the AdminRole "created" event.
     */
    public function created(AdminRole $adminRole): void
    {
        StaticDataCacheService::clearCacheByModel('AdminRole');
    }

    /**
     * Handle the AdminRole "updated" event.
     */
    public function updated(AdminRole $adminRole): void
    {
        StaticDataCacheService::clearCacheByModel('AdminRole');
    }

    /**
     * Handle the AdminRole "deleted" event.
     */
    public function deleted(AdminRole $adminRole): void
    {
        StaticDataCacheService::clearCacheByModel('AdminRole');
    }

    /**
     * Handle the AdminRole "restored" event.
     */
    public function restored(AdminRole $adminRole): void
    {
        StaticDataCacheService::clearCacheByModel('AdminRole');
    }

    /**
     * Handle the AdminRole "force deleted" event.
     */
    public function forceDeleted(AdminRole $adminRole): void
    {
        StaticDataCacheService::clearCacheByModel('AdminRole');
    }
}
