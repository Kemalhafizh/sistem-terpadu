<?php

use App\Models\AcademicYear;
use App\Models\AlumniData;
use App\Models\Exam;
use App\Models\FoundationUnit;
use App\Models\OsisTransaction;
use App\Models\PlcGroup;
use App\Models\QuestionBank;
use App\Models\StaffRotation;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Syllabus;
use App\Models\TracerResponse;
use App\Models\UnitAccreditationRecord;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/*
 * Pages that answered HTTP 500 when every GET route was opened in a browser
 * (plus the routed pages whose views didn't exist at all).
 */

beforeEach(function () {
    Cache::flush();
    $this->admin = User::factory()->create(['role' => 'admin']);
});

function unitNamed(string $name): FoundationUnit
{
    return FoundationUnit::create(['unit_code' => strtoupper(substr($name, 0, 3)).random_int(10, 99), 'unit_name' => $name, 'level' => 'sma']);
}

test('dashboard wadiah menautkan halaman merchant yang benar', function () {
    $this->actingAs($this->admin)->get(route('admin.finance.wadiah.dashboard'))
        ->assertOk()
        ->assertSee(route('admin.finance.canteen.merchants'), false);
});

test('halaman kurikulum tanpa tahun ajaran aktif mengarahkan dengan pesan, bukan 500', function () {
    foreach (['curriculum.syllabi.index', 'curriculum.lesson-plans.index', 'curriculum.report'] as $route) {
        $this->actingAs($this->admin)->get(route($route))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', 'Tahun akademik aktif tidak ditemukan.');
    }
});

test('silabus bisa diunduh dari bank silabus', function () {
    Storage::fake('public');
    Storage::disk('public')->put('syllabi/matematika.pdf', '%PDF-1.4 silabus');
    $syllabus = Syllabus::create([
        'subject_id' => Subject::factory()->create(['name' => 'Matematika'])->id,
        'academic_year_id' => AcademicYear::factory()->create(['is_active' => true])->id,
        'grade_level' => 'X', 'semester' => 'ganjil', 'uploaded_by' => $this->admin->id, 'file_path' => 'syllabi/matematika.pdf', 'status' => 'draft',
    ]);

    $this->actingAs($this->admin)->get(route('curriculum.syllabi.index'))->assertOk()->assertSee(route('curriculum.syllabi.download', $syllabus), false);
    $response = $this->get(route('curriculum.syllabi.download', $syllabus))->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('Silabus_matematika_kelas_x_ganjil.pdf');
});

test('pratinjau tracer study admin tidak lagi membuat data palsu', function () {
    // Used to call Student::factory() / AlumniData::factory() (dev-only classes) in production.
    // The alumni form is role:student; an admin reads results on the analytics page instead.
    $this->actingAs($this->admin)->get(route('alumni.tracer-study.form'))->assertForbidden();
    expect(Student::count())->toBe(0)->and(AlumniData::count())->toBe(0);

    $this->post(route('alumni.tracer-study.submit'), [])->assertForbidden();
});

test('alumni hanya bisa mengubah jawaban tracer study miliknya', function () {
    $makeAlumnus = function () {
        $user = User::factory()->create(['role' => 'student']);
        $student = Student::factory()->create(['user_id' => $user->id, 'status' => 'alumni']);
        $alumni = AlumniData::create(['student_id' => $student->id, 'graduation_year' => 2025, 'current_activity' => 'study']);
        $response = TracerResponse::create(['alumni_data_id' => $alumni->id, 'relevance_score' => 4, 'satisfaction_score' => 4]);

        return [$user, $response];
    };
    [$alice, $aliceResponse] = $makeAlumnus();
    [, $bobResponse] = $makeAlumnus();

    $this->actingAs($alice)->get(route('alumni.tracer-study.edit', $aliceResponse->uuid))->assertOk();
    $this->get(route('alumni.tracer-study.edit', $bobResponse->uuid))->assertForbidden();
    $this->put(route('alumni.tracer-study.update', $bobResponse->uuid), ['current_activity' => 'work', 'relevance_score' => 1, 'satisfaction_score' => 1])
        ->assertForbidden();
    expect($bobResponse->fresh()->relevance_score)->toBe(4);
});

