<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StakeholderType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'evidence_required',
    ];

    protected $casts = [
        'evidence_required' => 'boolean',
    ];

    public function stakeholders(): HasMany
    {
        return $this->hasMany(Stakeholder::class, 'type_id');
    }
}
