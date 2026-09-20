<?php

namespace Tests\Feature\Api;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PtnProgram;
use App\Models\Student;
use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Models\TryoutQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UtbkMobileApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $studentUser;
    protected Student $student;
    protected Tryout $activeTryout;
    protected TryoutQuestion $q1;
    protected TryoutQuestion $q2;
    protected PtnProgram $ptn1;
    protected PtnProgram $ptn2;

    protected function setUp(): void
    {
        parent::setUp();

        $academicYear = AcademicYear::factory()->create(['is_active' => true]);
        $classroom = Classroom::factory()->create(['academic_year' => '2026/2027']);
        
        $this->studentUser = User::factory()->create(['role' => 'student']);
        $this->student = Student::factory()->create([
            'user_id' => $this->studentUser->id,
            'classroom_id' => $classroom->id,
        ]);

        $this->activeTryout = Tryout::create([
            'title' => 'Tryout Akbar SNBT 2026',
            'start_time' => now()->subDay(),
            'end_time' => now()->addDays(2),
            'duration_minutes' => 120,
            'status' => 'active',
        ]);

        // Question 1: TPS, Average Difficulty
        $this->q1 = TryoutQuestion::create([
            'tryout_id' => $this->activeTryout->id,
            'sub_test' => 'TPS',
            'question_text' => 'Berapa 1+1?',
            'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4', 'E' => '5'],
            'correct_answer' => 'B',
            'difficulty_b' => 0.0, // average
            'discrimination_a' => 1.0,
        ]);

        // Question 2: TPS, High Difficulty
        $this->q2 = TryoutQuestion::create([
            'tryout_id' => $this->activeTryout->id,
            'sub_test' => 'TPS',
            'question_text' => 'Soal Sulit',
            'options' => ['A', 'B', 'C', 'D', 'E'],
            'correct_answer' => 'A',
            'difficulty_b' => 2.0, // hard
            'discrimination_a' => 1.5,
        ]);

        $this->ptn1 = PtnProgram::create([
            'university_name' => 'Universitas Indonesia',
            'program_name' => 'Kedokteran',
            'cluster' => 'Saintek',
            'passing_grade' => 750,
            'capacity' => 50,
        ]);

        $this->ptn2 = PtnProgram::create([
            'university_name' => 'Universitas Brawijaya',
            'program_name' => 'Sastra Inggris',
            'cluster' => 'Soshum',
            'passing_grade' => 550,
            'capacity' => 80,
        ]);
    }

    public function test_irt_rasch_scoring_calculation_accuracy()
    {
        Sanctum::actingAs($this->studentUser);

        // Student answers Q1 correctly, Q2 wrong
        $answers = [
            $this->q1->id => 'B', // Correct
            $this->q2->id => 'B', // Wrong
        ];

        $response = $this->postJson("/api/v1/utbk/tryouts/{$this->activeTryout->id}/submit", [
            'answers' => $answers
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Assert attempt is created with calculated score
        $attempt = TryoutAttempt::where('student_id', $this->student->id)->first();
        $this->assertNotNull($attempt);
        
        // Check scores. With MLE and 1 correct (easy), 1 wrong (hard), theta should be around 0.
        // Base UTBK score = 500. So we expect score ~ 500.
        $this->assertTrue($attempt->score_tps > 300 && $attempt->score_tps < 700);
    }

    public function test_rekomendasi_ptn_filter_berdasarkan_skor_minimum()
    {
        Sanctum::actingAs($this->studentUser);

        // Mock an attempt with a high score
        TryoutAttempt::create([
            'tryout_id' => $this->activeTryout->id,
            'student_id' => $this->student->id,
            'score_tps' => 780,
            'score_literasi' => 780,
            'score_penalaran' => 780,
            'total_score' => 780,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/utbk/recommendations');
        $response->assertStatus(200);

        $recs = $response->json('recommendations');
        $this->assertCount(2, $recs); // Both UI and UB should be recommended since score 780 > both

        // Now change attempt score to 600
        TryoutAttempt::where('student_id', $this->student->id)->update(['total_score' => 600]);
        
        $response2 = $this->getJson('/api/v1/utbk/recommendations');
        $recs2 = $response2->json('recommendations');
        
        $this->assertCount(1, $recs2); // Only UB (550) should be recommended, UI (750) is excluded
        $this->assertEquals('Universitas Brawijaya', $recs2[0]['university']);
    }

    public function test_gagal_submit_tryout_bila_sudah_mengerjakan()
    {
        Sanctum::actingAs($this->studentUser);

        TryoutAttempt::create([
            'tryout_id' => $this->activeTryout->id,
            'student_id' => $this->student->id,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $response = $this->postJson("/api/v1/utbk/tryouts/{$this->activeTryout->id}/submit", [
            'answers' => [$this->q1->id => 'A']
        ]);

        $response->assertStatus(400);
        $this->assertStringContainsString('sudah mengerjakan tryout ini', $response->json('message'));
    }
}
