<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminRole extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'can_register_users',
        'can_edit_users',
        'can_delete_users',
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
            'can_register_users' => 'boolean',
            'can_edit_users' => 'boolean',
            'can_delete_users' => 'boolean',
        ];
    }

    /**
     * Get the administrators associated with this role.
     */
    public function administrators(): HasMany
    {
        return $this->hasMany(Administrator::class, 'role_id');
    }

    /**
     * Get the privileges associated with this role.
     */
    public function privileges(): BelongsToMany
    {
        return $this->belongsToMany(Privilege::class, 'admin_role_privileges');
    }

    /**
     * Check if this role has a specific privilege.
     */
    public function hasPrivilege(string $privilegeName): bool
    {
        $cacheKey = "admin_role_{$this->id}_privilege_{$privilegeName}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($privilegeName) {
            return $this->privileges()->where('name', $privilegeName)->exists();
        });
    }

    /**
     * Check if this role has any of the given privileges.
     */
    public function hasAnyPrivilege(array $privilegeNames): bool
    {
        foreach ($privilegeNames as $privilegeName) {
            if ($this->hasPrivilege($privilegeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if this role has all of the given privileges.
     */
    public function hasAllPrivileges(array $privilegeNames): bool
    {
        foreach ($privilegeNames as $privilegeName) {
            if (! $this->hasPrivilege($privilegeName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get violator user types this role wants to receive report notifications for.
     *
     * @return array<int, string>
     */
    public function getReportTargets(): array
    {
        return DB::table('admin_role_report_targets')
            ->where('admin_role_id', $this->id)
            ->pluck('user_type')
            ->toArray();
    }

    /**
     * Sync the violator user types for report notifications.
     *
     * @param  array<int, string>  $userTypes
     */
    public function syncReportTargets(array $userTypes): void
    {
        DB::table('admin_role_report_targets')
            ->where('admin_role_id', $this->id)
            ->delete();

        $rows = collect($userTypes)->unique()->values()->map(function ($type) {
            return [
                'admin_role_id' => $this->id,
                'user_type' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->all();

        if (! empty($rows)) {
            DB::table('admin_role_report_targets')->insert($rows);
        }
    }

    /**
     * Determine if this role should receive notifications for the given violator user type.
     */
    public function wantsReportFor(string $userType): bool
    {
        return DB::table('admin_role_report_targets')
            ->where('admin_role_id', $this->id)
            ->where('user_type', $userType)
            ->exists();
    }
}
