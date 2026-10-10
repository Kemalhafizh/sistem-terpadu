<?php

namespace Tests\Feature\Growth;

use App\Models\CharacterDimension;
use App\Models\User;
use App\Services\Growth\P5AssessmentService;
use App\Services\Growth\PtcSchedulerService;
use App\Services\Growth\WellbeingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentGrowthTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $student;

    protected User $parent;

    protected User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'superadmin']);
        $this->student = User::factory()->create(['role' => 'student']);
        $this->parent = User::factory()->create(['role' => 'parent']);
        $this->teacher = User::factory()->create(['role' => 'guru']);
    }

    /** @test */
    public function test_34_1_wellbeing_screening_and_mood_journal()
    {
        $service = app(WellbeingService::class);

        // 1. Create & Activate Screening
        $screening = $service->createScreening([
            'title' => 'Skrining Mental Test',
            'screening_type' => 'srq20',
            'academic_year' => '2026/2027',
        ], $this->admin->id);

        $service->activateScreening($screening);
        $this->assertEquals('active', $screening->status);

        // 2. Submit Response
        $responseObj = $service->submitResponse($screening, $this->student->id, [
            'responses' => ['q1' => 1, 'q2' => 1, 'q3' => 1, 'q4' => 1, 'q5' => 1, 'q6' => 1, 'q7' => 1, 'q8' => 1],
        ]);

        $this->assertEquals(8, $responseObj->total_score);
        $this->assertEquals('high', $responseObj->risk_level);
        $this->assertTrue($responseObj->followup_required);

        // 3. Mood Journal
        $journal = $service->addMoodJournalEntry($this->student->id, [
            'mood_level' => 5,
            'journal_entry' => 'Hari ini menyenangkan!',
            'sleep_hours' => 8.0,
        ]);

        $this->assertEquals(5, $journal->mood_level);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('growth.wellbeing.screenings'));
        $response->assertStatus(200);
        $response->assertSee('Skrining Mental Test');
    }

    /** @test */
    public function test_34_2_character_p5_assessments_and_merit_points()
    {
        $service = app(P5AssessmentService::class);

        $dimension = CharacterDimension::create([
            'name' => 'Bergotong Royong',
            'category' => 'p5',
            'description' => 'Kerjasama tim',
        ]);

        // 1. Assess
        $ass = $service->assessStudent($this->student->id, $this->teacher->id, $dimension->id, [
            'score' => 4,
            'evidence_notes' => 'Sangat aktif membantu kawan.',
        ]);

        $this->assertEquals(4, $ass->score);

        // 2. Merit
        $merit = $service->awardMeritPoints($this->student->id, $this->teacher->id, 25, 'Gotong Royong Kebersihan', $dimension->id);
        $this->assertEquals(25, $merit->points);

        // 3. Radar Report
        $radar = $service->getStudentCharacterRadar($this->student->id);
        $this->assertEquals(25, $radar['total_merit_points']);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('growth.character.student_report', $this->student->id));
        $response->assertStatus(200);
        $response->assertSee('Character Report Card P5');
    }

    /** @test */
    public function test_34_3_parent_teacher_conference_scheduler()
    {
        $service = app(PtcSchedulerService::class);

        // 1. Create session & slots
        $session = $service->createSession([
            'title' => 'PTC Mid Semester',
            'academic_year' => '2026/2027',
            'date' => date('Y-m-d', strtotime('+5 days')),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'slot_duration_minutes' => 30,
        ], $this->admin->id);

        $this->assertGreaterThan(0, $session->slots()->count());

        // 2. Book Slot
        $slot = $session->slots()->first();
        $bookedSlot = $service->bookSlot($slot, $this->parent->id, $this->student->id, 'Konsultasi Rapor');

        $this->assertEquals('booked', $bookedSlot->status);
        $this->assertEquals($this->parent->id, $bookedSlot->parent_id);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('growth.ptc.sessions'));
        $response->assertStatus(200);
        $response->assertSee('PTC Mid Semester');
    }
}
