<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type'];

    public static function get($key, $default = null)
    {
        return \Illuminate\Support\Facades\Cache::rememberForever("setting_{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set($key, $value, $group = 'general', $type = 'text')
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type]
        );

        \Illuminate\Support\Facades\Cache::forget("setting_{$key}");
        \Illuminate\Support\Facades\Cache::forget('settings_all');

        return $setting;
    }
}
