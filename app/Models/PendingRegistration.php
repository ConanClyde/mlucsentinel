<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PendingRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'user_type',
        'license_no',
        'license_image',
        'guardian_evidence',
        'reporter_role_id',
        'status',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by',
        'ip_address',
        'user_agent',
        'program_id',
        'student_id',
        'staff_id',
        'security_id',
        'stakeholder_type_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the full name attribute.
     */
    public function getFullNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Get the pending vehicles for this registration.
     */
    public function pendingVehicles(): HasMany
    {
        return $this->hasMany(PendingVehicle::class);
    }

    /**
     * Get the reviewer who processed this registration.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the reporter role associated with this registration.
     */
    public function reporterRole()
    {
        return $this->belongsTo(ReporterRole::class);
    }

    /**
     * Get the program associated with this registration.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the stakeholder type associated with this registration.
     */
    public function stakeholderType()
    {
        return $this->belongsTo(StakeholderType::class, 'stakeholder_type_id');
    }

    /**
     * Scope for pending registrations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved registrations.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected registrations.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if registration has vehicles.
     */
    public function getHasVehicleAttribute(): bool
    {
        return $this->pendingVehicles()->count() > 0;
    }

    /**
     * Get the first vehicle's plate number.
     */
    public function getLicensePlateAttribute(): ?string
    {
        $firstVehicle = $this->pendingVehicles()->first();

        return $firstVehicle?->plate_no;
    }

    /**
     * Get vehicle information string.
     */
    public function getVehicleInfoAttribute(): ?string
    {
        $vehicles = $this->pendingVehicles()->with('vehicleType')->get();
        if ($vehicles->isEmpty()) {
            return null;
        }

        return $vehicles->map(function ($vehicle) {
            $type = $vehicle->vehicleType?->name ?? 'Unknown';

            return $vehicle->plate_no ? "{$type} - {$vehicle->plate_no}" : $type;
        })->join(', ');
    }
}
