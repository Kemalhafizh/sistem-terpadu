<?php

namespace App\Services\Grc;

use App\Models\SopAcknowledgement;
use App\Models\SopDocument;
use App\Models\User;
use Illuminate\Support\Collection;

class SopGovernanceService
{
    /**
     * Publish and approve SOP document.
     */
    public function publish(int $sopId, int $approverId): SopDocument
    {
        $sop = SopDocument::findOrFail($sopId);
        $sop->status = 'published';
        $sop->approved_by = $approverId;
        $sop->save();

        return $sop;
    }

    /**
     * Archive old SOP version.
     */
    public function archive(int $sopId): SopDocument
    {
        $sop = SopDocument::findOrFail($sopId);
        $sop->status = 'archived';
        $sop->save();

        return $sop;
    }

    /**
     * Create new version update for existing SOP.
     */
    public function createVersion(int $sopId, array $data): SopDocument
    {
        $oldSop = SopDocument::findOrFail($sopId);

        return SopDocument::create([
            'title' => $data['title'] ?? $oldSop->title,
            'code' => $oldSop->code . '-V' . ($oldSop->version + 1),
            'category' => $oldSop->category,
            'content_html' => $data['content_html'] ?? $oldSop->content_html,
            'version' => $oldSop->version + 1,
            'status' => 'draft',
            'effective_date' => $data['effective_date'] ?? now()->toDateString(),
            'review_date' => $data['review_date'] ?? now()->addYear()->toDateString(),
            'author_id' => $data['author_id'] ?? $oldSop->author_id,
        ]);
    }

    /**
     * Digital acknowledgement signing by staff/user.
     */
    public function acknowledge(int $sopId, int $userId, ?string $ipAddress = null): SopAcknowledgement
    {
        return SopAcknowledgement::firstOrCreate(
            [
                'sop_document_id' => $sopId,
                'user_id' => $userId,
            ],
            [
                'acknowledged_at' => now(),
                'ip_address' => $ipAddress ?? '127.0.0.1',
            ]
        );
    }

    /**
     * Calculate compliance rate percentage for an SOP document across all active users.
     */
    public function getComplianceRate(int $sopId): float
    {
        $totalUsers = User::count();
        if ($totalUsers === 0) {
            return 0.0;
        }

        $ackCount = SopAcknowledgement::where('sop_document_id', $sopId)->count();

        return round(($ackCount / $totalUsers) * 100, 1);
    }

    /**
     * Get list of users who have NOT yet acknowledged the published SOP.
     */
    public function getUnacknowledgedUsers(int $sopId): Collection
    {
        $acknowledgedUserIds = SopAcknowledgement::where('sop_document_id', $sopId)->pluck('user_id');

        return User::whereNotIn('id', $acknowledgedUserIds)->get();
    }
}
