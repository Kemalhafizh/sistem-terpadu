<?php

namespace Database\Seeders;

use App\Models\ClassFeedPost;
use App\Models\Employee;
use App\Models\FamilyLearningPlan;
use App\Models\FamilyTask;
use App\Models\ParentSkill;
use App\Models\Student;
use Illuminate\Database\Seeder;

class FamilyEngagementSeeder extends Seeder
{
    public function run(): void
    {
        $student = Student::first();
        $teacher = Employee::first();

        if (! $student || ! $teacher) {
            return;
        }

        FamilyTask::create([
            'student_id' => $student->id,
            'assignment_id' => 1,
            'parent_guide_notes' => 'Bantu anak memahami konsep dasar aljabar. Pastikan lingkungan tenang.',
            'estimated_parent_time_minutes' => 20,
            'parent_reviewed' => false,
        ]);

        ClassFeedPost::create([
            'teacher_id' => $teacher->id,
            'target_audience' => ['all'],
            'content' => 'Alhamdulillah, hari ini anak-anak berhasil menyelesaikan proyek sains dengan sangat baik.',
            'post_type' => 'achievement_highlight',
            'requires_acknowledgment' => true,
        ]);

        FamilyLearningPlan::create([
            'student_id' => $student->id,
            'academic_term' => 'Semester Ganjil 2026/2027',
            'focus_area' => 'Peningkatan hafalan Al-Quran dan nilai Matematika.',
            'school_action_items' => ['Mentoring pekanan', 'Kelas tambahan MTK'],
            'home_action_items' => ['Murojaah ba\'da maghrib', 'Mengerjakan PR dengan pendampingan'],
            'next_checkin_date' => now()->addMonths(1),
        ]);

        ParentSkill::create([
            'student_id' => $student->id,
            'parent_name' => 'Bapak Ahmad',
            'profession' => 'Software Engineer',
            'skill_tags' => ['Programming', 'Teknologi', 'Karir IT'],
            'available_for_guest_lecture' => true,
            'available_for_mentorship' => true,
        ]);
    }
}
