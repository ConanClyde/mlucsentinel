<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapLocation extends Model
{
    protected $fillable = [
        'type_id',
        'name',
        'short_code',
        'description',
        'color',
        'vertices',
        'center_x',
        'center_y',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'type_id' => 'integer',
            'vertices' => 'array',
            'center_x' => 'decimal:4',
            'center_y' => 'decimal:4',
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(MapLocationType::class, 'type_id');
    }

    /**
     * Scope to get only active locations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get locations by type
     */
    public function scopeByType($query, $typeId)
    {
        return $query->where('type_id', $typeId);
    }

    /**
     * Scope to get locations ordered by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('created_at', 'desc');
    }
}
