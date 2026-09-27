<?php

namespace App\Services;

use App\Models\ElearningAssignment;
use App\Models\ElearningClass;
use App\Models\ElearningMaterial;
use App\Models\ElearningMaterialView;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ElearningService
{
    /**
     * Duplicate a virtual classroom along with its materials and assignments.
     */
    public function duplicateClass(int $classId, int $teacherId): ElearningClass
    {
        return DB::transaction(function () use ($classId, $teacherId) {
            $originalClass = ElearningClass::with(['materials', 'assignments'])->findOrFail($classId);

            // 1. Clone Class
            $newClass = ElearningClass::create([
                'uuid' => (string) Str::uuid(),
                'subject_id' => $originalClass->subject_id,
                'classroom_id' => $originalClass->classroom_id,
                'teacher_id' => $teacherId,
                'name' => 'Salinan '.$originalClass->name,
                'description' => $originalClass->description,
                'is_archived' => false,
            ]);

            // 2. Clone Materials
            foreach ($originalClass->materials as $material) {
                ElearningMaterial::create([
                    'uuid' => (string) Str::uuid(),
                    'elearning_class_id' => $newClass->id,
                    'title' => $material->title,
                    'description' => $material->description,
                    'file_path' => $material->file_path,
                    'video_url' => $material->video_url,
                ]);
            }

            // 3. Clone Assignments (shift due date to 7 days from now)
            foreach ($originalClass->assignments as $assignment) {
                ElearningAssignment::create([
                    'uuid' => (string) Str::uuid(),
                    'elearning_class_id' => $newClass->id,
                    'title' => $assignment->title,
                    'description' => $assignment->description,
                    'due_at' => now()->addDays(7),
                    'allow_late' => $assignment->allow_late,
                    'is_secure_mode' => $assignment->is_secure_mode,
                    'max_violations' => $assignment->max_violations,
                ]);
            }

            return $newClass;
        });
    }

    /**
     * Toggle archived state of a class.
     */
    public function toggleArchive(int $classId): void
    {
        $class = ElearningClass::findOrFail($classId);
        $class->update([
            'is_archived' => ! $class->is_archived,
        ]);
    }

    /**
     * Log student reading learning material.
     */
    public function logMaterialView(int $studentId, int $materialId): void
    {
        ElearningMaterialView::firstOrCreate([
            'student_id' => $studentId,
            'elearning_material_id' => $materialId,
        ], [
            'viewed_at' => now(),
        ]);
    }

    /**
     * Get learning progress details for a specific student.
     */
    public function getStudentProgress(int $classId, int $studentId): array
    {
        $class = ElearningClass::with(['materials', 'assignments'])->findOrFail($classId);
        $student = Student::findOrFail($studentId);

        $totalMaterials = $class->materials->count();
        $totalAssignments = $class->assignments->count();
        $totalItems = $totalMaterials + $totalAssignments;

        $viewedMaterialIds = ElearningMaterialView::where('student_id', $studentId)
            ->whereIn('elearning_material_id', $class->materials->pluck('id'))
            ->pluck('elearning_material_id')
            ->toArray();

        $readCount = count($viewedMaterialIds);

        // Fetch submissions for this class assignments
        $submissions = DB::table('elearning_submissions')
            ->where('student_id', $studentId)
            ->whereIn('elearning_assignment_id', $class->assignments->pluck('id'))
            ->get()
            ->keyBy('elearning_assignment_id');

        $submittedCount = $submissions->count();

        $percentage = $totalItems > 0
            ? round((($readCount + $submittedCount) / $totalItems) * 100, 1)
            : 100.0;

        $materialsProgress = [];
        foreach ($class->materials as $material) {
            $viewRecord = DB::table('elearning_material_views')
                ->where('student_id', $studentId)
                ->where('elearning_material_id', $material->id)
                ->first();

            $materialsProgress[] = [
                'material' => $material,
                'is_viewed' => in_array($material->id, $viewedMaterialIds),
                'viewed_at' => $viewRecord ? $viewRecord->viewed_at : null,
            ];
        }

        $assignmentsProgress = [];
        $totalScore = 0;
        $gradedCount = 0;

        foreach ($class->assignments as $assignment) {
            $sub = $submissions->get($assignment->id);
            $score = $sub && ! is_null($sub->score) ? (float) $sub->score : null;

            if (! is_null($score)) {
                $totalScore += $score;
                $gradedCount++;
            }

            $assignmentsProgress[] = [
                'assignment' => $assignment,
                'is_submitted' => ! is_null($sub),
                'submitted_at' => $sub ? $sub->submitted_at : null,
                'status' => $sub ? $sub->status : 'pending',
                'score' => $score,
                'feedback' => $sub ? $sub->feedback : null,
            ];
        }

        $averageScore = $gradedCount > 0 ? round($totalScore / $gradedCount, 2) : 0.0;

        return [
            'student' => $student,
            'total_materials' => $totalMaterials,
            'read_materials' => $readCount,
            'total_assignments' => $totalAssignments,
            'submitted_assignments' => $submittedCount,
            'completion_percentage' => $percentage,
            'materials' => $materialsProgress,
            'assignments' => $assignmentsProgress,
            'average_score' => $averageScore,
        ];
    }

    /**
     * Get aggregate progress summary for all students in a virtual class.
     */
    public function getClassProgressSummary(int $classId): array
    {
        $class = ElearningClass::findOrFail($classId);

        // Get all students in the classroom
        $students = Student::where('classroom_id', $class->classroom_id)
            ->with('user')
            ->get();

        $studentsProgress = [];
        $totalCompletion = 0.0;

        foreach ($students as $student) {
            $prog = $this->getStudentProgress($classId, $student->id);
            $totalCompletion += $prog['completion_percentage'];

            $studentsProgress[] = [
                'student' => $student,
                'completion_percentage' => $prog['completion_percentage'],
                'read_materials' => $prog['read_materials'],
                'submitted_assignments' => $prog['submitted_assignments'],
                'average_score' => $prog['average_score'],
            ];
        }

        $averageCompletion = $students->count() > 0
            ? round($totalCompletion / $students->count(), 1)
            : 100.0;

        return [
            'class' => $class,
            'students' => collect($studentsProgress)->sortByDesc('completion_percentage')->values(),
            'average_completion_percentage' => $averageCompletion,
        ];
    }
}
