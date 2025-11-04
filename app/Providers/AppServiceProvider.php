<?php

namespace App\Providers;

use App\Models\AdminRole;
use App\Models\College;
use App\Models\MapLocation;
use App\Models\Payment;
use App\Models\StakeholderType;
use App\Models\VehicleType;
use App\Models\ViolationType;
use App\Observers\AdminRoleObserver;
use App\Observers\CollegeObserver;
use App\Observers\MapLocationObserver;
use App\Observers\PaymentObserver;
use App\Observers\StakeholderTypeObserver;
use App\Observers\VehicleTypeObserver;
use App\Observers\ViolationTypeObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers for cache invalidation
        VehicleType::observe(VehicleTypeObserver::class);
        College::observe(CollegeObserver::class);
        ViolationType::observe(ViolationTypeObserver::class);
        AdminRole::observe(AdminRoleObserver::class);
        StakeholderType::observe(StakeholderTypeObserver::class);
        Payment::observe(PaymentObserver::class);

        // Register map location observer for sticker generation
        MapLocation::observe(MapLocationObserver::class);
    }
}
