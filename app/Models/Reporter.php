<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type_id',
        'expiration_date',
    ];

    protected $casts = [
        'expiration_date' => 'date',
    ];

    /**
     * Get the user associated with the reporter.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reporter type.
     */
    public function reporterType()
    {
        return $this->belongsTo(ReporterType::class, 'type_id');
    }
}
