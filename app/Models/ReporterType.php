<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporterType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the reporters for this type.
     */
    public function reporters()
    {
        return $this->hasMany(Reporter::class, 'type_id');
    }
}