test('forum dan cek ketersediaan booking tidak lagi error', function () {
    // forum/create calls auth()->user()->isParent(), which didn't exist.
    $this->actingAs($this->admin)->get(route('forum.create'))->assertOk();
    $this->getJson(route('resource_bookings.check'))->assertStatus(422)->assertJsonValidationErrors(['resource_id', 'start', 'end']);

    expect(User::factory()->make(['role' => 'orang_tua'])->isParent())->toBeTrue()
        ->and(User::factory()->make(['role' => 'guru'])->isParent())->toBeFalse();
});

test('dokumen API: spesifikasi yang belum dibuat menjawab 404, yang ada dilayani', function () {
    Storage::fake('local');
    $this->get('/docs.openapi')->assertNotFound();
    $this->get('/docs.postman')->assertNotFound();

    Storage::disk('local')->put('scribe/openapi.yaml', "openapi: 3.0.3\n");
    Storage::disk('local')->put('scribe/collection.json', '{"info":{"name":"API"}}');
    expect($this->get('/docs.openapi')->assertOk()->baseResponse->getFile()->getContent())->toBe("openapi: 3.0.3\n");
    $this->get('/docs.postman')->assertOk()->assertJsonPath('info.name', 'API');
});

test('template Scribe lokal tidak memuat CDN, jadi scribe:generate tetap bebas CDN', function () {
    $theme = file_get_contents(resource_path('views/vendor/scribe/themes/default/index.blade.php'));

    expect($theme)->not->toMatch('#<script[^>]+src="https?://#')
        ->toContain('<x-vendor.script lib="lodash" />')
        ->toContain('@verbatim');
});

test('transaksi kas OSIS bisa diedit', function () {
    $transaction = OsisTransaction::create(['type' => 'income', 'amount' => 50000, 'description' => 'Iuran', 'category' => 'Iuran', 'transaction_date' => '2026-09-01', 'recorded_by' => $this->admin->id]);

    $this->actingAs($this->admin)->get(route('osis.transactions.edit', $transaction->id))->assertOk()->assertSee('Iuran');
    $this->put(route('osis.transactions.update', $transaction->id), [
        'type' => 'expense', 'amount' => 75000, 'description' => 'Beli bola', 'category' => 'Perlengkapan', 'transaction_date' => '2026-09-02',
    ])->assertRedirect(route('osis.dashboard'));

    expect($transaction->fresh())->type->toBe('expense')->description->toBe('Beli bola');
});

test('ujian bisa diedit, termasuk mematikan acak soal', function () {
    $exam = Exam::factory()->create(['created_by' => $this->admin->id, 'shuffle_questions' => true, 'shuffle_options' => true, 'is_secure_mode' => true]);

    $this->actingAs($this->admin)->get(route('cbt.exams.show', $exam))->assertSee(route('cbt.exams.edit', $exam), false);
    $this->get(route('cbt.exams.edit', $exam))->assertOk()->assertSee($exam->title);

    // Unchecked boxes are sent as the hidden "0"; before, update() defaulted a
    // missing checkbox to true, so shuffling could never be switched off.
    $this->put(route('cbt.exams.update', $exam), [
        'title' => 'UTS Revisi', 'subject_id' => $exam->subject_id, 'academic_year_id' => $exam->academic_year_id, 'type' => 'uts',
        'start_time' => '2026-10-01 07:00', 'end_time' => '2026-10-01 09:00', 'duration_minutes' => 90, 'passing_score' => 75, 'max_violations' => 3,
        'shuffle_questions' => '0', 'shuffle_options' => '1', 'is_secure_mode' => '0',
    ])->assertRedirect(route('cbt.exams.show', $exam));

    expect($exam->fresh())->title->toBe('UTS Revisi')->shuffle_questions->toBeFalse()->shuffle_options->toBeTrue()->is_secure_mode->toBeFalse();
});

