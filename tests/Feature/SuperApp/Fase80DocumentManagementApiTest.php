<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase80DocumentManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_grades_and_report_card_data(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $classroom = Classroom::first() ?? Classroom::create([
            'name' => 'XII MIPA 1',
            'grade_level' => 'XII',
            'major' => 'MIPA',
            'academic_year' => '2026/2027',
        ]);

        Student::create([
            'user_id' => $user->id,
            'nisn' => '9988776655',
            'nis' => '1205',
            'gender' => 'L',
            'classroom_id' => $classroom->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/student/grades');
        $response->assertStatus(200);
    }
}
