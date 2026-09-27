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
        $response = $this->actingAs($this->user)->get(route('spaced-repetition.deck'));
        $response->assertStatus(200);
    }

    public function test_transcripts_index()
    {
        $response = $this->actingAs($this->user)->get(route('transcripts.index'));
        $response->assertStatus(200);
    }
}
