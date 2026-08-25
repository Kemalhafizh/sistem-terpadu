<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDeviceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'device_token',
        'device_type',
        'device_name',
        'platform',
        'fcm_token',
        'apns_token',
        'os_version',
        'app_version',
        'badge_count',
        'is_active',
        'last_active_at',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_active_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
