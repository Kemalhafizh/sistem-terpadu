<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase82MobileAnalyticsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_attendance_summary_and_points(): void
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

        $attRes = $this->actingAs($user, 'sanctum')->getJson('/api/v1/student/attendance');
        $attRes->assertStatus(200);

        $pointRes = $this->actingAs($user, 'sanctum')->getJson('/api/v1/student/points');
        $pointRes->assertStatus(200);
    }
}
