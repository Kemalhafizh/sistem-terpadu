<?php

namespace App\Services\Inclusive;

use App\Models\IepDocument;
use Illuminate\Support\Collection;

class IepManagementService
{
    public function generateIepTemplate(int $studentId, int $caseManagerId): IepDocument
    {
        return IepDocument::create([
            'student_id' => $studentId,
            'case_manager_id' => $caseManagerId,
            'start_date' => now(),
            'review_date' => now()->addYear(),
            'diagnoses' => [],
            'present_level_of_performance' => '',
            'annual_goals' => [],
            'required_accommodations' => [],
            'status' => 'draft',
        ]);
    }

    public function trackGoalProgress(IepDocument $iep, array $progressUpdates): void
    {
        $goals = $iep->annual_goals;
        foreach ($progressUpdates as $index => $update) {
            if (isset($goals[$index])) {
                $goals[$index]['progress'] = $update;
            }
        }
        $iep->update(['annual_goals' => $goals]);
    }

    public function notifyUpcomingReviews(): int
    {
        $upcoming = IepDocument::where('status', 'active')
            ->where('review_date', '<=', now()->addDays(30))
            ->get();

        // logic to notify case managers
        return $upcoming->count();
    }

    public function compileAccommodationList(int $studentId): Collection
    {
        $iep = IepDocument::where('student_id', $studentId)
            ->where('status', 'active')
            ->first();

        return collect($iep ? $iep->required_accommodations : []);
    }
}
