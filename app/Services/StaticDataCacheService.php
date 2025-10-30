<?php

namespace App\Services;

use App\Models\AdminRole;
use App\Models\College;
use App\Models\StakeholderType;
use App\Models\VehicleType;
use App\Models\ViolationType;
use Illuminate\Support\Facades\Cache;

class StaticDataCacheService
{
    /**
     * Cache duration in minutes
     */
    const CACHE_DURATION = 60; // 1 hour

    /**
     * Cache keys
     */
    const VEHICLE_TYPES_KEY = 'static_data.vehicle_types';
    const COLLEGES_KEY = 'static_data.colleges';
    const VIOLATION_TYPES_KEY = 'static_data.violation_types';
    const ADMIN_ROLES_KEY = 'static_data.admin_roles';
    const STAKEHOLDER_TYPES_KEY = 'static_data.stakeholder_types';

    /**
     * Get all vehicle types with caching
     */
    public static function getVehicleTypes(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(self::VEHICLE_TYPES_KEY, self::CACHE_DURATION, function () {
            return VehicleType::orderBy('name')->get();
        });
    }

    /**
     * Get all colleges with caching
     */
    public static function getColleges(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(self::COLLEGES_KEY, self::CACHE_DURATION, function () {
            return College::orderBy('name')->get();
        });
    }

    /**
     * Get all violation types with caching
     */
    public static function getViolationTypes(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(self::VIOLATION_TYPES_KEY, self::CACHE_DURATION, function () {
            return ViolationType::orderBy('name')->get();
        });
    }

    /**
     * Get all admin roles with caching
     */
    public static function getAdminRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(self::ADMIN_ROLES_KEY, self::CACHE_DURATION, function () {
            return AdminRole::orderBy('name')->get();
        });
    }

    /**
     * Get all stakeholder types with caching
     */
    public static function getStakeholderTypes(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(self::STAKEHOLDER_TYPES_KEY, self::CACHE_DURATION, function () {
            return StakeholderType::orderBy('name')->get();
        });
    }

    /**
     * Get vehicle types as array for select options
     */
    public static function getVehicleTypesForSelect(): array
    {
        return Cache::remember(self::VEHICLE_TYPES_KEY . '.select', self::CACHE_DURATION, function () {
            return VehicleType::orderBy('name')->pluck('name', 'id')->toArray();
        });
    }

    /**
     * Get colleges as array for select options
     */
    public static function getCollegesForSelect(): array
    {
        return Cache::remember(self::COLLEGES_KEY . '.select', self::CACHE_DURATION, function () {
            return College::orderBy('name')->pluck('name', 'id')->toArray();
        });
    }

    /**
     * Get violation types as array for select options
     */
    public static function getViolationTypesForSelect(): array
    {
        return Cache::remember(self::VIOLATION_TYPES_KEY . '.select', self::CACHE_DURATION, function () {
            return ViolationType::orderBy('name')->pluck('name', 'id')->toArray();
        });
    }

    /**
     * Get admin roles as array for select options
     */
    public static function getAdminRolesForSelect(): array
    {
        return Cache::remember(self::ADMIN_ROLES_KEY . '.select', self::CACHE_DURATION, function () {
            return AdminRole::orderBy('name')->pluck('name', 'id')->toArray();
        });
    }

    /**
     * Get stakeholder types as array for select options
     */
    public static function getStakeholderTypesForSelect(): array
    {
        return Cache::remember(self::STAKEHOLDER_TYPES_KEY . '.select', self::CACHE_DURATION, function () {
            return StakeholderType::orderBy('name')->pluck('name', 'id')->toArray();
        });
    }

    /**
     * Clear all static data cache
     */
    public static function clearAllCache(): void
    {
        Cache::forget(self::VEHICLE_TYPES_KEY);
        Cache::forget(self::COLLEGES_KEY);
        Cache::forget(self::VIOLATION_TYPES_KEY);
        Cache::forget(self::ADMIN_ROLES_KEY);
        Cache::forget(self::STAKEHOLDER_TYPES_KEY);
        
        // Clear select option caches
        Cache::forget(self::VEHICLE_TYPES_KEY . '.select');
        Cache::forget(self::COLLEGES_KEY . '.select');
        Cache::forget(self::VIOLATION_TYPES_KEY . '.select');
        Cache::forget(self::ADMIN_ROLES_KEY . '.select');
        Cache::forget(self::STAKEHOLDER_TYPES_KEY . '.select');
    }

    /**
     * Clear specific cache by model
     */
    public static function clearCacheByModel(string $model): void
    {
        switch ($model) {
            case 'VehicleType':
                Cache::forget(self::VEHICLE_TYPES_KEY);
                Cache::forget(self::VEHICLE_TYPES_KEY . '.select');
                break;
            case 'College':
                Cache::forget(self::COLLEGES_KEY);
                Cache::forget(self::COLLEGES_KEY . '.select');
                break;
            case 'ViolationType':
                Cache::forget(self::VIOLATION_TYPES_KEY);
                Cache::forget(self::VIOLATION_TYPES_KEY . '.select');
                break;
            case 'AdminRole':
                Cache::forget(self::ADMIN_ROLES_KEY);
                Cache::forget(self::ADMIN_ROLES_KEY . '.select');
                break;
            case 'StakeholderType':
                Cache::forget(self::STAKEHOLDER_TYPES_KEY);
                Cache::forget(self::STAKEHOLDER_TYPES_KEY . '.select');
                break;
        }
    }

    /**
     * Warm up all caches
     */
    public static function warmUpCache(): void
    {
        self::getVehicleTypes();
        self::getColleges();
        self::getViolationTypes();
        self::getAdminRoles();
        self::getStakeholderTypes();
        
        self::getVehicleTypesForSelect();
        self::getCollegesForSelect();
        self::getViolationTypesForSelect();
        self::getAdminRolesForSelect();
        self::getStakeholderTypesForSelect();
    }
}