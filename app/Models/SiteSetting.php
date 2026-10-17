<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $guarded = [];

    public static function getVal(string $key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }
}
