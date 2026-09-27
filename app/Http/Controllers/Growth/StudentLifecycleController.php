<?php

namespace App\Http\Controllers\Growth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Growth\StudentLifecycleService;
use Illuminate\Http\Request;

class StudentLifecycleController extends Controller
{
    public function timeline(int $studentId, StudentLifecycleService $service)
    {
        $student = User::where('role', 'student')->findOrFail($studentId);
        $timelineData = $service->getStudentTimeline($studentId);

        return view('lifecycle.student-timeline', compact('student', 'timelineData'));
    }

    public function storeEvent(int $studentId, Request $request, StudentLifecycleService $service)
    {
        $validated = $request->validate([
            'event_type' => 'required|in:prospective,applied,accepted,enrolled,promoted,retained,transferred_in,transferred_out,suspended,graduated,dropped_out',
            'to_status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $service->recordEvent(
            $studentId,
            $validated['event_type'],
            $validated['to_status'],
            auth()->id(),
            null,
            null,
            $validated['notes'] ?? null
        );

        return back()->with('success', 'Lifecycle Milestone Event berhasil dicatat!');
    }

    public function retentionDashboard(StudentLifecycleService $service)
    {
        $retention = $service->calculateCohortRetention();

        return view('lifecycle.retention-dashboard', compact('retention'));
    }

    public function atRiskStudents()
    {
        $students = User::where('role', 'student')
            ->whereHas('wellbeingResponses', function ($q) {
                $q->whereIn('risk_level', ['high', 'critical']);
            })->get();

        return view('lifecycle.at-risk', compact('students'));
    }
}
