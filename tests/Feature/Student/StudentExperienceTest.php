<?php

namespace Tests\Feature\Student;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'siswa']);
    }

    public function test_can_view_student_portal_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('student.portal.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Student Self-Service Portal');
    }

    public function test_can_view_virtual_id_card()
    {
        $response = $this->actingAs($this->user)->get(route('student.idcard.my_card'));
        $response->assertStatus(200);
        $response->assertSee('Kartu Pelajar Digital');
    }

    public function test_can_view_certificate_templates()
    {
        $response = $this->actingAs($this->user)->get(route('certificates.templates'));
        $response->assertStatus(200);
        $response->assertSee('Student Achievement Certificate Generator');
    }

    public function test_can_view_course_evaluations()
    {
        $response = $this->actingAs($this->user)->get(route('evaluations.index'));
        $response->assertStatus(200);
        $response->assertSee('Student Feedback', false);
    }
}
