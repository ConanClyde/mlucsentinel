<?php

namespace App\Observers;

use App\Models\College;
use App\Services\StaticDataCacheService;

class CollegeObserver
{
    /**
     * Handle the College "created" event.
     */
    public function created(College $college): void
    {
        StaticDataCacheService::clearCacheByModel('College');
    }

    /**
     * Handle the College "updated" event.
     */
    public function updated(College $college): void
    {
        StaticDataCacheService::clearCacheByModel('College');
    }

    /**
     * Handle the College "deleted" event.
     */
    public function deleted(College $college): void
    {
        StaticDataCacheService::clearCacheByModel('College');
    }

    /**
     * Handle the College "restored" event.
     */
    public function restored(College $college): void
    {
        StaticDataCacheService::clearCacheByModel('College');
    }

    /**
     * Handle the College "force deleted" event.
     */
    public function forceDeleted(College $college): void
    {
        StaticDataCacheService::clearCacheByModel('College');
    }
}
