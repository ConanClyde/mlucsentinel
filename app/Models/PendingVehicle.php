<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingVehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'pending_registration_id',
        'type_id',
        'plate_no',
    ];

    /**
     * Get the pending registration that owns this vehicle.
     */
    public function pendingRegistration(): BelongsTo
    {
        return $this->belongsTo(PendingRegistration::class);
    }

    /**
     * Get the vehicle type.
     */
    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'type_id');
    }
}
