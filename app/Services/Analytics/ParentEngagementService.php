<?php

namespace App\Services\Analytics;

use App\Models\EngagementMetric;

class ParentEngagementService
{
    public function calculateEngagementScore(int $parentId, int $logins, int $replies, int $events): EngagementMetric
    {
        $score = min(100.0, ($logins * 2) + ($replies * 5) + ($events * 20));

        return EngagementMetric::create([
            'parent_user_id' => $parentId,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'login_count' => $logins,
            'message_replies' => $replies,
            'event_attendance' => $events,
            'engagement_score' => $score,
        ]);
    }
}
