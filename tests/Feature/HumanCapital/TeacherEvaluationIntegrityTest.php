<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Evaluation360Cycle;
use App\Models\Evaluation360Instrument;
use App\Models\Evaluation360Response;
use App\Models\Evaluation360Summary;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\HumanCapital\Evaluation360Service;
use Laravel\Sanctum\Sanctum;

/*
 * 119.9 teacher evaluation. A student was offered every teacher in the
 * school, each labelled "Guru Mata Pelajaran". The 360° summary filled empty
 * perspectives with 4.2/4.1/4.5/4.3/4.0, stored the same "strengths" for
 * every teacher, halved student ratings (the respondent hash was summed in),
 * and never included them. The web routes had no role: anyone could open a
 * cycle, rate as "supervisor", or read any teacher's results.
 */

beforeEach(function () {
    $this->year = AcademicYear::factory()->create(['is_active' => true]);
    $this->classroom = Classroom::factory()->create();
    $this->studentUser = User::factory()->create(['role' => 'siswa']);
    Student::factory()->create(['user_id' => $this->studentUser->id, 'classroom_id' => $this->classroom->id]);

    $this->math = User::factory()->create(['role' => 'guru', 'name' => 'Pak Matematika']);
    $this->other = User::factory()->create(['role' => 'guru', 'name' => 'Bu Kelas Lain']);
    Schedule::factory()->create(['academic_year_id' => $this->year->id, 'classroom_id' => $this->classroom->id, 'teacher_id' => $this->math->id, 'subject_id' => Subject::factory()->create(['name' => 'Matematika'])->id]);
    Schedule::factory()->create(['academic_year_id' => $this->year->id, 'classroom_id' => $this->classroom->id, 'teacher_id' => $this->math->id, 'subject_id' => Subject::factory()->create(['name' => 'Informatika'])->id]);
    Schedule::factory()->create(['academic_year_id' => $this->year->id, 'teacher_id' => $this->other->id]);

    $this->principal = User::factory()->create(['role' => 'kepala_sekolah']);
    $this->cycle = app(Evaluation360Service::class)->createCycle(['title' => 'Evaluasi Ganjil', 'academic_year' => '2026/2027', 'semester' => 'ganjil'], $this->principal->id);
    foreach (['supervisor', 'peer', 'student', 'parent', 'self'] as $type) {
        app(Evaluation360Service::class)->createInstrument($this->cycle, ['evaluator_type' => $type]);
    }
});

test('siswa hanya melihat guru di kelasnya, dengan mapel dari jadwal', function () {
    Sanctum::actingAs($this->studentUser);

    // Android reads {id, name, subject}.
    $this->getJson('/api/v1/evaluations/teachers')
        ->assertOk()
        ->assertExactJson([['id' => $this->math->id, 'name' => 'Pak Matematika', 'subject' => 'Informatika, Matematika']]);
});

test('siswa tidak bisa menilai guru yang tidak mengajar di kelasnya', function () {
    Sanctum::actingAs($this->studentUser);

    $this->postJson('/api/v1/evaluations/teachers/submit', ['teacher_id' => $this->other->id, 'rating' => 1])
        ->assertStatus(400)
        ->assertJsonPath('success', false);

    expect(Evaluation360Response::count())->toBe(0);
});

test('penilaian siswa masuk ringkasan dengan nilai utuh, perspektif kosong tetap kosong', function () {
    Sanctum::actingAs($this->studentUser);
    $this->postJson('/api/v1/evaluations/teachers/submit', ['teacher_id' => $this->math->id, 'rating' => 4])->assertOk();

    $summary = Evaluation360Summary::where('evaluatee_id', $this->math->id)->sole();

    // Was 2.0 (the hash was summed in) and never reached the summary; the rest were 4.2/4.1/4.3/4.0.
    expect($summary->student_avg)->toBe(4.0)
        ->and($summary->supervisor_avg)->toBeNull()
        ->and($summary->peer_avg)->toBeNull()
        ->and($summary->parent_avg)->toBeNull()
        ->and($summary->self_score)->toBeNull()
        ->and($summary->overall_score)->toBe(4.0)
        ->and($summary->strengths)->toBeNull();
});

