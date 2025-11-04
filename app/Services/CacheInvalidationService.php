<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheInvalidationService
{
    /**
     * Clear dashboard-related caches
     */
    public static function clearDashboardCache(): void
    {
        Cache::forget('dashboard.stats');
        Cache::forget('dashboard.reports_by_status');
        Cache::forget('dashboard.vehicle_types');
    }

    /**
     * Clear metrics-related caches
     */
    public static function clearMetricsCache(): void
    {
        Cache::forget('metrics.overview.stats');
        Cache::forget('metrics.overview.paymentsByStatus');
        Cache::forget('metrics.overview.reportsByStatus');
        Cache::forget('metrics.overview.userTypes');
        Cache::forget('metrics.overview.vehicleTypes');
        Cache::forget('metrics.violationsPerDay');
        Cache::forget('metrics.paymentsMonthly');
        Cache::forget('metrics.patrol.24h');
    }

    /**
     * Clear all dashboard and metrics caches
     */
    public static function clearAllAnalyticsCache(): void
    {
        self::clearDashboardCache();
        self::clearMetricsCache();
    }
}
