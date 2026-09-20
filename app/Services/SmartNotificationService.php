<?php

namespace App\Services;

use App\Models\NotificationPreference;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class SmartNotificationService
{
    /**
     * Classify notification priority.
     */
    public function classifyPriority(string $type): string
    {
        return match (strtolower($type)) {
            'emergency', 'sos', 'cbt_security', 'crisis' => 'critical',
            'announcement_headmaster', 'rapor_ready', 'payment_overdue' => 'high',
            'assignment_new', 'exam_reminder', 'library_due' => 'medium',
            default => 'low',
        };
    }

    /**
     * Check if quiet hours are currently in effect.
     */
    public function isQuietHoursActive(?NotificationPreference $pref, ?Carbon $now = null): bool
    {
        if (!$pref || empty($pref->quiet_hours) || !($pref->quiet_hours['enabled'] ?? false)) {
            return false;
        }

        $now = $now ?: Carbon::now('Asia/Jakarta');
        $currentTime = $now->format('H:i');
        $start = $pref->quiet_hours['start'] ?? '22:00';
        $end = $pref->quiet_hours['end'] ?? '04:30';

        if ($start > $end) {
            // Spans over midnight
            return $currentTime >= $start || $currentTime <= $end;
        }

        return $currentTime >= $start && $currentTime <= $end;
    }

    /**
     * Deliver notification according to preferences.
     */
    public function deliver(int $userId, array $payload): array
    {
        $priority = $this->classifyPriority($payload['type'] ?? 'info');
        $pref = NotificationPreference::where('user_id', $userId)->first();

        // 1. Critical bypasses all restrictions
        if ($priority === 'critical') {
            return [
                'delivered' => true,
                'mode' => 'instant_critical',
                'payload' => $payload,
            ];
        }

        // 2. Check Quiet Hours
        if ($this->isQuietHoursActive($pref)) {
            $this->queueToDigest($userId, $payload);
            return [
                'delivered' => false,
                'mode' => 'queued_quiet_hours',
                'reason' => 'Quiet hours active. Queued for morning release.',
            ];
        }

        // 3. Check Digest Mode
        if ($pref && $pref->digest_mode && in_array($priority, ['medium', 'low'])) {
            $this->queueToDigest($userId, $payload);
            return [
                'delivered' => false,
                'mode' => 'queued_digest',
                'reason' => 'User is in digest mode. Queued for batch delivery.',
            ];
        }

        return [
            'delivered' => true,
            'mode' => 'instant',
            'payload' => $payload,
        ];
    }

    /**
     * Queue notification for digest.
     */
    protected function queueToDigest(int $userId, array $payload): void
    {
        $queue = Cache::get("user_{$userId}_digest_queue", []);
        $queue[] = array_merge($payload, ['queued_at' => Carbon::now('Asia/Jakarta')->toIso8601String()]);
        Cache::put("user_{$userId}_digest_queue", $queue, 86400 * 3);
    }

    /**
     * Build digest summary for user.
     */
    public function buildDigest(int $userId): array
    {
        $queue = Cache::get("user_{$userId}_digest_queue", []);
        if (empty($queue)) {
            return [
                'has_digest' => false,
                'count' => 0,
                'summary' => 'Tidak ada notifikasi yang tertunda.',
                'items' => [],
            ];
        }

        $grouped = $this->groupNotifications($queue);

        return [
            'has_digest' => true,
            'count' => count($queue),
            'summary' => "Anda memiliki " . count($queue) . " pembaruan penting sekolah hari ini.",
            'grouped' => $grouped,
            'items' => $queue,
        ];
    }

    /**
     * Group notification items to avoid spamming.
     */
    public function groupNotifications(array $items): array
    {
        $groups = [];
        foreach ($items as $item) {
            $channel = $item['channel'] ?? 'umum';
            if (!isset($groups[$channel])) {
                $groups[$channel] = [];
            }
            $groups[$channel][] = $item;
        }
        return $groups;
    }

    /**
     * Attach actionable deep links.
     */
    public function getActionableNotifications(int $userId): array
    {
        $notifs = Cache::get("user_{$userId}_notifications", [
            [
                'id' => '1',
                'title' => 'Tugas Matematika Tingkat Lanjut',
                'body' => 'Batas pengumpulan tugas Trigonometri Analitik pukul 15:00 WIB.',
                'type' => 'academic',
                'channel' => 'academic',
                'deep_link' => 'elearning_class_list',
                'timestamp' => '1 jam yang lalu',
                'is_read' => false,
            ],
            [
                'id' => '2',
                'title' => 'Tagihan SPP Bulan September 2026',
                'body' => 'Pembayaran SPP dapat diselesaikan melalui Virtual Account BSI / BNI.',
                'type' => 'financial',
                'channel' => 'financial',
                'deep_link' => 'billing',
                'timestamp' => '3 jam yang lalu',
                'is_read' => true,
            ],
        ]);

        return $notifs;
    }
}
