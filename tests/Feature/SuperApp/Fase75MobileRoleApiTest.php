<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase75MobileRoleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_mobile_api_schedule_and_classes(): void
    {
        $teacher = User::factory()->create(['role' => 'guru']);
        $response = $this->actingAs($teacher, 'sanctum')->getJson('/api/v1/teacher/schedule');
        $response->assertStatus(200);

        $classesResponse = $this->actingAs($teacher, 'sanctum')->getJson('/api/v1/teacher/classes');
        $classesResponse->assertStatus(200);
    }

    public function test_parent_mobile_api_children_monitoring(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);
        $studentUser = User::factory()->create(['role' => 'student']);

        $classroom = Classroom::first() ?? Classroom::create([
            'name' => 'XII MIPA 1',
            'grade_level' => 'XII',
            'major' => 'MIPA',
            'academic_year' => '2026/2027',
        ]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'nisn' => '9912345678',
            'nis' => '1201',
            'gender' => 'L',
            'classroom_id' => $classroom->id,
            'parent_id' => $parent->id,
        ]);

        $response = $this->actingAs($parent, 'sanctum')->getJson('/api/v1/parent/children');
        $response->assertStatus(200);
    }

    public function test_mobile_remote_config_and_feature_flags(): void
    {
        $response = $this->getJson('/api/v1/mobile/config?platform=android&build=102');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'app_name',
            'features',
        ]);
    }
}
