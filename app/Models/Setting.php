<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for application settings stored as key-value pairs.
 */
class Setting extends Model
{
    public $timestamps = true;

    protected $fillable = ['key','value'];

    /**
     * Retrieve a setting value by key with optional default.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}