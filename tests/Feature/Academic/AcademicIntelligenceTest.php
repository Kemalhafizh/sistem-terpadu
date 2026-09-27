<?php

namespace Tests\Feature\Academic;

use App\Models\AcademicPathway;
use App\Models\Subject;
use App\Models\User;
use App\Services\Academic\AcademicPathwayService;
use App\Services\Academic\CompetencyMappingService;
use App\Services\Academic\ScormService;
use App\Services\Academic\VirtualClassroomService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $student;

    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'superadmin']);
        $this->student = User::factory()->create(['role' => 'student']);
        $this->subject = Subject::factory()->create(['name' => 'Fisika', 'code' => 'FIS-E10']);
    }

    /** @test */
    public function test_35_1_kurikulum_merdeka_competency_mapping()
    {
        $service = app(CompetencyMappingService::class);

        // 1. Outcome CP
        $outcome = $service->createOutcome([
            'subject_id' => $this->subject->id,
            'phase' => 'E',
            'element' => 'Pemahaman Fisika',
            'cp_code' => 'CP-FIS-E01',
            'cp_description' => 'Menganalisis konsep pengukuran dan gerak.',
        ]);

        $this->assertEquals('CP-FIS-E01', $outcome->cp_code);

        // 2. Objective TP
        $objective = $service->createObjective($outcome, [
            'tp_code' => 'TP-FIS-01',
            'tp_description' => 'Menerapkan besaran fisika dalam eksperimen.',
            'semester' => 'ganjil',
            'bloom_taxonomy_level' => 'apply',
        ]);

        $this->assertEquals('TP-FIS-01', $objective->tp_code);

        // 3. ATP Mapping
        $atp = $service->mapToAtp($objective, [
            'week_number' => 1,
            'teaching_hours' => 2,
            'learning_activities' => 'Praktikum pengukuran dengan jangka sorong.',
            'assessment_method' => 'Tes Unjuk Kerja',
        ]);

        $this->assertEquals(1, $atp->week_number);

        // 4. Achievement
        $ach = $service->assessStudentCompetency($this->student->id, $objective->id, 'achieved', $this->admin->id);
        $this->assertEquals('achieved', $ach->status);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('academic.competency.outcomes'));
        $response->assertStatus(200);
        $response->assertSee('Kurikulum Merdeka Competency Mapping');
    }

    /** @test */
    public function test_35_2_multi_year_academic_planning_and_graduation_pathway()
    {
        $service = app(AcademicPathwayService::class);

        $pathway = AcademicPathway::create([
            'name' => 'IPA',
            'description' => 'Saintek Pathway',
            'required_credits' => 120,
        ]);

        // Enroll
        $enrollment = $service->enrollStudent($this->student->id, $pathway->id);
        $this->assertEquals($pathway->id, $enrollment->academic_pathway_id);

        // Check readiness
        $readiness = $service->checkGraduationReadiness($this->student->id);
        $this->assertIsArray($readiness);
        $this->assertArrayHasKey('readiness_score', $readiness);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('academic.pathways.index'));
        $response->assertStatus(200);
        $response->assertSee('Multi-Year Academic Planning');
    }

    /** @test */
    public function test_35_3_scorm_xapi_learning_content_interoperability()
    {
        $service = app(ScormService::class);

        // Upload
        $package = $service->uploadPackage([
            'title' => 'Optik Interaktif SCORM',
            'version' => 'scorm12',
            'file_path' => 'storage/scorm/optics.zip',
        ], $this->admin->id);

        $this->assertEquals('ready', $package->status);

        // Record tracking
        $tracking = $service->recordTracking($package, $this->student->id, [
            'lesson_status' => 'passed',
            'score_raw' => 95,
        ]);

        $this->assertEquals('passed', $tracking->lesson_status);

        // Record xAPI
        $xapi = $service->recordXapiStatement($this->student->id, 'completed', 'scorm_module', 'OPTICS-01');
        $this->assertEquals('completed', $xapi->verb);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('academic.scorm.packages'));
        $response->assertStatus(200);
        $response->assertSee('Optik Interaktif SCORM');
    }

    /** @test */
    public function test_35_4_virtual_classroom_hub()
    {
        $service = app(VirtualClassroomService::class);

        // Create Meeting
        $meeting = $service->createMeeting([
            'title' => 'Webinar Fisika Modern',
            'provider' => 'google_meet',
            'start_time' => now()->addHour(),
            'duration_minutes' => 60,
        ], $this->admin->id);

        $this->assertNotNull($meeting->meeting_url);

        // Record Attendance
        $att = $service->recordAttendance($meeting, $this->student->id);
        $this->assertEquals($this->student->id, $att->user_id);
        $this->assertEquals(1, $meeting->fresh()->attendees_count);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('academic.virtual.index'));
        $response->assertStatus(200);
        $response->assertSee('Webinar Fisika Modern');
    }
}
