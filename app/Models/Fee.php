<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'amount',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Get fee amount by name
     */
    public static function getAmount(string $name, float $default = 15.00): float
    {
        $fee = static::where('name', $name)->first();

        return $fee ? (float) $fee->amount : $default;
    }
}
