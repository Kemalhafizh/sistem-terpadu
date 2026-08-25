<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase70GenerativeAiTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_personalized_tutor_socratic_flow(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        // Start Session
        $startRes = $this->actingAs($student, 'sanctum')->postJson('/api/v1/ai/tutor/session', [
            'subject_name' => 'Fisika',
            'topic' => 'Hukum Termodinamika II',
            'difficulty_level' => 'intermediate',
        ]);

        $startRes->assertStatus(201);
        $sessionId = $startRes->json('session.id');
        $this->assertNotNull($sessionId);

        // Send Student Message
        $replyRes = $this->actingAs($student, 'sanctum')->postJson("/api/v1/ai/tutor/session/{$sessionId}/message", [
            'message' => 'Entropi alam semesta selalu meningkat dalam proses spontan.',
        ]);

        $replyRes->assertStatus(200);
        $replyRes->assertJsonPath('success', true);
        $this->assertNotEmpty($replyRes->json('data.ai_response.message'));
    }

    public function test_nlp_essay_grader_and_plagiarism_analysis(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $res = $this->actingAs($student, 'sanctum')->postJson('/api/v1/ai/essay/grade', [
            'assignment_title' => 'Dampak Kecerdasan Buatan pada Pendidikan Islam',
            'essay_text' => 'Pemanfaatan kecerdasan buatan dalam pendidikan Islam membuka peluang besar untuk personalisasi pembelajaran Al-Qur\'an dan hadits. Namun demikian, nilai-nilai akhlakul karimah dan keteladanan guru tetap menjadi pilar yang tidak tergantikan oleh algoritma mesin.',
        ]);

        $res->assertStatus(201);
        $this->assertGreaterThan(50.0, $res->json('submission.ai_score'));
        $this->assertNotNull($res->json('submission.originality_score'));
        $this->assertNotNull($res->json('submission.ai_feedback'));
    }

    public function test_ai_lesson_plan_generator_for_teachers(): void
    {
        $teacher = User::factory()->create(['role' => 'guru']);

        $res = $this->actingAs($teacher, 'sanctum')->postJson('/api/v1/ai/lesson-plan/generate', [
            'subject_name' => 'Biologi',
            'grade_level' => 'XI MIPA',
            'topic' => 'Sistem Sirkulasi Darah Manusia',
        ]);

        $res->assertStatus(201);
        $res->assertJsonPath('lesson_plan.curriculum_type', 'Kurikulum Merdeka');
        $this->assertNotEmpty($res->json('lesson_plan.learning_objectives'));
        $this->assertNotEmpty($res->json('lesson_plan.pedagogical_steps'));
    }
}
