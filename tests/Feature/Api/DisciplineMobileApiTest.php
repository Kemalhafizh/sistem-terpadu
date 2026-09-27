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

        // ApiResponseTrait wraps the payload in {success, message, data}.
        $response->assertStatus(200)
            ->assertJsonPath('data.total_points', 70)
            ->assertJsonPath('data.status', 'SP1 (Waspada)') // >= 50
            ->assertJsonPath('data.next_warning_level', 'SP2')
            ->assertJsonPath('data.next_warning_threshold', 75);
    }

    public function test_ringkasan_memisahkan_poin_pelanggaran_dan_kebaikan_serta_aturan_nyata()
    {
        Sanctum::actingAs($this->studentUser);

        $reward = PointCategory::create([
            'name' => 'Juara lomba tingkat kota',
            'type' => 'reward',
            'default_points' => -25, // rewards are stored negative
            'is_active' => true,
        ]);
        PointCategory::create(['name' => 'Kategori nonaktif', 'type' => 'penalty', 'default_points' => 5, 'is_active' => false]);

        foreach ([[40, 'penalty'], [15, 'penalty'], [-25, 'reward']] as [$points, $type]) {
            StudentPointLog::create([
                'student_id' => $this->student->id,
                'point_category_id' => $type === 'reward' ? $reward->id : $this->violationCategory->id,
                'created_by' => $this->parentUser->id,
                'points' => $points,
                'type' => $type,
                'description' => 'uji',
            ]);
        }

        $data = $this->getJson('/api/v1/discipline/summary')->assertOk()->json('data');

        // Net balance 30 drives the status; the split is reported as-is.
        $this->assertSame(30, $data['total_points']);
        $this->assertSame('Perlu Perhatian', $data['status']);
        $this->assertSame(55, $data['total_violation_points']);
        $this->assertSame(25, $data['total_reward_points']);
        $this->assertSame(2, $data['total_cases']);
        $this->assertSame('SP1', $data['next_warning_level']);
        $this->assertSame(50, $data['next_warning_threshold']);
        $this->assertNotNull($data['last_updated']);

        // Only active categories, points as positive magnitudes.
        $this->assertEqualsCanonicalizing(
            [
                ['name' => 'Juara lomba tingkat kota', 'type' => 'reward', 'points' => 25],
                ['name' => 'Merokok di area sekolah', 'type' => 'penalty', 'points' => 50],
            ],
            $data['rules']
        );
    }

    public function test_ringkasan_tanpa_catatan_poin_berstatus_aman_dan_sp3_tidak_punya_level_berikutnya()
    {
        Sanctum::actingAs($this->studentUser);

        $empty = $this->getJson('/api/v1/discipline/summary')->assertOk()->json('data');
        $this->assertSame(0, $empty['total_points']);
        $this->assertSame('Aman', $empty['status']);
        $this->assertNull($empty['last_updated']);

        StudentPointLog::create([
            'student_id' => $this->student->id,
            'point_category_id' => $this->violationCategory->id,
            'created_by' => $this->parentUser->id,
            'points' => 100,
            'type' => 'penalty',
            'description' => 'Pelanggaran sangat berat',
        ]);

        $full = $this->getJson('/api/v1/discipline/summary')->assertOk()->json('data');
        $this->assertSame('SP3 (Sangat Kritis)', $full['status']);
        $this->assertNull($full['next_warning_level']);
        $this->assertNull($full['next_warning_threshold']);
    }

    public function test_riwayat_mobile_berupa_daftar_datar_dan_tidak_error()
    {
        Sanctum::actingAs($this->studentUser);

        StudentPointLog::create([
            'student_id' => $this->student->id,
            'point_category_id' => $this->violationCategory->id,
            'created_by' => $this->parentUser->id,
            'points' => 20,
            'type' => 'penalty',
            'description' => 'Terlambat',
        ]);
        // Automatic log without a category, like AttendanceService's Alpa penalty.
        StudentPointLog::create([
            'student_id' => $this->student->id,
            'created_by' => $this->parentUser->id,
            'points' => 10,
            'type' => 'penalty',
            'description' => 'Bolos',
        ]);

        // Used to be a 400 on every call: getCollection() on a plain Collection.
        $response = $this->getJson('/api/v1/discipline/history')->assertOk();

        $rows = $response->json('data');
        $this->assertCount(2, $rows);
        $this->assertTrue(array_is_list($rows));
        $byDescription = collect($rows)->keyBy('description');
        $this->assertSame('Merokok di area sekolah', $byDescription['Terlambat']['category_name']);
        $this->assertNull($byDescription['Bolos']['category_name']);
        $this->assertSame('penalty', $byDescription['Bolos']['type']);
        $this->assertSame($this->parentUser->name, $byDescription['Bolos']['recorded_by']);
    }

    public function test_hanya_orang_tua_siswa_yang_ditawari_tanda_tangan_sp()
    {
        $letter = WarningLetter::create([
            'student_id' => $this->student->id,
            'level' => 'SP1',
            'point_threshold' => 50,
            'issued_at' => now(),
            'notes' => 'Penerbitan otomatis SP1.',
        ]);

        Sanctum::actingAs($this->studentUser);
        $asStudent = $this->getJson('/api/v1/discipline/warning-letters')->assertOk()->json('data.0');
        $this->assertFalse($asStudent['can_sign']);
        $this->assertFalse($asStudent['is_signed']);
        $this->assertSame('Penerbitan otomatis SP1.', $asStudent['notes']);

        Sanctum::actingAs($this->parentUser);
        $this->assertTrue($this->getJson('/api/v1/discipline/warning-letters')->json('data.0.can_sign'));

        $this->postJson("/api/v1/discipline/warning-letters/{$letter->id}/sign", [
            'digital_signature' => 'data:image/png;base64,iVBORw0KGgo=',
        ])->assertOk();

        $afterSigning = $this->getJson('/api/v1/discipline/warning-letters')->json('data.0');
        $this->assertTrue($afterSigning['is_signed']);
        $this->assertFalse($afterSigning['can_sign']);
        $this->assertNotNull($afterSigning['signed_at']);
    }

    public function test_orang_tua_dengan_dua_anak_memilih_anak_lewat_student_uuid()
    {
        $secondUser = User::factory()->create(['role' => 'student']);
        $second = Student::factory()->create([
            'user_id' => $secondUser->id,
            'parent_id' => $this->parentUser->id,
            'classroom_id' => $this->student->classroom_id,
        ]);

        StudentPointLog::create([
            'student_id' => $second->id,
            'point_category_id' => $this->violationCategory->id,
            'created_by' => $this->parentUser->id,
            'points' => 60,
            'type' => 'penalty',
            'description' => 'Hanya milik anak kedua',
        ]);

        Sanctum::actingAs($this->parentUser);

        // Without a choice: the first child, and the response says which one.
        $this->getJson('/api/v1/discipline/summary')->assertOk()
            ->assertJsonPath('data.student.uuid', $this->student->uuid)
            ->assertJsonPath('data.total_points', 0);

        // The second child, selected by uuid, across all three endpoints.
        $query = '?student_uuid='.$second->uuid;
        $this->getJson('/api/v1/discipline/summary'.$query)->assertOk()
            ->assertJsonPath('data.student.uuid', $second->uuid)
            ->assertJsonPath('data.student.name', $second->name)
            ->assertJsonPath('data.total_points', 60)
            ->assertJsonPath('data.status', 'SP1 (Waspada)');
        $this->getJson('/api/v1/discipline/history'.$query)->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.description', 'Hanya milik anak kedua');
        $letters = $this->getJson('/api/v1/discipline/warning-letters'.$query)->assertOk();
        $letters->assertJsonPath('data.0.level', 'SP1')->assertJsonPath('data.0.can_sign', true);

        // …and the first child's letters stay empty.
        $this->getJson('/api/v1/discipline/warning-letters?student_uuid='.$this->student->uuid)
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_student_uuid_anak_orang_lain_tidak_bisa_dibuka()
    {
        $stranger = Student::factory()->create(); // no relation to this parent
        Sanctum::actingAs($this->parentUser);

        $this->getJson('/api/v1/discipline/summary?student_uuid='.$stranger->uuid)->assertStatus(404);
        $this->getJson('/api/v1/discipline/history?student_uuid='.$stranger->uuid)->assertStatus(404);

        // A student always gets their own record, whatever uuid is sent.
        Sanctum::actingAs($this->studentUser);
        $this->getJson('/api/v1/discipline/summary?student_uuid='.$stranger->uuid)->assertOk()
            ->assertJsonPath('data.student.uuid', $this->student->uuid);
    }

    public function test_tanda_tangan_dengan_nama_field_lama_dari_aplikasi_ditolak()
    {
        $letter = WarningLetter::create([
            'student_id' => $this->student->id,
            'level' => 'SP1',
            'point_threshold' => 50,
            'issued_at' => now(),
        ]);
        Sanctum::actingAs($this->parentUser);

        // The shape the Android app used to send — never accepted.
        $this->postJson("/api/v1/discipline/warning-letters/{$letter->id}/sign", [
            'letter_id' => $letter->id,
            'signature_data' => 'abc',
            'parent_name' => 'Bapak',
            'parent_phone' => '0812',
        ])->assertStatus(422)->assertJsonValidationErrors('digital_signature');
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
