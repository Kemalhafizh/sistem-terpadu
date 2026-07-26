<?php

namespace Database\Seeders;

use App\Models\CharacterDimension;

use App\Models\PtcSession;
use App\Models\User;
use App\Services\Growth\P5AssessmentService;
use App\Services\Growth\PtcSchedulerService;
use App\Services\Growth\StudentLifecycleService;
use App\Services\Growth\WellbeingService;
use Illuminate\Database\Seeder;

class StudentGrowthSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::where('role', 'superadmin')->first() ?? User::factory()->create(['role' => 'superadmin']);
        $student = User::where('role', 'student')->first() ?? User::factory()->create(['role' => 'student']);
        $parent = User::where('role', 'parent')->first() ?? User::factory()->create(['role' => 'parent']);
        $teacher = User::where('role', 'guru')->first() ?? User::factory()->create(['role' => 'guru']);

        // 1. Character Dimensions (6 P5 + 5 Islamic)
        $dimensions = [
            ['name' => 'Beriman & Bertakwa kepada Tuhan YME', 'category' => 'p5', 'description' => 'Memiliki akhlak mulia dan taat beribadah.', 'display_order' => 1],
            ['name' => 'Berkebinekaan Global', 'category' => 'p5', 'description' => 'Menghargai keberagaman budaya dan toleransi.', 'display_order' => 2],
            ['name' => 'Bergotong Royong', 'category' => 'p5', 'description' => 'Aktif bekerjasama dan membantu sesama.', 'display_order' => 3],
            ['name' => 'Mandiri', 'category' => 'p5', 'description' => 'Bertanggung jawab atas proses dan hasil belajar.', 'display_order' => 4],
            ['name' => 'Bernalar Kritis', 'category' => 'p5', 'description' => 'Memproses informasi dan menganalisis secara objektif.', 'display_order' => 5],
            ['name' => 'Kreatif', 'category' => 'p5', 'description' => 'Menghasilkan gagasan dan karya orisinal.', 'display_order' => 6],
            ['name' => 'Jujur (Shiddiq)', 'category' => 'islamic', 'description' => 'Jujur dalam perkataan dan perbuatan.', 'display_order' => 7],
            ['name' => 'Amanah', 'category' => 'islamic', 'description' => 'Dapat dipercaya memegang tanggung jawab.', 'display_order' => 8],
            ['name' => 'Istiqomah', 'category' => 'islamic', 'description' => 'Konsisten dalam beramal shalih.', 'display_order' => 9],
            ['name' => 'Tawadhu (Rendah Hati)', 'category' => 'islamic', 'description' => 'Tidak sombong dan menghormati sesama.', 'display_order' => 10],
            ['name' => 'Ta\'awun (Tolong-Menolong)', 'category' => 'islamic', 'description' => 'Saling membantu dalam kebaikan dan takwa.', 'display_order' => 11],
        ];

        foreach ($dimensions as $d) {
            CharacterDimension::updateOrCreate(['name' => $d['name']], $d);
        }

        // 2. Wellbeing Screening & Mood Journal
        $wellbeingService = app(WellbeingService::class);
        $sc = $wellbeingService->createScreening([
            'title' => 'Skrining Kesehatan Emosional Semester Ganjil 2026/2027',
            'screening_type' => 'srq20',
            'academic_year' => '2026/2027',
        ], $superadmin->id);
        $wellbeingService->activateScreening($sc);

        $wellbeingService->submitResponse($sc, $student->id, [
            'responses' => ['q0' => 1, 'q1' => 0, 'q2' => 0, 'q3' => 1, 'q4' => 0, 'q5' => 0, 'q6' => 0, 'q7' => 0]
        ]);

        $wellbeingService->addMoodJournalEntry($student->id, [
            'mood_level' => 4,
            'journal_entry' => 'Hari ini mengikuti kegiatan sholat Dhuha berjamaah dan merasa tenang.',
            'sleep_hours' => 8.0,
        ]);

        // 3. P5 Assessments & Merit
        $p5Service = app(P5AssessmentService::class);
        $dim1 = CharacterDimension::first();
        if ($dim1) {
            $p5Service->assessStudent($student->id, $superadmin->id, $dim1->id, [
                'score' => 4,
                'evidence_notes' => 'Aktif memimpin doa dan sholat berjamaah.',
            ]);
            $p5Service->awardMeritPoints($student->id, $superadmin->id, 50, 'Partisipasi Utama Panitia PHBI Maulid Nabi', $dim1->id);
        }

        // 4. PTC Sessions & Slots
        $ptcService = app(PtcSchedulerService::class);
        $ptcSession = $ptcService->createSession([
            'title' => 'Parent-Teacher Conference Mid Semester Ganjil 2026',
            'academic_year' => '2026/2027',
            'date' => date('Y-m-d', strtotime('+3 days')),
            'start_time' => '08:00',
            'end_time' => '11:00',
            'slot_duration_minutes' => 30,
        ], $superadmin->id);

        $slot = $ptcSession->slots()->where('status', 'available')->first();
        if ($slot) {
            $ptcService->bookSlot($slot, $parent->id, $student->id, 'Diskusi Perkembangan Belajar & Hasil Tahfidz');
        }

        // 5. Student Lifecycle
        $lifecycleService = app(StudentLifecycleService::class);
        $lifecycleService->recordEvent($student->id, 'enrolled', 'Active Student', $superadmin->id, null, null, 'Siswa terdaftar resmi di SMA ISA 1 Semarang');
        $lifecycleService->recordMilestone($student->id, 'first_day', 'Hari pertama masuk sekolah T.A. 2026/2027');
    }
}
