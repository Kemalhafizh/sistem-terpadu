<?php

namespace Tests\Feature\Api;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Evaluation360Cycle;
use App\Models\Evaluation360Instrument;
use App\Models\OsisCandidate;
use App\Models\OsisElection;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EvaluationMobileApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $studentUser;

    protected Student $student;

    protected User $teacherUser;

    protected AcademicYear $academicYear;

    protected Evaluation360Cycle $activeCycle;

    protected Evaluation360Instrument $studentInstrument;

    protected OsisElection $activeElection;

    protected OsisCandidate $candidate1;

    protected OsisCandidate $candidate2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);

        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->teacherUser = User::factory()->create(['role' => 'guru', 'name' => 'Budi Guru']);
        $this->studentUser = User::factory()->create(['role' => 'student']);

        $classroom = Classroom::factory()->create(['academic_year' => '2026/2027']);

        $this->student = Student::factory()->create([
            'user_id' => $this->studentUser->id,
            'classroom_id' => $classroom->id,
        ]);

        // Budi teaches the student's class this year: only such teachers can be rated.
        Schedule::factory()->create(['academic_year_id' => $this->academicYear->id, 'classroom_id' => $classroom->id, 'teacher_id' => $this->teacherUser->id]);

        // Setup Evaluation Cycle
        $this->activeCycle = Evaluation360Cycle::create([
            'title' => 'Evaluasi Semester Ganjil',
            'academic_year' => '2026/2027',
            'semester' => 'Ganjil',
            'status' => 'active',
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(10),
            'created_by' => $this->adminUser->id,
        ]);

        $this->studentInstrument = Evaluation360Instrument::create([
            'cycle_id' => $this->activeCycle->id,
            'evaluator_type' => 'student',
            'questions' => ['Bagaimana cara guru mengajar?'],
            'scale_type' => 'likert5',
            'is_anonymous' => true,
        ]);

        // Setup OSIS Election
        $this->activeElection = OsisElection::create([
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Pemilihan Ketua OSIS 2026/2027',
            'voting_start' => now()->subDay(),
            'voting_end' => now()->addDays(2),
            'status' => 'active',
        ]);

        $this->candidate1 = OsisCandidate::create([
            'election_id' => $this->activeElection->id,
            'student_id' => $this->student->id,
            'candidate_number' => 1,
            'vision' => 'OSIS Hebat',
        ]);

        $this->candidate2 = OsisCandidate::create([
            'election_id' => $this->activeElection->id,
            'student_id' => Student::factory()->create(['classroom_id' => $classroom->id])->id,
            'candidate_number' => 2,
            'vision' => 'OSIS Keren',
        ]);
    }

    public function test_anonimitas_response_evaluasi_guru_terjaga()
    {
        Sanctum::actingAs($this->studentUser);

        $response = $this->postJson('/api/v1/evaluations/teachers/submit', [
            'teacher_id' => $this->teacherUser->id,
            'rating' => 4,
            'comments' => 'Bagus sekali cara mengajarnya',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Evaluasi guru berhasil disubmit secara anonim.',
            ]);

        $this->assertDatabaseHas('evaluation_360_responses', [
            'evaluatee_id' => $this->teacherUser->id,
            'evaluator_id' => null, // Harus null karena anonim!
        ]);
    }

    public function test_double_vote_prevention_pada_e_voting_osis()
    {
        Sanctum::actingAs($this->studentUser);

        // Suara pertama
        $response1 = $this->postJson('/api/v1/evaluations/osis-election/vote', [
            'election_id' => $this->activeElection->id,
            'candidate_id' => $this->candidate1->id,
            'biometric_signature' => 'valid_hash_from_android_biometric_prompt',
        ]);

        $response1->assertStatus(200);

        // Percobaan double vote
        $response2 = $this->postJson('/api/v1/evaluations/osis-election/vote', [
            'election_id' => $this->activeElection->id,
            'candidate_id' => $this->candidate2->id,
            'biometric_signature' => 'valid_hash_from_android_biometric_prompt',
        ]);

        $response2->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertStringContainsString('Double Vote Prevention', $response2->json('message'));

        $this->assertDatabaseCount('osis_votes', 1);
    }

    public function test_validasi_periode_evaluasi_hanya_aktif_saat_cycle_dibuka()
    {
        Sanctum::actingAs($this->studentUser);

        // Ubah cycle jadi closed
        $this->activeCycle->update(['status' => 'completed']);

        $response = $this->postJson('/api/v1/evaluations/teachers/submit', [
            'teacher_id' => $this->teacherUser->id,
            'rating' => 5,
        ]);

        $response->assertStatus(400);
        $this->assertStringContainsString('Tidak ada periode evaluasi yang aktif', $response->json('message'));
    }

    public function test_osis_vote_ditolak_jika_di_luar_periode_voting()
    {
        Sanctum::actingAs($this->studentUser);

        // Ubah jadwal voting ke masa depan
        $this->activeElection->update([
            'voting_start' => now()->addDays(5),
            'voting_end' => now()->addDays(10),
        ]);

        $response = $this->postJson('/api/v1/evaluations/osis-election/vote', [
            'election_id' => $this->activeElection->id,
            'candidate_id' => $this->candidate1->id,
            'biometric_signature' => 'hash',
        ]);

        $response->assertStatus(400);
        $this->assertStringContainsString('Saat ini bukan masa voting', $response->json('message'));
    }
}
