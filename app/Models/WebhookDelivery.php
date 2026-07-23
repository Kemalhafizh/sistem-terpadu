<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    use HasFactory;

    protected $table = 'webhook_deliveries';

    protected $fillable = [
        'webhook_subscription_id',
        'event_type',
        'payload',
        'signature',
        'status',
        'response_code',
        'response_body',
        'attempt_count',
    ];

    protected $casts = [
        'payload' => 'array',
        'response_code' => 'integer',
        'attempt_count' => 'integer',
    ];

    public function subscription()
    {
        return $this->belongsTo(WebhookSubscription::class, 'webhook_subscription_id');
    }
}
