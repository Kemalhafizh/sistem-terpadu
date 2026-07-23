<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookSubscription extends Model
{
    use HasFactory;

    protected $table = 'webhook_subscriptions';

    protected $fillable = [
        'name',
        'target_url',
        'secret_key',
        'events',
        'is_active',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
    ];

    public function deliveries()
    {
        return $this->hasMany(WebhookDelivery::class);
    }
}
