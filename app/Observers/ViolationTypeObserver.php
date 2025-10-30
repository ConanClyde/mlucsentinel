<?php

namespace App\Observers;

use App\Models\ViolationType;
use App\Services\StaticDataCacheService;

class ViolationTypeObserver
{
    /**
     * Handle the ViolationType "created" event.
     */
    public function created(ViolationType $violationType): void
    {
        StaticDataCacheService::clearCacheByModel('ViolationType');
    }

    /**
     * Handle the ViolationType "updated" event.
     */
    public function updated(ViolationType $violationType): void
    {
        StaticDataCacheService::clearCacheByModel('ViolationType');
    }

    /**
     * Handle the ViolationType "deleted" event.
     */
    public function deleted(ViolationType $violationType): void
    {
        StaticDataCacheService::clearCacheByModel('ViolationType');
    }

    /**
     * Handle the ViolationType "restored" event.
     */
    public function restored(ViolationType $violationType): void
    {
        StaticDataCacheService::clearCacheByModel('ViolationType');
    }

    /**
     * Handle the ViolationType "force deleted" event.
     */
    public function forceDeleted(ViolationType $violationType): void
    {
        StaticDataCacheService::clearCacheByModel('ViolationType');
    }
}
