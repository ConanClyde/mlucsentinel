<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reporter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reporter_role_id',
        'expiration_date',
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
            'expiration_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user associated with the reporter.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reporter role.
     */
    public function reporterRole(): BelongsTo
    {
        return $this->belongsTo(ReporterRole::class);
    }

    /**
     * Check if this reporter can report a specific user.
     */
    public function canReportUser(User $user): bool
    {
        if (! $this->reporterRole) {
            return false;
        }

        return $this->reporterRole->canReport($user->user_type->value);
    }

    /**
     * Check if this reporter can report a specific user type.
     */
    public function canReportUserType(string $userType): bool
    {
        if (! $this->reporterRole) {
            return false;
        }

        return $this->reporterRole->canReport($userType);
    }

    /**
     * Check if this reporter can only report students.
     */
    public function canOnlyReportStudents(): bool
    {
        if (! $this->reporterRole) {
            return false;
        }

        $allowedTypes = $this->reporterRole->getAllowedUserTypes();

        return count($allowedTypes) === 1 && in_array('student', $allowedTypes);
    }
}
