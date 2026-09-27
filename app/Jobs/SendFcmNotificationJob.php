<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\UserDeviceToken;
use App\Services\Mobile\FcmHttpV1Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Push one notification to every active device of the given users through
 * FCM HTTP v1. Without FCM credentials it only logs (the previous behaviour),
 * so local/dev environments keep working.
 */
class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Retry rounds for devices that hit a quota/server error (429/5xx). */
    public const MAX_ATTEMPTS = 3;

    /**
     * Channel ids the Android app creates (NotificationChannelType). Anything
     * else would land in FCM's generic fallback channel.
     */
    public const ANDROID_CHANNELS = ['attendance_alerts', 'academic_updates', 'financial_reminders', 'emergency_broadcast', 'general_info'];

    /** Older server-side channel names mapped onto the Android ones. */
    private const LEGACY_CHANNELS = [
        'channel_announcement' => 'general_info',
        'channel_class_session' => 'attendance_alerts',
    ];

    /**
     * @param  array<int, int>  $userIds
     * @param  array<string, mixed>  $payload
     * @param  array<int, int>|null  $deviceIds  when set, only these user_device_tokens rows (retry rounds)
     */
    public function __construct(
        public array $userIds,
        public string $title,
        public string $body,
        public array $payload = [],
        public ?string $channelId = 'channel_announcement',
        public ?array $deviceIds = null,
        public int $attempt = 1,
    ) {}

    public function handle(FcmHttpV1Client $fcm): void
    {
        $devices = UserDeviceToken::whereIn('user_id', $this->userIds)
            ->where('is_active', true)
            ->when($this->deviceIds !== null, fn ($q) => $q->whereIn('id', $this->deviceIds))
            ->get(['id', 'user_id', 'fcm_token', 'device_token']);

        // The app registers through PushNotificationService, which stores the
        // token in fcm_token; device_token is the older column. This job used
        // to read only device_token, so tokens from real phones were never used.
        $targets = $devices
            ->map(fn (UserDeviceToken $d) => ['id' => $d->id, 'token' => (string) ($d->fcm_token ?: $d->device_token)])
            ->filter(fn (array $t) => $t['token'] !== '')
            ->values();

        if ($targets->isEmpty()) {
            Log::info('SendFcmNotificationJob: no active device tokens for users '.implode(',', $this->userIds));

            return;
        }

        if (! $fcm->isConfigured()) {
            Log::info('SendFcmNotificationJob: FCM not configured (services.fcm), logged only. '
                ."{$targets->count()} device(s), title: {$this->title}");

            return;
        }

        $channel = self::androidChannel($this->channelId);
        $data = array_map('strval', array_filter(array_merge($this->payload, [
            'title' => $this->title,
            'body' => $this->body,
            'channel' => $channel,
            'sent_at' => now()->toIso8601String(),
        ]), fn ($value) => $value !== null && ! is_array($value)));

        $retry = [];
        $counts = array_fill_keys([FcmHttpV1Client::RESULT_SENT, FcmHttpV1Client::RESULT_INVALID_TOKEN, FcmHttpV1Client::RESULT_RETRY, FcmHttpV1Client::RESULT_FAILED], 0);

        foreach ($targets as $target) {
            $result = $fcm->send($target['token'], $this->title, $this->body, $data, $channel);
            $counts[$result]++;

            if ($result === FcmHttpV1Client::RESULT_INVALID_TOKEN) {
                UserDeviceToken::whereKey($target['id'])->update(['is_active' => false]);
            } elseif ($result === FcmHttpV1Client::RESULT_RETRY) {
                $retry[] = $target['id'];
            } elseif ($result === FcmHttpV1Client::RESULT_SENT) {
                UserDeviceToken::whereKey($target['id'])->update(['last_used_at' => now()]);
            }
        }

        // Retry only the devices that hit a transient error, with backoff,
        // instead of failing the whole job and re-sending to everyone.
        if ($retry !== [] && $this->attempt < self::MAX_ATTEMPTS) {
            self::dispatch($this->userIds, $this->title, $this->body, $this->payload, $this->channelId, $retry, $this->attempt + 1)
                ->delay(now()->addSeconds(60 * $this->attempt));
        }

        Log::info('SendFcmNotificationJob: '.json_encode($counts)." (attempt {$this->attempt}) title: {$this->title}");
    }

    public static function androidChannel(?string $channelId): string
    {
        if (in_array($channelId, self::ANDROID_CHANNELS, true)) {
            return $channelId;
        }

        return self::LEGACY_CHANNELS[$channelId] ?? 'general_info';
    }
}
