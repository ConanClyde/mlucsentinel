<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StickerCounter extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'color',
        'count',
        'user_id',
    ];
}
