<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'requires_plate',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requires_plate' => 'boolean',
        ];
    }

    /**
     * Get the vehicles for the vehicle type.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'type_id');
    }
}
