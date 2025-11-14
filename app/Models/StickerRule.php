<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StickerRule extends Model
{
    protected $table = 'sticker_rules';

    protected $fillable = [
        'student_expiration_years',
        'staff_expiration_years',
        'security_expiration_years',
        'stakeholder_expiration_years',
        'staff_color',
        'security_color',
        'student_map',
        'stakeholder_map',
        'palette',
    ];

    protected $casts = [
        'student_map' => 'array',
        'stakeholder_map' => 'array',
        'palette' => 'array',
        'student_expiration_years' => 'integer',
        'staff_expiration_years' => 'integer',
        'security_expiration_years' => 'integer',
        'stakeholder_expiration_years' => 'integer',
    ];

    public static function getSingleton(): self
    {
        $rules = static::query()->first();
        if (! $rules) {
            $rules = new static([
                'student_expiration_years' => 4,
                'staff_expiration_years' => 4,
                'security_expiration_years' => 4,
                'stakeholder_expiration_years' => 4,
                'staff_color' => 'maroon',
                'security_color' => 'maroon',
                'student_map' => [
                    '12' => 'blue',
                    '34' => 'green',
                    '56' => 'yellow',
                    '78' => 'pink',
                    '90' => 'orange',
                    'no_plate' => 'white',
                ],
                'stakeholder_map' => [
                    'Guardian' => 'white',
                    'Service Provider' => 'white',
                    'Visitor' => 'black',
                ],
                'palette' => [],
            ]);
            $rules->save();
        }

        return $rules;
    }
}
