<?php

namespace App\Observers;

use App\Models\StakeholderType;
use App\Services\StaticDataCacheService;

class StakeholderTypeObserver
{
    /**
     * Handle the StakeholderType "created" event.
     */
    public function created(StakeholderType $stakeholderType): void
    {
        StaticDataCacheService::clearCacheByModel('StakeholderType');
    }

    /**
     * Handle the StakeholderType "updated" event.
     */
    public function updated(StakeholderType $stakeholderType): void
    {
        StaticDataCacheService::clearCacheByModel('StakeholderType');
    }

    /**
     * Handle the StakeholderType "deleted" event.
     */
    public function deleted(StakeholderType $stakeholderType): void
    {
        StaticDataCacheService::clearCacheByModel('StakeholderType');
    }

    /**
     * Handle the StakeholderType "restored" event.
     */
    public function restored(StakeholderType $stakeholderType): void
    {
        StaticDataCacheService::clearCacheByModel('StakeholderType');
    }

    /**
     * Handle the StakeholderType "force deleted" event.
     */
    public function forceDeleted(StakeholderType $stakeholderType): void
    {
        StaticDataCacheService::clearCacheByModel('StakeholderType');
    }
}
