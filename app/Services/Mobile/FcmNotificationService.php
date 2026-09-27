<?php

declare(strict_types=1);

namespace App\Services\Mobile;

use App\Jobs\SendFcmNotificationJob;
use App\Models\User;
use App\Models\UserDeviceToken;

class FcmNotificationService
{
    /**
     * Send push notification to a specific user.
     */
    public function sendToUser(int $userId, string $title, string $body, array $payload = [], string $channelId = 'channel_announcement'): void
    {
        SendFcmNotificationJob::dispatch([$userId], $title, $body, $payload, $channelId);
    }

    /**
     * Send push notification to users by role segment.
     */
    public function sendToRole(string $role, string $title, string $body, array $payload = [], string $channelId = 'channel_announcement'): int
    {
        $userIds = User::where('role', $role)->pluck('id')->toArray();
        if (! empty($userIds)) {
            SendFcmNotificationJob::dispatch($userIds, $title, $body, $payload, $channelId);
        }

        return count($userIds);
    }

    /**
     * Register or refresh a device token.
     */
    public function registerDeviceToken(User $user, string $token, string $deviceType = 'android', ?string $deviceName = null): UserDeviceToken
    {
        return UserDeviceToken::updateOrCreate(
            ['user_id' => $user->id, 'device_token' => $token],
            [
                'device_type' => $deviceType,
                'device_name' => $deviceName,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );
    }

    /**
     * Deactivate a device token.
     */
    public function deactivateDeviceToken(int $userId, string $token): bool
    {
        $device = UserDeviceToken::where('user_id', $userId)->where('device_token', $token)->first();
        if ($device) {
            $device->update(['is_active' => false]);

            return true;
        }

        return false;
    }
}
