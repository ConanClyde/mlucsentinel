<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ReporterRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'default_expiration_years',
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
     * Get the reporters with this role.
     */
    public function reporters(): HasMany
    {
        return $this->hasMany(Reporter::class);
    }

    /**
     * Get the allowed user types for this role.
     */
    public function getAllowedUserTypes(): array
    {
        return DB::table('reporter_role_user_type')
            ->where('reporter_role_id', $this->id)
            ->pluck('user_type')
            ->toArray();
    }

    /**
     * Check if this role can report a specific user type.
     */
    public function canReport(string $userType): bool
    {
        return DB::table('reporter_role_user_type')
            ->where('reporter_role_id', $this->id)
            ->where('user_type', $userType)
            ->exists();
    }

    /**
     * Sync the allowed user types for this role.
     */
    public function syncUserTypes(array $userTypes): void
    {
        // Delete existing
        DB::table('reporter_role_user_type')
            ->where('reporter_role_id', $this->id)
            ->delete();

        // Insert new
        $data = collect($userTypes)->map(function ($userType) {
            return [
                'reporter_role_id' => $this->id,
                'user_type' => $userType,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        if (! empty($data)) {
            DB::table('reporter_role_user_type')->insert($data);
        }
    }

    /**
     * Scope a query to only include active roles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
