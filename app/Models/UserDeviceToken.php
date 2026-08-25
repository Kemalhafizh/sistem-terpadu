<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDeviceToken extends Model
{
    use HasFactory;

    protected $table = 'user_device_tokens';

    protected $fillable = [
        'user_id',
        'device_id',
        'device_name',
        'platform',
        'fcm_token',
        'apns_token',
        'os_version',
        'app_version',
        'is_active',
        'badge_count',
        'last_active_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'badge_count' => 'integer',
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
