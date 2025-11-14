<?php

namespace App\Observers;

use App\Models\Privilege;
use App\Services\StaticDataCacheService;
use Illuminate\Support\Facades\Cache;

class PrivilegeObserver
{
    /**
     * Handle the Privilege "created" event.
     */
    public function created(Privilege $privilege): void
    {
        $this->clearPrivilegeCache();
    }

    /**
     * Handle the Privilege "updated" event.
     */
    public function updated(Privilege $privilege): void
    {
        $this->clearPrivilegeCache();
    }

    /**
     * Handle the Privilege "deleted" event.
     */
    public function deleted(Privilege $privilege): void
    {
        $this->clearPrivilegeCache();
    }

    /**
     * Handle the Privilege "restored" event.
     */
    public function restored(Privilege $privilege): void
    {
        $this->clearPrivilegeCache();
    }

    /**
     * Handle the Privilege "force deleted" event.
     */
    public function forceDeleted(Privilege $privilege): void
    {
        $this->clearPrivilegeCache();
    }

    /**
     * Clear all privilege-related caches.
     */
    protected function clearPrivilegeCache(): void
    {
        // Clear static data cache
        StaticDataCacheService::clearCacheByModel('Privilege');

        // Clear all admin role privilege caches
        Cache::flush();
    }
}
