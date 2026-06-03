<?php

namespace App\Http\Controllers;

use App\Models\ElearningAssignment;
use App\Models\ElearningSubmission;
use App\Models\Student;
use Illuminate\Http\Request;

class ElearningSecureController extends Controller
{
    /**
     * Display secure lock-down mode view.
     */
    public function secureView(ElearningAssignment $elearningAssignment)
    {
        $user = auth()->user();

        // Admin / Maintenance Bypass
        if (in_array($user->role, ['admin', 'superadmin', 'guru'])) {
            $student = Student::first(); // Grab first student for layout preview
            $submission = null;

            return view('elearning.assignment_secure', compact('elearningAssignment', 'student', 'submission'));
        }

        $student = $user->student;
        if (! $student) {
            abort(403, 'Akses ditolak.');
        }

        // Security check: must belong to the classroom
        if ($student->classroom_id !== $elearningAssignment->elearningClass->classroom_id) {
            abort(403, 'Anda tidak terdaftar di kelas virtual ini.');
        }

        // Must be in secure mode
        if (! $elearningAssignment->is_secure_mode) {
            return redirect()->route('elearning.assignment.show', $elearningAssignment->uuid)
                ->with('error', 'Tugas ini tidak menggunakan mode aman.');
        }

        // Check if already locked out
        $submission = $elearningAssignment->studentSubmission($student);
        if ($submission && $submission->status === 'locked_cheated') {
            return view('elearning.assignment_secure', compact('elearningAssignment', 'student', 'submission'));
        }

        return view('elearning.assignment_secure', compact('elearningAssignment', 'student', 'submission'));
    }

    /**
     * Log a lock-down violation via AJAX.
     */
    public function logViolation(Request $request, ElearningAssignment $elearningAssignment)
    {
        $user = auth()->user();

        // Admin bypass
        if (in_array($user->role, ['admin', 'superadmin'])) {
            return response()->json([
                'success' => true,
                'violation_count' => 1,
                'max_violations' => $elearningAssignment->max_violations,
                'locked' => false,
            ]);
        }

        $student = $user->student;
        if (! $student) {
            return response()->json(['error' => 'Student not found.'], 403);
        }

        // Get or create submission
        $submission = ElearningSubmission::firstOrCreate(
            [
                'elearning_assignment_id' => $elearningAssignment->id,
                'student_id' => $student->id,
            ],
            [
                'submitted_at' => now(),
                'status' => 'submitted',
                'violation_count' => 0,
            ]
        );

        // Check if already locked
        if ($submission->status === 'locked_cheated') {
            return response()->json([
                'success' => true,
                'violation_count' => $submission->violation_count,
                'max_violations' => $elearningAssignment->max_violations,
                'locked' => true,
            ]);
        }

        // Increment violations
        $newViolationCount = $submission->violation_count + 1;
        $locked = $newViolationCount >= $elearningAssignment->max_violations;

        $submission->update([
            'violation_count' => $newViolationCount,
            'status' => $locked ? 'locked_cheated' : 'submitted',
        ]);

        return response()->json([
            'success' => true,
            'violation_count' => $newViolationCount,
            'max_violations' => $elearningAssignment->max_violations,
            'locked' => $locked,
        ]);
    }
}
