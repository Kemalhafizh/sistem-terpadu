<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\UserDeviceToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $userIds,
        public string $title,
        public string $body,
        public array $payload = [],
        public ?string $channelId = 'channel_announcement'
    ) {}

    public function handle(): void
    {
        $tokens = UserDeviceToken::whereIn('user_id', $this->userIds)
            ->where('is_active', true)
            ->pluck('device_token')
            ->toArray();

        if (empty($tokens)) {
            Log::info("SendFcmNotificationJob: No active device tokens found for users: " . implode(',', $this->userIds));
            return;
        }

        // Mock FCM dispatch payload formatting
        $fcmPayload = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $this->title,
                'body' => $this->body,
                'android_channel_id' => $this->channelId,
                'sound' => 'default',
            ],
            'data' => array_merge($this->payload, [
                'timestamp' => now()->toIso8601String(),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]),
        ];

        Log::info("SendFcmNotificationJob: Dispatched push notification to " . count($tokens) . " devices. Title: {$this->title}");
    }
}
