<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileAppConfig extends Model
{
    use HasFactory;

    protected $table = 'mobile_app_configs';

    protected $fillable = [
        'app_name',
        'min_android_build',
        'latest_android_build',
        'min_ios_build',
        'latest_ios_build',
        'force_update',
        'maintenance_mode',
        'maintenance_message',
        'play_store_url',
        'app_store_url',
        'active_features',
    ];

    protected $casts = [
        'min_android_build' => 'integer',
        'latest_android_build' => 'integer',
        'min_ios_build' => 'integer',
        'latest_ios_build' => 'integer',
        'force_update' => 'boolean',
        'maintenance_mode' => 'boolean',
        'active_features' => 'array',
    ];
}
