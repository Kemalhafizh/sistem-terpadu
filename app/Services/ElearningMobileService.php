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
        $student = Student::where('user_id', $studentId)->first();
        if (! $student || ! $student->classroom_id) {
            return collect();
        }

        return ElearningClass::with(['subject', 'classroom', 'teacher'])
            ->withCount(['materials', 'assignments'])
            ->where('classroom_id', $student->classroom_id)
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
            $studentRecordId = Student::where('user_id', $studentId)->value('id');
            $assignments->map(function ($assignment) use ($studentRecordId) {
                $sub = $studentRecordId
                    ? ElearningSubmission::where('elearning_assignment_id', $assignment->id)
                        ->where('student_id', $studentRecordId)
                        ->first()
                    : null;
                $assignment->student_submission = $sub;
                $assignment->is_submitted = $sub !== null;

                return $assignment;
            });
        }

        return $assignments;
    }

    public function submitAssignment(int $assignmentId, int $studentId, ?string $content, ?UploadedFile $file): ElearningSubmission
    {
        $student = Student::where('user_id', $studentId)->first();
        abort_unless($student !== null, 403, 'Profil siswa tidak ditemukan.');

        $filePath = null;
        if ($file) {
            $filePath = $file->store('uploads/elearning/submissions', 'public');
        }

        return ElearningSubmission::updateOrCreate(
            [
                'elearning_assignment_id' => $assignmentId,
                'student_id' => $student->id,
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
