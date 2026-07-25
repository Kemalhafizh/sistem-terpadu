<?php

namespace App\Services\Communication;

use App\Jobs\DispatchNotificationChannelJob;
use App\Models\NotificationLog;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NotificationDispatchService
{
    /**
     * Categories of events supported in SISTA.
     */
    public const CATEGORIES = ['academic', 'finance', 'system', 'discipline', 'hr'];

    /**
     * Dispatch notification to a single user based on their multi-channel preferences.
     */
    public function dispatch(
        int $userId,
        string $eventType,
        string $title,
        string $body,
        array $metadata = [],
        ?string $actionUrl = null
    ): array {
        $category = $this->determineCategory($eventType);
        $userPref = $this->getCategoryPreference($userId, $category);

        $channelsToDispatch = [];

        if ($userPref['channel_inapp']) {
            $channelsToDispatch[] = 'inapp';
        }
        if ($userPref['channel_email']) {
            $channelsToDispatch[] = 'email';
        }
        if ($userPref['channel_whatsapp']) {
            $channelsToDispatch[] = 'whatsapp';
        }
        if ($userPref['channel_push']) {
            $channelsToDispatch[] = 'push';
        }

        // Always fallback to inapp if no channels enabled
        if (empty($channelsToDispatch)) {
            $channelsToDispatch[] = 'inapp';
        }

        $createdLogs = [];

        foreach ($channelsToDispatch as $channel) {
            $log = NotificationLog::create([
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'channel' => $channel,
                'event_type' => $eventType,
                'event_category' => $category,
                'action_url' => $actionUrl,
                'metadata' => $metadata,
            ]);

            if (app()->environment('testing')) {
                // Synchronous in testing
                $this->deliverChannel($log);
            } else {
                DispatchNotificationChannelJob::dispatch($log);
            }

            $createdLogs[] = $log;
        }

        return $createdLogs;
    }

    /**
     * Bulk dispatch notification to multiple users.
     */
    public function dispatchBulk(
        array $userIds,
        string $eventType,
        string $title,
        string $body,
        array $metadata = [],
        ?string $actionUrl = null
    ): int {
        $count = 0;

        foreach (array_unique($userIds) as $userId) {
            $this->dispatch($userId, $eventType, $title, $body, $metadata, $actionUrl);
            $count++;
        }

        return $count;
    }

    /**
     * Deliver specific notification channel message.
     */
    public function deliverChannel(NotificationLog $log): bool
    {
        try {
            switch ($log->channel) {
                case 'email':
                    // Trigger email notification pipeline
                    Log::info("Email notification sent to User #{$log->user_id}: [{$log->title}]");
                    break;
                case 'whatsapp':
                    // Trigger Fonnte WhatsApp integration
                    Log::info("WhatsApp notification sent to User #{$log->user_id}: [{$log->title}]");
                    break;
                case 'push':
                    // Trigger WebPush / Firebase push notification
                    Log::info("Push notification sent to User #{$log->user_id}: [{$log->title}]");
                    break;
                case 'inapp':
                default:
                    // Stored directly in DB log
                    break;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to deliver notification #{$log->id} via channel {$log->channel}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark single notification as read.
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $log = NotificationLog::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if (! $log) {
            return false;
        }

        if (! $log->isRead()) {
            $log->read_at = now();
            $log->save();
        }

        return true;
    }

    /**
     * Mark all unread notifications for a user as read.
     */
    public function markAllRead(int $userId): int
    {
        return NotificationLog::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get user preferences for all categories.
     */
    public function getUserPreferences(int $userId): Collection
    {
        $existing = NotificationPreference::where('user_id', $userId)->get()->keyBy('event_category');
        $preferences = collect();

        foreach (self::CATEGORIES as $category) {
            if ($existing->has($category)) {
                $preferences->push($existing->get($category));
            } else {
                $preferences->push(new NotificationPreference([
                    'user_id' => $userId,
                    'event_category' => $category,
                    'channel_inapp' => true,
                    'channel_email' => true,
                    'channel_whatsapp' => false,
                    'channel_push' => false,
                ]));
            }
        }

        return $preferences;
    }

    /**
     * Update user notification channel preferences.
     */
    public function updatePreferences(int $userId, array $preferencesData): void
    {
        foreach (self::CATEGORIES as $category) {
            $catData = $preferencesData[$category] ?? [];

            NotificationPreference::updateOrCreate(
                [
                    'user_id' => $userId,
                    'event_category' => $category,
                ],
                [
                    'channel_inapp' => ! empty($catData['inapp']),
                    'channel_email' => ! empty($catData['email']),
                    'channel_whatsapp' => ! empty($catData['whatsapp']),
                    'channel_push' => ! empty($catData['push']),
                ]
            );
        }
    }

    /**
     * Get unread notification count for navbar badge.
     */
    public function getUnreadCount(int $userId): int
    {
        return NotificationLog::where('user_id', $userId)
            ->where('channel', 'inapp')
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get in-app notification logs for user inbox.
     */
    public function getUserInbox(int $userId, ?string $category = null, bool $unreadOnly = false, int $perPage = 20)
    {
        $query = NotificationLog::where('user_id', $userId)
            ->where('channel', 'inapp')
            ->orderBy('created_at', 'desc');

        if ($category && in_array($category, self::CATEGORIES)) {
            $query->where('event_category', $category);
        }

        if ($unreadOnly) {
            $query->whereNull('read_at');
        }

        return $query->paginate($perPage);
    }

    /**
     * Helper to get single category preference.
     */
    private function getCategoryPreference(int $userId, string $category): array
    {
        $pref = NotificationPreference::where('user_id', $userId)
            ->where('event_category', $category)
            ->first();

        if (! $pref) {
            return [
                'channel_inapp' => true,
                'channel_email' => true,
                'channel_whatsapp' => false,
                'channel_push' => false,
            ];
        }

        return [
            'channel_inapp' => $pref->channel_inapp,
            'channel_email' => $pref->channel_email,
            'channel_whatsapp' => $pref->channel_whatsapp,
            'channel_push' => $pref->channel_push,
        ];
    }

    /**
     * Helper to infer event category from event type string.
     */
    private function determineCategory(string $eventType): string
    {
        $parts = explode('.', $eventType);
        $prefix = strtolower($parts[0] ?? 'system');

        if (in_array($prefix, self::CATEGORIES)) {
            return $prefix;
        }

        return 'system';
    }
}
