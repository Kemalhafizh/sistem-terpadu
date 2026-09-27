<?php

namespace Tests\Feature\Adaptive;

use App\Models\CatItemResponse;
use App\Models\CatSession;
use App\Models\Exam;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\User;
use App\Services\Adaptive\IrtCalibrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdaptiveLearningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
    }

    public function test_can_view_irt_calibration_dashboard()
    {
        $response = $this->get('/adaptive/calibration/dashboard');
        $response->assertStatus(200);
    }

    public function test_can_view_cat_admin_sessions()
    {
        $response = $this->get('/adaptive/cat/admin-sessions');
        $response->assertStatus(200);
    }

    public function test_can_view_learning_path_index()
    {
        $response = $this->get('/adaptive/learning-paths');
        $response->assertStatus(200);
    }

    public function test_can_view_mastery_heatmap()
    {
        $response = $this->get('/adaptive/mastery/heatmap/1');
        $response->assertStatus(200);
    }

    private function makeQuestion(Exam $exam): QuestionBank
    {
        return QuestionBank::create([
            'subject_id' => $exam->subject_id,
            'created_by' => $exam->created_by,
            'question_text' => 'Berapakah 2 + 2?',
            'type' => 'multiple_choice',
            'options' => ['A' => '3', 'B' => '4'],
            'correct_answer' => 'B',
            'grade_level' => 'X',
        ]);
    }

    public function test_irt_calibration_is_stored_against_question_bank_item()
    {
        $question = $this->makeQuestion(Exam::factory()->create());

        $parameter = app(IrtCalibrationService::class)->calibrateItem($question->id);

        $this->assertTrue($parameter->exists);
        $this->assertInstanceOf(QuestionBank::class, $parameter->question);
        $this->assertTrue($parameter->question->is($question));
    }

    public function test_cat_item_response_belongs_to_question_bank_item()
    {
        $exam = Exam::factory()->create();
        $question = $this->makeQuestion($exam);
        $session = CatSession::create([
            'uuid' => (string) Str::uuid(),
            'student_id' => Student::factory()->create()->id,
            'exam_id' => $exam->id,
            'started_at' => now(),
        ]);

        $response = CatItemResponse::create([
            'cat_session_id' => $session->id,
            'question_id' => $question->id,
            'sequence_number' => 1,
            'is_correct' => true,
            'theta_before' => 0,
            'theta_after' => 0.2,
            'item_difficulty' => 0.1,
        ]);

        $this->assertTrue($response->question->is($question));
        $this->assertTrue($session->responses()->first()->is($response));
    }
}
