<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reported_by',
        'violator_vehicle_id',
        'violator_sticker_number',
        'violation_type_id',
        'description',
        'location',
        'pin_x',
        'pin_y',
        'assigned_to',
        'assigned_to_user_type',
        'status',
        'reported_at',
        'evidence_image',
        'remarks',
        'updated_by',
        'status_updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'status_updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user who reported this violation.
     */
    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get the violator's vehicle.
     */
    public function violatorVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'violator_vehicle_id');
    }

    /**
     * Get the violation type.
     */
    public function violationType(): BelongsTo
    {
        return $this->belongsTo(ViolationType::class, 'violation_type_id');
    }

    /**
     * Get the user this report is assigned to.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who last updated this report.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Automatically assign report to appropriate administrator based on violator type.
     * Student violators -> SAS Admin
     * Other user types -> Chancellor Admin
     */
    public static function getAutoAssignedAdmin(string $violatorUserType): ?array
    {
        // Determine which admin role to assign based on violator type
        $targetRole = $violatorUserType === 'student'
            ? 'SAS (Student Affairs & Services)'
            : 'Chancellor';

        // Find an administrator with the target role
        $admin = User::where('user_type', 'administrator')
            ->whereHas('administrator.adminRole', function ($query) use ($targetRole) {
                $query->where('name', $targetRole);
            })
            ->first();

        if ($admin) {
            return [
                'assigned_to' => $admin->id,
                'assigned_to_user_type' => 'administrator',
            ];
        }

        return null;
    }
}
