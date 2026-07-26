<?php

namespace Database\Seeders;

use App\Models\AcademicPathway;
use App\Models\GraduationRequirement;
use App\Models\Subject;
use App\Models\User;
use App\Services\Academic\AcademicPathwayService;
use App\Services\Academic\CompetencyMappingService;
use App\Services\Academic\ScormService;
use App\Services\Academic\VirtualClassroomService;
use Illuminate\Database\Seeder;

class AcademicIntelligenceSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::where('role', 'superadmin')->first() ?? User::factory()->create(['role' => 'superadmin']);
        $student = User::where('role', 'student')->first() ?? User::factory()->create(['role' => 'student']);
        $subject = Subject::first() ?? Subject::factory()->create(['name' => 'Biologi', 'code' => 'BIO-F10']);

        // 1. Competency Mapping CP/TP/ATP
        $compService = app(CompetencyMappingService::class);
        $outcome = $compService->createOutcome([
            'subject_id' => $subject->id,
            'phase' => 'E',
            'element' => 'Pemahaman Biologi',
            'cp_code' => 'CP-BIO-E01',
            'cp_description' => 'Peserta didik mampu mengidentifikasi struktur sel, keanekaragaman hayati, dan ekosistem lokal.',
        ]);

        $objective = $compService->createObjective($outcome, [
            'tp_code' => 'TP-BIO-01',
            'tp_description' => 'Menganalisis komponen penyusun sel dan mekanismenya.',
            'semester' => 'ganjil',
            'sequence_order' => 1,
            'assessment_type' => 'formatif',
            'bloom_taxonomy_level' => 'analyze',
        ]);

        $compService->mapToAtp($objective, [
            'week_number' => 1,
            'teaching_hours' => 2,
            'learning_activities' => 'Pengamatan mikroskopis jaringan tumbuhan di Laboratorium Biologi.',
            'assessment_method' => 'Laporan Praktikum & Unjuk Kerja',
        ]);

        $compService->assessStudentCompetency($student->id, $objective->id, 'achieved', $superadmin->id, 'Siswa mampu mengidentifikasi organel sel secara tepat.');

        // 2. Academic Pathways & Graduation Requirements
        $pathways = [
            ['name' => 'IPA', 'description' => 'Peminatan Matematika & Ilmu Pengetahuan Alam', 'required_credits' => 120],
            ['name' => 'IPS', 'description' => 'Peminatan Ilmu-Ilmu Sosial', 'required_credits' => 118],
            ['name' => 'Bahasa', 'description' => 'Peminatan Bahasa dan Budaya', 'required_credits' => 116],
            ['name' => 'Keagamaan', 'description' => 'Peminatan Ilmu-Ilmu Keagamaan Islam', 'required_credits' => 124],
        ];

        foreach ($pathways as $pw) {
            AcademicPathway::updateOrCreate(['name' => $pw['name']], $pw);
        }

        $ipaPathway = AcademicPathway::where('name', 'IPA')->first();
        if ($ipaPathway) {
            $pathwayService = app(AcademicPathwayService::class);
            $pathwayService->enrollStudent($student->id, $ipaPathway->id);
        }

        $gradReqs = [
            ['requirement_type' => 'subject_credit', 'description' => 'Minimal Kredit SKS Terlaksana', 'threshold_value' => '120 SKS'],
            ['requirement_type' => 'attendance_minimum', 'description' => 'Presensi Kehadiran Minimal', 'threshold_value' => '85%'],
            ['requirement_type' => 'tahfidz_minimum', 'description' => 'Target Setoran Tahfidz Al-Qur\'an', 'threshold_value' => 'Juz 30'],
            ['requirement_type' => 'thesis', 'description' => 'Penulisan Karya Tulis Ilmiah (KTI)', 'threshold_value' => 'Disetujui'],
        ];

        foreach ($gradReqs as $gr) {
            GraduationRequirement::updateOrCreate(['description' => $gr['description']], $gr);
        }

        // 3. SCORM Package
        $scormService = app(ScormService::class);
        $pkg = $scormService->uploadPackage([
            'title' => 'Modul Interaktif Simulasi Laboratorium Kimia Virtual',
            'version' => 'scorm12',
            'file_path' => 'storage/scorm/chemistry_lab_v1.zip',
            'entry_point' => 'index.html',
        ], $superadmin->id);

        $scormService->recordTracking($pkg, $student->id, [
            'lesson_status' => 'passed',
            'score_raw' => 90,
        ]);

        $scormService->recordXapiStatement($student->id, 'completed', 'module', 'CHEM-LAB-01', ['score' => 90]);

        // 4. Virtual Classroom
        $vcService = app(VirtualClassroomService::class);
        $meeting = $vcService->createMeeting([
            'title' => 'Kelas Virtual Pengayaan Persiapan OSN Biologi 2026',
            'provider' => 'google_meet',
            'start_time' => now()->addHour(),
            'duration_minutes' => 90,
        ], $superadmin->id);

        $vcService->recordAttendance($meeting, $student->id);
    }
}