test('detail soal bank soal bisa dibuka', function () {
    $question = QuestionBank::create([
        'subject_id' => Subject::factory()->create()->id, 'created_by' => $this->admin->id, 'question_text' => 'Hasil dari 2 + 2 adalah?',
        'type' => 'multiple_choice', 'options' => ['A' => '3', 'B' => '4'], 'correct_answer' => 'B', 'difficulty' => 'easy', 'grade_level' => 'X',
    ]);

    $this->actingAs($this->admin)->get(route('cbt.question-banks.index'))->assertSee(route('cbt.question-banks.show', $question), false);
    $this->get(route('cbt.question-banks.show', $question))->assertOk()->assertSee('Hasil dari 2 + 2 adalah?')->assertSee('Kunci');
});

test('modul yayasan hanya untuk pimpinan, dan akreditasi serta rotasi berfungsi', function () {
    $this->actingAs(User::factory()->create(['role' => 'guru']))->get(route('foundation.accreditation.index'))->assertForbidden();

    $head = User::factory()->create(['role' => 'kepala_sekolah']);
    [$sma, $smp] = [unitNamed('SMA Sultan Agung 1'), unitNamed('SMP Sultan Agung 4')];

    $this->actingAs($head)->get(route('foundation.accreditation.create'))->assertOk()->assertSee('SMA Sultan Agung 1');
    $this->post(route('foundation.accreditation.store'), ['foundation_unit_id' => $sma->id])->assertSessionHasErrors(['accreditation_body', 'valid_from']);
    $this->post(route('foundation.accreditation.store'), [
        'foundation_unit_id' => $sma->id, 'accreditation_body' => 'BAN-PDM', 'accreditation_grade' => 'A', 'accreditation_score' => 95,
        'valid_from' => '2024-01-01', 'valid_until' => '2029-01-01', 'status' => 'active',
    ])->assertRedirect(route('foundation.accreditation.show', UnitAccreditationRecord::sole()->id));
    $this->get(route('foundation.accreditation.index'))->assertOk()->assertSee('BAN-PDM');

    $teacher = User::factory()->create(['role' => 'guru', 'name' => 'Pak Budi']);
    $this->post(route('foundation.rotation.store'), [
        'user_id' => $teacher->id, 'from_unit_id' => $sma->id, 'to_unit_id' => $sma->id, 'rotation_type' => 'mutasi_tetap', 'effective_date' => '2026-10-01', 'reason' => 'x',
    ])->assertSessionHasErrors('to_unit_id');
    $this->post(route('foundation.rotation.store'), [
        'user_id' => $teacher->id, 'from_unit_id' => $sma->id, 'to_unit_id' => $smp->id, 'rotation_type' => 'mutasi_tetap', 'effective_date' => '2026-10-01', 'reason' => 'Pemerataan guru',
    ])->assertRedirect();

    // store() used to redirect to foundation.rotation.index, which didn't exist
    // (the routes were named foundation.rotation.rotation.*).
    $this->get(route('foundation.rotation.show', StaffRotation::sole()->id))->assertOk()->assertSee('Pak Budi')->assertSee('SMP Sultan Agung 4');
});

test('komunitas praktisi bisa dibuat dan dibuka', function () {
    $teacher = User::factory()->create(['role' => 'guru']);

    $this->actingAs($teacher)->get(route('knowledge.plc.create'))->assertOk();
    $this->post(route('knowledge.plc.store'), ['name' => 'MGMP Matematika', 'type' => 'rumpun_mapel'])->assertRedirect();

    $group = PlcGroup::sole();
    expect($group->coordinator_id)->toBe($teacher->id)->and($group->members()->count())->toBe(1);
    $this->get(route('knowledge.plc.index'))->assertOk()->assertSee('MGMP Matematika');
    $this->get(route('knowledge.plc.show', $group->id))->assertOk()->assertSee('Koordinator');
});

test('link pratinjau berita di CMS memakai route yang ada', function () {
    $this->actingAs($this->admin)->get(route('cms.posts.index'))->assertOk();
});
