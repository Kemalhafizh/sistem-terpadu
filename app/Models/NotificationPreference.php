<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_category',
        'channel_inapp',
        'channel_email',
        'channel_whatsapp',
        'channel_push',
    ];

    protected $casts = [
        'channel_inapp' => 'boolean',
        'channel_email' => 'boolean',
        'channel_whatsapp' => 'boolean',
        'channel_push' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
