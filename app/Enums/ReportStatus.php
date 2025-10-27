<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    /**
     * Get badge color for UI
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Approved => 'green',
            self::Rejected => 'red',
        };
    }

    /**
     * Get Tailwind CSS classes for badge
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            self::Approved => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            self::Rejected => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        };
    }

    /**
     * Get icon for status
     */
    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'clock',
            self::Approved => 'check-circle',
            self::Rejected => 'x-circle',
        };
    }

    /**
     * Check if status can be changed to another status
     */
    public function canChangeTo(self $newStatus): bool
    {
        return match ($this) {
            self::Pending => in_array($newStatus, [self::Approved, self::Rejected]),
            self::Approved => $newStatus === self::Rejected,
            self::Rejected => $newStatus === self::Approved,
        };
    }

    /**
     * Get all statuses as array for select options
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($status) {
            return [$status->value => $status->label()];
        })->toArray();
    }
}
