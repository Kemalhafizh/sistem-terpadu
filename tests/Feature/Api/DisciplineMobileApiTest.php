<?php

namespace Tests\Feature\Api;

use App\Models\Classroom;
use App\Models\PointCategory;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\User;
use App\Models\WarningLetter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DisciplineMobileApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $parentUser;
    protected User $studentUser;
    protected Student $student;
    protected PointCategory $violationCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parentUser = User::factory()->create(['role' => 'parent']);
        $this->studentUser = User::factory()->create(['role' => 'student']);
        
        $classroom = Classroom::factory()->create(['academic_year' => '2026/2027']);
        
        $this->student = Student::factory()->create([
            'user_id' => $this->studentUser->id,
            'parent_id' => $this->parentUser->id,
            'classroom_id' => $classroom->id,
        ]);

        $this->violationCategory = PointCategory::create([
            'name' => 'Merokok di area sekolah',
            'type' => 'penalty',
            'default_points' => 50,
            'is_active' => true,
        ]);
    }

    public function test_penghitungan_saldo_poin_akumulatif_akurat()
    {
        Sanctum::actingAs($this->studentUser);

        // Tambah 2 log pelanggaran: 50 + 20 = 70
        StudentPointLog::create([
            'student_id' => $this->student->id,
            'point_category_id' => $this->violationCategory->id,
            'created_by' => $this->parentUser->id,
            'points' => 50,
            'type' => 'penalty',
            'description' => 'Ketahuan merokok',
        ]);

        StudentPointLog::create([
            'student_id' => $this->student->id,
            'point_category_id' => $this->violationCategory->id,
            'created_by' => $this->parentUser->id,
            'points' => 20,
            'type' => 'penalty',
            'description' => 'Terlambat',
        ]);

        $response = $this->getJson('/api/v1/discipline/summary');

        $response->assertStatus(200)
            ->assertJson([
                'total_points' => 70,
                'status' => 'SP1 (Waspada)', // >= 50
            ]);
    }

    public function test_auto_generate_sp_ketika_ambang_batas_terlampaui()
    {
        // 0 points -> 50 points -> SP1
        StudentPointLog::create([
            'student_id' => $this->student->id,
            'point_category_id' => $this->violationCategory->id,
            'created_by' => $this->parentUser->id,
            'points' => 50,
            'type' => 'penalty',
            'description' => 'Pelanggaran berat',
        ]);

        $this->assertDatabaseHas('warning_letters', [
            'student_id' => $this->student->id,
            'level' => 'SP1',
            'point_threshold' => 50,
        ]);

        // Add 25 more points -> total 75 -> SP2
        StudentPointLog::create([
            'student_id' => $this->student->id,
            'point_category_id' => $this->violationCategory->id,
            'created_by' => $this->parentUser->id,
            'points' => 25,
            'type' => 'penalty',
            'description' => 'Pelanggaran lagi',
        ]);

        $this->assertDatabaseHas('warning_letters', [
            'student_id' => $this->student->id,
            'level' => 'SP2',
            'point_threshold' => 75,
        ]);
    }

    public function test_otorisasi_ttd_digital_hanya_parent_yang_sah_yang_bisa_sign()
    {
        // Create an SP
        $letter = WarningLetter::create([
            'student_id' => $this->student->id,
            'level' => 'SP1',
            'point_threshold' => 50,
            'issued_at' => now(),
        ]);

        // Test with unauthorized parent
        $otherParent = User::factory()->create(['role' => 'parent']);
        Sanctum::actingAs($otherParent);

        $response = $this->postJson("/api/v1/discipline/warning-letters/{$letter->id}/sign", [
            'digital_signature' => 'fake_base64_signature',
        ]);

        $response->assertStatus(403);
        $this->assertStringContainsString('bukan orang tua yang terdaftar', $response->json('message'));

        // Test with authorized parent
        Sanctum::actingAs($this->parentUser);

        $response2 = $this->postJson("/api/v1/discipline/warning-letters/{$letter->id}/sign", [
            'digital_signature' => 'valid_base64_signature',
        ]);

        $response2->assertStatus(200);
        $this->assertDatabaseHas('warning_letters', [
            'id' => $letter->id,
            'signed_by_parent_id' => $this->parentUser->id,
            'digital_signature' => 'valid_base64_signature',
        ]);
    }
}
