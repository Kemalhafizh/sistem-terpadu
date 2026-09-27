<?php

namespace Tests\Feature\ExtendedDiamond;

use App\Models\Student;
use App\Models\TahfidzLog;
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

    public function test_student_mobile_hub_shows_latest_tahfidz_setoran(): void
    {
        $student = Student::factory()->create();
        $teacher = User::factory()->create(['role' => 'guru']);

        foreach ([['An-Naba', '2026-09-01', 'B'], ['Al-Mulk', '2026-09-20', 'A']] as [$surah, $date, $grade]) {
            TahfidzLog::create([
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'surah_number' => 1,
                'surah_name' => $surah,
                'from_ayat' => 1,
                'to_ayat' => 10,
                'grade' => $grade,
                'date' => $date,
            ]);
        }

        $response = $this->actingAs($student->user)->getJson(route('mobile.student.hub'));

        $response->assertStatus(200)
            ->assertJsonPath('data.latest_tahfidz', [
                'surah' => 'Al-Mulk',
                'ayah' => 'Ayat 1 - 10',
                'score' => 'A',
            ]);

        $this->actingAs($student->user)->get(route('mobile.student.hub'))
            ->assertStatus(200)
            ->assertSee('Al-Mulk');
    }

    public function test_parent_mobile_quick_actions_returns_200(): void
    {
        $parent = User::factory()->create(['role' => 'parent']);

        $response = $this->actingAs($parent)->get(route('mobile.parent.actions'));

        $response->assertStatus(200);
        $response->assertSee('Aksi Cepat Orang Tua');
    }
}
