<?php

namespace App\Enums;

enum StickerColor: string
{
    case Blue = 'blue';
    case Green = 'green';
    case Yellow = 'yellow';
    case Pink = 'pink';
    case Orange = 'orange';
    case Maroon = 'maroon';
    case White = 'white';
    case Black = 'black';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::Blue => 'Blue',
            self::Green => 'Green',
            self::Yellow => 'Yellow',
            self::Pink => 'Pink',
            self::Orange => 'Orange',
            self::Maroon => 'Maroon',
            self::White => 'White',
            self::Black => 'Black',
        };
    }

    /**
     * Get hex color code
     */
    public function hex(): string
    {
        return match ($this) {
            self::Blue => '#007BFF',
            self::Green => '#28A745',
            self::Yellow => '#FFC107',
            self::Pink => '#E83E8C',
            self::Orange => '#FD7E14',
            self::Maroon => '#800000',
            self::White => '#FFFFFF',
            self::Black => '#000000',
        };
    }

    /**
     * Get Tailwind CSS background class
     */
    public function bgClass(): string
    {
        return match ($this) {
            self::Blue => 'bg-blue-500',
            self::Green => 'bg-green-500',
            self::Yellow => 'bg-yellow-400',
            self::Pink => 'bg-pink-500',
            self::Orange => 'bg-orange-500',
            self::Maroon => 'bg-red-900',
            self::White => 'bg-white border border-gray-300',
            self::Black => 'bg-black',
        };
    }

    /**
     * Get text color class for sticker
     */
    public function textClass(): string
    {
        return match ($this) {
            self::White => 'text-gray-900',
            default => 'text-white',
        };
    }

    /**
     * Get appropriate color for user type and plate number
     */
    public static function forUser(string $userType, ?string $stakeholderType = null, ?string $plateNumber = null): self
    {
        // Security & Staff
        if (in_array($userType, ['security', 'staff'])) {
            return self::Maroon;
        }

        // Stakeholders
        if ($userType === 'stakeholder') {
            return match ($stakeholderType) {
                'Visitor' => self::Black,
                'Guardian', 'Service Provider' => self::White,
                default => self::White,
            };
        }

        // Students - based on plate number
        if ($userType === 'student') {
            // If no plate number (electric vehicle), return white
            if (! $plateNumber) {
                return self::White;
            }

            // Determine color based on last digit of plate number
            $lastDigit = substr($plateNumber, -1);

            return match ($lastDigit) {
                '1', '2' => self::Blue,
                '3', '4' => self::Green,
                '5', '6' => self::Yellow,
                '7', '8' => self::Pink,
                '9', '0' => self::Orange,
                default => self::Blue,
            };
        }

        return self::Blue; // default fallback
    }

    /**
     * Get all colors as array for select options
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($color) {
            return [$color->value => $color->label()];
        })->toArray();
    }
}
