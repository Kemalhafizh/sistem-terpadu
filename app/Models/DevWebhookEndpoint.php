<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevWebhookEndpoint extends Model
{
    use HasFactory;

    protected $table = 'dev_webhook_endpoints';

    protected $fillable = [
        'user_id',
        'target_url',
        'event_types',
        'secret',
        'is_active',
    ];

    protected $casts = [
        'event_types' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
