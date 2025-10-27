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
        'type_id',
        'expiration_date',
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
     * Get the reporter type.
     */
    public function reporterType(): BelongsTo
    {
        return $this->belongsTo(ReporterType::class, 'type_id');
    }
}
