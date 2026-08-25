<?php

namespace Tests\Feature\ExtendedDiamond;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase63MobileExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_mobile_dashboard_returns_200(): void
    {
        $teacher = User::factory()->create(['role' => 'guru']);

        $response = $this->actingAs($teacher)->get(route('mobile.teacher.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dasbor Mobile Guru');
    }

    public function test_student_mobile_hub_returns_200(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($studentUser)->get(route('mobile.student.hub'));

        $response->assertStatus(200);
        $response->assertSee('Hub Belajar Siswa');
    }

    public function test_parent_mobile_quick_actions_returns_200(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);

        $response = $this->actingAs($parent)->get(route('mobile.parent.actions'));

        $response->assertStatus(200);
        $response->assertSee('Aksi Cepat Orang Tua');
    }
}
