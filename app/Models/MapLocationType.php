<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MapLocationType extends Model
{
    protected $fillable = [
        'name',
        'default_color',
        'requires_polygon',
        'description',
        'is_active',
        'display_order',
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
            'requires_polygon' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function locations(): HasMany
    {
        return $this->hasMany(MapLocation::class, 'type_id');
    }

    public function activeLocations(): HasMany
    {
        return $this->locations()->where('is_active', true);
    }
}
