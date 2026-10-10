<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassroomLayout;
use App\Models\ExecutiveKpi;
use App\Models\KanbanBoard;
use App\Models\KanbanTask;
use App\Models\LocalizationString;
use App\Models\ScheduleBuilderDraft;
use App\Models\Student;
use App\Models\StudentProgressSnapshot;
use App\Models\Teacher360Metric;
use App\Models\ThemePreference;
use App\Models\User;
use App\Models\VoiceNavigationCommand;
use App\Models\WcagScanReport;
use App\Models\WhiteboardSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExtendedDiamondSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin', 'name' => 'Admin SISTA']);
        $guru = User::where('role', 'guru')->first() ?? User::factory()->create(['role' => 'guru', 'name' => 'Ustadz Ahmad Santoso, M.Pd']);
        $student = Student::first();
        $classroom = Classroom::first();

        // 1. FASE 64 Seeding
        ScheduleBuilderDraft::firstOrCreate(
            ['name' => 'Draft Jadwal Ganjil 2026/2027'],
            [
                'academic_year_id' => null,
                'schedule_data' => [
                    ['day' => 'Senin', 'time_slot' => '07:00-08:30', 'subject_id' => 1, 'teacher_id' => $guru->id, 'classroom_id' => 1],
                ],
                'conflict_reports' => [],
                'status' => 'approved',
                'created_by' => $admin->id,
            ]
        );

        if ($classroom) {
            ClassroomLayout::firstOrCreate(
                ['classroom_id' => $classroom->id],
                [
                    'layout_name' => 'Formasi U-Shape Diskusi '.$classroom->name,
                    'grid_rows' => 5,
                    'grid_columns' => 6,
                    'furniture_positions' => [],
                    'is_active' => true,
                ]
            );
        }

        $board = KanbanBoard::firstOrCreate(
            ['title' => 'Papan Tugas Utama Guru & Staf'],
            [
                'type' => 'general',
                'owner_id' => $admin->id,
                'columns' => [
                    ['id' => 'todo', 'title' => 'Rencana / To Do', 'color' => '#64748b'],
                    ['id' => 'in_progress', 'title' => 'Sedang Dikerjakan', 'color' => '#3b82f6'],
                    ['id' => 'review', 'title' => 'Review / Evaluasi', 'color' => '#f59e0b'],
                    ['id' => 'done', 'title' => 'Selesai', 'color' => '#10b981'],
                ],
            ]
        );

        KanbanTask::firstOrCreate(
            ['title' => 'Penyusunan Modul Ajar Kurikulum Merdeka'],
            [
                'kanban_board_id' => $board->id,
                'description' => 'Menyusun modul ajar mapel PAI dan Bahasa Arab terintegrasi nilai-nilai YBWSA.',
                'column_id' => 'in_progress',
                'position_order' => 1,
                'priority' => 'high',
                'assignee_id' => $guru->id,
                'due_date' => now()->addDays(5),
            ]
        );

        WhiteboardSession::firstOrCreate(
            ['title' => 'Kelas Virtual Matematika Diskrit X-MIPA 1'],
            [
                'session_id' => (string) Str::uuid(),
                'host_id' => $guru->id,
                'status' => 'active',
                'canvas_state_json' => json_encode(['objects' => []]),
            ]
        );

        // 2. FASE 65 Seeding
        ExecutiveKpi::firstOrCreate(
            ['metric_name' => 'Tingkat Kehadiran Siswa Hari Ini'],
            [
                'category' => 'attendance',
                'current_value' => 98.6,
                'target_value' => 98.0,
                'previous_value' => 97.2,
                'trend_direction' => 'up',
                'unit' => '%',
                'calculated_at' => now(),
            ]
        );

        Teacher360Metric::firstOrCreate(
            ['teacher_id' => $guru->id],
            [
                'student_satisfaction_score' => 92.5,
                'peer_review_score' => 94.0,
                'avg_class_grade' => 86.8,
                'attendance_percentage' => 100,
                'skill_radar_data' => ['pedagogy' => 92, 'personality' => 95, 'social' => 90, 'professional' => 94, 'leadership' => 88],
            ]
        );

        if ($student) {
            StudentProgressSnapshot::firstOrCreate(
                ['student_id' => $student->id],
                [
                    'academic_timeline' => [
                        ['event' => 'Penilaian Tengah Semester (PTS)', 'date' => '2026-03-15', 'score' => 88, 'status' => 'completed'],
                    ],
                    'competency_radar' => ['math_science' => 85, 'language' => 90, 'islamic_studies' => 95],
                    'overall_percentile' => 90,
                    'at_risk_status' => 'none',
                ]
            );
        }

        // Financial health metrics are no longer seeded with made-up figures:
        // FinancialScoreboardService computes them from real transactions
        // whenever the scoreboard is opened.

        // 3. FASE 68 Seeding
        WcagScanReport::firstOrCreate(
            ['scanned_url' => 'https://sistem.sultanagung1.sch.id'],
            [
                'compliance_score' => 98.00,
                'total_issues' => 1,
                'critical_issues' => 0,
                'scanned_at' => now(),
            ]
        );

        LocalizationString::firstOrCreate(
            ['group_name' => 'navigation', 'string_key' => 'dashboard'],
            [
                'text_id' => 'Dasbor Utama',
                'text_en' => 'Main Dashboard',
                'text_ar' => 'لوحة القيادة',
            ]
        );

        VoiceNavigationCommand::firstOrCreate(
            ['command_phrase' => 'buka rapor'],
            [
                'language_code' => 'id',
                'action_url' => '/grades',
                'target_module' => 'akademik',
                'is_active' => true,
            ]
        );

        ThemePreference::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'color_mode' => 'system',
                'font_scaling' => '100%',
                'reduce_animations' => false,
                'dyslexic_font_enabled' => false,
                'primary_color_hex' => '#059669',
            ]
        );
    }
}
