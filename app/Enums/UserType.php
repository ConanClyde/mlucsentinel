<?php

namespace App\Enums;

enum UserType: string
{
    case GlobalAdministrator = 'global_administrator';
    case Administrator = 'administrator';
    case Student = 'student';
    case Staff = 'staff';
    case Security = 'security';
    case Reporter = 'reporter';
    case Stakeholder = 'stakeholder';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::GlobalAdministrator => 'Global Administrator',
            self::Administrator => 'Administrator',
            self::Student => 'Student',
            self::Staff => 'Staff',
            self::Security => 'Security',
            self::Reporter => 'Reporter',
            self::Stakeholder => 'Stakeholder',
        };
    }

    /**
     * Get badge color for UI
     */
    public function color(): string
    {
        return match ($this) {
            self::GlobalAdministrator => 'purple',
            self::Administrator => 'blue',
            self::Student => 'green',
            self::Staff => 'indigo',
            self::Security => 'red',
            self::Reporter => 'orange',
            self::Stakeholder => 'gray',
        };
    }

    /**
     * Get Tailwind CSS classes for badge
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::GlobalAdministrator => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            self::Administrator => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            self::Student => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            self::Staff => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
            self::Security => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            self::Reporter => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
            self::Stakeholder => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        };
    }

    /**
     * Check if user type is admin
     */
    public function isAdmin(): bool
    {
        return in_array($this, [self::GlobalAdministrator, self::Administrator]);
    }

    /**
     * Check if user type can report violations
     */
    public function canReport(): bool
    {
        return in_array($this, [self::Reporter, self::Security]);
    }

    /**
     * Check if user type needs a vehicle
     */
    public function needsVehicle(): bool
    {
        return in_array($this, [self::Student, self::Staff, self::Security, self::Stakeholder]);
    }

    /**
     * Get home route for user type
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::GlobalAdministrator, self::Administrator => 'dashboard',
            self::Reporter, self::Security => 'home',
            default => 'home',
        };
    }

    /**
     * Get all user types as array for select options
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($type) {
            return [$type->value => $type->label()];
        })->toArray();
    }
}