test('skor keseluruhan hanya menimbang perspektif yang ada', function () {
    $service = app(Evaluation360Service::class);
    $instrument = fn (string $type) => Evaluation360Instrument::where('cycle_id', $this->cycle->id)->where('evaluator_type', $type)->sole();
    $service->submitResponse($instrument('supervisor'), $this->math->id, $this->principal->id, ['a' => 5, 'b' => 3]);
    $service->submitResponse($instrument('student'), $this->math->id, null, ['rating' => 2, 'respondent_hash' => 'abc']);

    $summary = $service->generateSummary($this->cycle->id, $this->math->id);

    // (4 × 0.3 + 2 × 0.3) / 0.6
    expect($summary->supervisor_avg)->toBe(4.0)
        ->and($summary->student_avg)->toBe(2.0)
        ->and($summary->overall_score)->toBe(3.0);
});

test('perspektif penilai ditentukan dari akun, bukan dipilih di form', function () {
    $peer = User::factory()->create(['role' => 'guru']);

    $this->actingAs($peer)->post(route('hc.evaluation.submit', $this->cycle->id), [
        'evaluatee_id' => $this->math->id, 'evaluator_type' => 'supervisor', 'scores' => ['a' => 5],
    ])->assertSessionHas('success');
    $this->actingAs($this->principal)->post(route('hc.evaluation.submit', $this->cycle->id), [
        'evaluatee_id' => $this->math->id, 'scores' => ['a' => 3], 'comments' => 'Perlu variasi metode',
    ])->assertSessionHas('success');
    $this->actingAs($this->math)->post(route('hc.evaluation.submit', $this->cycle->id), [
        'evaluatee_id' => $this->math->id, 'scores' => ['a' => 4],
    ])->assertSessionHas('success');

    $types = Evaluation360Response::with('instrument')->get()->mapWithKeys(fn ($r) => [$r->evaluator_id => $r->instrument->evaluator_type]);
    expect($types[$peer->id])->toBe('peer')
        ->and($types[$this->principal->id])->toBe('supervisor')
        ->and($types[$this->math->id])->toBe('self');

    // One rating per person per cycle.
    $this->actingAs($peer)->post(route('hc.evaluation.submit', $this->cycle->id), ['evaluatee_id' => $this->math->id, 'scores' => ['a' => 1]])
        ->assertSessionHas('error');

    $this->actingAs($this->principal)->get(route('hc.evaluation.results', $this->math->id))
        ->assertOk()
        ->assertSee('Perlu variasi metode')
        ->assertDontSee('Penguasaan materi');
});

test('hasil guru hanya untuk pimpinan dan guru itu sendiri', function () {
    $this->actingAs($this->math)->get(route('hc.evaluation.results', $this->math->id))->assertOk();
    $this->actingAs($this->other)->get(route('hc.evaluation.results', $this->math->id))->assertForbidden();
    $this->actingAs($this->other)->get(route('hc.evaluation.ranking'))->assertForbidden();
    $this->actingAs($this->principal)->get(route('hc.evaluation.ranking'))->assertOk();

    // No data yet: dashes, not 4.2 / 5.0.
    $this->actingAs($this->principal)->get(route('hc.evaluation.results', $this->other->id))
        ->assertOk()->assertDontSee('4.2 / 5.0')->assertDontSee('>4.5<', false)->assertSee('Belum ada komentar dari penilai.');
});

test('siswa, orang tua, dan guru tidak bisa membuka siklus evaluasi baru', function (string $role) {
    $this->actingAs(User::factory()->create(['role' => $role]))
        ->post(route('hc.evaluation.store_cycle'), ['title' => 'x', 'academic_year' => '2026/2027', 'semester' => 'ganjil'])
        ->assertForbidden();

    expect(Evaluation360Cycle::count())->toBe(1);
})->with(['siswa', 'orang_tua', 'guru']);

test('siswa tidak bisa membuka halaman evaluasi 360 di web', function () {
    $this->actingAs($this->studentUser)->get(route('hc.evaluation.cycles'))->assertForbidden();
    $this->actingAs($this->studentUser)->get(route('hc.evaluation.results', $this->math->id))->assertForbidden();
});
