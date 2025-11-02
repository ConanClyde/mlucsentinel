<?php

namespace App\Observers;

use App\Models\VehicleType;
use App\Services\StaticDataCacheService;

class VehicleTypeObserver
{
    /**
     * Handle the VehicleType "created" event.
     */
    public function created(VehicleType $vehicleType): void
    {
        StaticDataCacheService::clearCacheByModel('VehicleType');
    }

    /**
     * Handle the VehicleType "updated" event.
     */
    public function updated(VehicleType $vehicleType): void
    {
        StaticDataCacheService::clearCacheByModel('VehicleType');
    }

    /**
     * Handle the VehicleType "deleted" event.
     */
    public function deleted(VehicleType $vehicleType): void
    {
        StaticDataCacheService::clearCacheByModel('VehicleType');
    }

    /**
     * Handle the VehicleType "restored" event.
     */
    public function restored(VehicleType $vehicleType): void
    {
        StaticDataCacheService::clearCacheByModel('VehicleType');
    }

    /**
     * Handle the VehicleType "force deleted" event.
     */
    public function forceDeleted(VehicleType $vehicleType): void
    {
        StaticDataCacheService::clearCacheByModel('VehicleType');
    }
}
