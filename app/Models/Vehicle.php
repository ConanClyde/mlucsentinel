<?php

namespace App\Models;

use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type_id',
        'plate_no',
        'color',
        'number',
        'sticker',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the vehicle.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vehicle type that the vehicle belongs to.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'type_id');
    }

    /**
     * Alias for type relationship to match view expectations
     */
    public function vehicleType(): BelongsTo
    {
        return $this->type();
    }

    /**
     * Get the sticker color based on user type and plate number.
     */
    public function getStickerColorAttribute(): string
    {
        $user = $this->user;

        if (! $user) {
            return 'blue'; // default fallback
        }

        // Security & Staff
        if (in_array($user->user_type, [UserType::Security, UserType::Staff])) {
            return 'maroon';
        }

        // Stakeholders
        if ($user->user_type === UserType::Stakeholder) {
            // This would need to be implemented based on stakeholder type
            // For now, return white as default
            return 'white';
        }

        // Students - based on plate number
        if ($user->user_type === UserType::Student) {
            // If no plate number (electric vehicle), return white
            if (! $this->plate_no) {
                return 'white';
            }

            // Otherwise, determine color based on last digit of plate number
            $lastDigit = substr($this->plate_no, -1);

            return match ($lastDigit) {
                '1', '2' => 'blue',
                '3', '4' => 'green',
                '5', '6' => 'yellow',
                '7', '8' => 'pink',
                '9', '0' => 'orange',
                default => 'blue' // fallback
            };
        }

        return 'blue'; // default fallback
    }

    /**
     * Get the reports where this vehicle was the violator.
     */
    public function violatorReports()
    {
        return $this->hasMany(Report::class, 'violator_vehicle_id');
    }
}
