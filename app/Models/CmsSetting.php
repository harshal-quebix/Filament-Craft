<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsSetting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type'];

    /**
     * Get setting with automatic type casting
     */
    public static function get($key, $group = 'general', $default = null)
    {
        $setting = self::where('key', $key)->where('group', $group)->first();
        if (!$setting) return $default;

        return match($setting->type) {
            'json' => json_decode($setting->value, true) ?? $default,
            'color', 'text', 'image' => $setting->value ?? $default,
            default => $setting->value ?? $default,
        };
    }

    /**
     * Set setting with type
     */
    public static function set($key, $value, $group = 'general', $type = 'text')
    {
        if (is_array($value)) {
            $value = json_encode($value);
            $type = 'json';
        }

        return self::updateOrCreate(
            ['key' => $key, 'group' => $group],
            ['value' => $value, 'type' => $type]
        );
    }

    /**
     * Get all settings by group
     */
    public static function getGroup($group)
    {
        $settings = self::where('group', $group)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = match($setting->type) {
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        }

        return $result;
    }
}
