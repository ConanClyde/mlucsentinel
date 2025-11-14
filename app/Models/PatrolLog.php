<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatrolLog extends Model
{
    protected $fillable = [
        'security_user_id',
        'map_location_id',
        'checked_in_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
        ];
    }

    /**
     * Get the security guard who made this patrol check-in
     */
    public function securityUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'security_user_id');
    }

    /**
     * Get the map location for this patrol check-in
     */
    public function mapLocation(): BelongsTo
    {
        return $this->belongsTo(MapLocation::class, 'map_location_id');
    }

    /**
     * Scope to get logs for a specific security guard
     */
    public function scopeByGuard($query, int $securityUserId)
    {
        return $query->where('security_user_id', $securityUserId);
    }

    /**
     * Scope to get logs for a specific location
     */
    public function scopeByLocation($query, int $mapLocationId)
    {
        return $query->where('map_location_id', $mapLocationId);
    }

    /**
     * Scope to get logs within a date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('checked_in_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get recent logs
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('checked_in_at', '>=', now()->subHours($hours));
    }
}
