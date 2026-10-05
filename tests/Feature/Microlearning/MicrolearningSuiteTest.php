<?php

namespace Tests\Feature\Microlearning;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MicrolearningSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_micro_lessons_index()
    {
        $response = $this->actingAs($this->user)->get(route('micro-lessons.index'));
        $response->assertStatus(200);
    }

    public function test_gamified_quizzes_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('gamified-quizzes.dashboard'));
        $response->assertStatus(200);
    }

    public function test_spaced_repetition_deck()
    {
        // FASE 118: the spaced-repetition deck is a student's own.
        $response = $this->actingAs(User::factory()->create(['role' => 'siswa']))->get(route('spaced-repetition.deck'));
        $response->assertStatus(200);
    }

    public function test_transcripts_index()
    {
        $response = $this->actingAs($this->user)->get(route('transcripts.index'));
        $response->assertStatus(200);
    }

    /** FASE 118: students study lessons but do not author them; parents see neither. */
    public function test_roles_are_separated()
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($student)->get(route('micro-lessons.index'))->assertOk();
        $this->actingAs($student)->get(route('micro-lessons.create'))->assertForbidden();
        $this->actingAs($student)->get(route('gamified-quizzes.dashboard'))->assertForbidden();

        $parent = User::factory()->create(['role' => 'orang_tua']);
        $this->actingAs($parent)->get(route('micro-lessons.index'))->assertForbidden();
        $this->actingAs($parent)->get(route('spaced-repetition.deck'))->assertForbidden();
    }

    /** FASE 118.3: saving a micro lesson has no backend yet; it says so instead of pretending. */
    public function test_saving_a_lesson_answers_501()
    {
        $this->actingAs($this->user)->post(route('micro-lessons.store'), ['title' => 'Wudhu'])
            ->assertStatus(501);
    }
}
