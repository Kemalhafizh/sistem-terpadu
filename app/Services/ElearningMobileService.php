<?php

namespace App\Services;

use App\Models\ElearningAssignment;
use App\Models\ElearningClass;
use App\Models\ElearningMaterial;
use App\Models\ElearningSubmission;
use App\Models\Student;
use Illuminate\Http\UploadedFile;

class ElearningMobileService
{
    public function getStudentClasses(int $studentId)
    {
        return ElearningClass::with(['subject', 'classroom', 'teacher'])
            ->withCount(['materials', 'assignments'])
            ->where('is_archived', false)
            ->get();
    }

    public function getTeacherClasses(int $teacherId)
    {
        return ElearningClass::with(['subject', 'classroom'])
            ->withCount(['materials', 'assignments'])
            ->where('teacher_id', $teacherId)
            ->where('is_archived', false)
            ->get();
    }

    public function getClassMaterials(int $classId)
    {
        return ElearningMaterial::where('elearning_class_id', $classId)
            ->latest()
            ->get();
    }

    public function getClassAssignments(int $classId, ?int $studentId = null)
    {
        $assignments = ElearningAssignment::with('elearningClass.subject')
            ->where('elearning_class_id', $classId)
            ->latest()
            ->get();

        if ($studentId) {
            $assignments->map(function ($assignment) use ($studentId) {
                $sub = ElearningSubmission::where('elearning_assignment_id', $assignment->id)
                    ->where('student_id', $studentId)
                    ->first();
                $assignment->student_submission = $sub;
                $assignment->is_submitted = $sub !== null;

                return $assignment;
            });
        }

        return $assignments;
    }

    public function submitAssignment(int $assignmentId, int $studentId, ?string $content, ?UploadedFile $file): ElearningSubmission
    {
        $filePath = null;
        if ($file) {
            $filePath = $file->store('uploads/elearning/submissions', 'public');
        }

        $student = Student::where('user_id', $studentId)->first();
        $targetStudentId = $student ? $student->id : $studentId;

        return ElearningSubmission::updateOrCreate(
            [
                'elearning_assignment_id' => $assignmentId,
                'student_id' => $targetStudentId,
            ],
            [
                'content' => $content,
                'file_path' => $filePath,
                'submitted_at' => now(),
                'status' => 'submitted',
            ]
        );
    }

    public function gradeSubmission(int $submissionId, int $score, ?string $feedback = null): ElearningSubmission
    {
        $sub = ElearningSubmission::findOrFail($submissionId);
        $sub->update([
            'score' => $score,
            'feedback' => $feedback,
            'status' => 'graded',
        ]);

        return $sub;
    }
}
