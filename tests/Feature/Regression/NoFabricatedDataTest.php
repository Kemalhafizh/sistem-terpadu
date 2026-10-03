<?php

use App\Models\Billing;
use App\Models\BlockchainCredential;
use App\Models\CatSession;
use App\Models\Employee;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\IrtItemParameter;
use App\Models\IrtStudentAbility;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\User;
use App\Services\Adaptive\CatEngineService;
use App\Services\Adaptive\IrtCalibrationService;
use App\Services\Blockchain\BlockchainCredentialService;
use App\Services\ImportExportService;
use App\Services\PredictiveAnalyticsService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/*
 * These services used to fill their output with rand(): payment trends,
 * radar dimensions, IRT parameters, the next CAT item, block numbers and an
 * initial password. Every value now comes from stored data, or is null.
 */

function bankQuestion(Exam $exam, int $order, string $correct = 'A'): QuestionBank
{
    $question = QuestionBank::create([
        'subject_id' => $exam->subject_id,
        'created_by' => $exam->created_by,
        'question_text' => "Soal {$order}",
        'type' => 'multiple_choice',
        'options' => ['A' => 'a', 'B' => 'b'],
        'correct_answer' => $correct,
        'difficulty' => 'medium',
        'grade_level' => 'X',
    ]);
    $exam->questions()->attach($question->id, ['order' => $order]);

    return $question;
}

function finishedAttempt(Exam $exam, Student $student, array $answers, string $status = 'submitted'): ExamAttempt
{
    return ExamAttempt::create([
        'uuid' => (string) Str::uuid(),
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'answers' => $answers,
        'started_at' => now()->subHour(),
        'submitted_at' => now(),
        'status' => $status,
    ]);
}

test('tren keuangan dihitung dari tagihan, bulan tanpa data null', function () {
    $this->travelTo('2026-10-15');
    Billing::factory()->create(['amount' => 1000000, 'remaining_balance' => 250000, 'due_date' => '2026-08-10']);
    // Billing turns a remaining balance of 0 into the full amount on create, so settle it afterwards.
    Billing::factory()->create(['amount' => 1000000, 'due_date' => '2026-08-20'])->update(['remaining_balance' => 0, 'status' => 'paid']);
    Billing::factory()->create(['amount' => 500000, 'remaining_balance' => 500000, 'due_date' => '2026-09-05']);

    $trend = collect(app(PredictiveAnalyticsService::class)->generateTrendForecast('finance'));
    $byMonth = $trend->keyBy('month');

    expect($trend)->toHaveCount(9)
        ->and($byMonth['2026-08']['actual'])->toBe(87.5)
        ->and($byMonth['2026-09']['actual'])->toBe(0.0)
        ->and($byMonth['2026-04']['actual'])->toBeNull()
        // Projection: average of the real months only.
        ->and($byMonth['2026-10']['forecast'])->toBe(43.8)
        ->and(app(PredictiveAnalyticsService::class)->generateTrendForecast('finance'))->toBe($trend->all());
});

test('tren tanpa tagihan sama sekali tidak memproyeksikan angka', function () {
    $trend = app(PredictiveAnalyticsService::class)->generateTrendForecast('finance');

    expect(array_filter(array_column($trend, 'actual'), fn ($v) => $v !== null))->toBe([])
        ->and(array_filter(array_column($trend, 'forecast'), fn ($v) => $v !== null))->toBe([]);
});

test('halaman analitik keuangan berterus terang saat belum ada tagihan', function () {
    $this->actingAs(User::factory()->create(['role' => 'kepala_sekolah']))
        ->get(route('analytics.financial'))->assertOk()
        ->assertSee('belum ada tren yang bisa dihitung')
        ->assertDontSee('Proyeksi AI');
});

test('metrik tren tanpa sumber data ditolak', function () {
    app(PredictiveAnalyticsService::class)->generateTrendForecast('attendance');
})->throws(InvalidArgumentException::class);

test('radar siswa tidak mengarang dimensi e-learning, tahfidz, dan ekskul', function () {
    $radar = app(PredictiveAnalyticsService::class)->getStudentPerformanceRadar(Student::factory()->create());
    $values = array_combine($radar['labels'], $radar['data']);

    expect($values['E-Learning'])->toBeNull()
        ->and($values['Tahfidz'])->toBeNull()
        ->and($values['Ekstrakurikuler'])->toBeNull();
});

test('kalibrasi IRT memakai jawaban ujian yang sudah selesai', function () {
    $exam = Exam::factory()->create();
    $easy = bankQuestion($exam, 1, 'A');
    $hard = bankQuestion($exam, 2, 'B');
    $unused = bankQuestion($exam, 3, 'A');

    $students = Student::factory()->count(4)->create();
    // Easy: 3 of 4 right. Hard: 1 of 4 right. Unused: never answered.
    finishedAttempt($exam, $students[0], [$easy->id => 'A', $hard->id => 'B']);
    finishedAttempt($exam, $students[1], [$easy->id => 'a', $hard->id => 'A']);
    finishedAttempt($exam, $students[2], [$easy->id => 'A', $hard->id => 'A'], 'timeout');
    finishedAttempt($exam, $students[3], [$easy->id => 'B', $hard->id => 'A', $unused->id => '']);
    // Still in progress: answers are not final and must not count.
    finishedAttempt($exam, Student::factory()->create(), [$easy->id => 'B', $hard->id => 'B'], 'in_progress');

    $service = app(IrtCalibrationService::class);
    $easyItem = $service->calibrateItem($easy->id);
    $hardItem = $service->calibrateItem($hard->id);

    expect($easyItem->total_attempts)->toBe(4)
        ->and($easyItem->correct_count)->toBe(3)
        ->and((float) $easyItem->p_value)->toBe(0.75)
        // b = ln((1-p)/p) with p = (3 + 0.5) / 5 = 0.7
        ->and((float) $easyItem->difficulty)->toBe(round(log(0.3 / 0.7), 4))
        ->and((float) $hardItem->difficulty)->toBe(round(log(0.7 / 0.3), 4))
        ->and((float) $easyItem->discrimination)->toBe(1.0)
        ->and((float) $easyItem->guessing)->toBe(0.0)
        ->and($service->calibrateItem($unused->id))->toBeNull()
        ->and(IrtItemParameter::where('question_id', $unused->id)->exists())->toBeFalse();

    // Same data, same parameters.
    expect((float) $service->calibrateItem($easy->id)->difficulty)->toBe((float) $easyItem->difficulty);

    expect($service->batchCalibrateExam($exam->id))->toBe(['status' => 'success', 'calibrated_count' => 2, 'skipped_count' => 1])
        ->and($service->batchCalibrateExam(Exam::factory()->create()->id))->toBe(['status' => 'no_data', 'calibrated_count' => 0, 'skipped_count' => 0]);
});

test('kemampuan siswa diestimasi dari jawabannya, null bila belum menjawab', function () {
    $exam = Exam::factory()->create();
    $q1 = bankQuestion($exam, 1);
    $q2 = bankQuestion($exam, 2);
    [$strong, $weak, $absent] = Student::factory()->count(3)->create()->all();
    finishedAttempt($exam, $strong, [$q1->id => 'A', $q2->id => 'A']);
    finishedAttempt($exam, $weak, [$q1->id => 'B', $q2->id => 'B']);

    $service = app(IrtCalibrationService::class);
    $thetaStrong = $service->estimateStudentAbility($strong->id, $exam->subject_id);
    $thetaWeak = $service->estimateStudentAbility($weak->id, $exam->subject_id);

    expect($thetaStrong)->toBeGreaterThan($thetaWeak)
        ->and($service->estimateStudentAbility($strong->id, $exam->subject_id))->toBe($thetaStrong)
        ->and($service->estimateStudentAbility($absent->id, $exam->subject_id))->toBeNull()
        ->and(IrtStudentAbility::where('student_id', $absent->id)->exists())->toBeFalse()
        ->and(IrtStudentAbility::where('student_id', $strong->id)->value('items_attempted'))->toBe(2);
});

test('CAT memilih soal ujian itu sendiri, terdekat ke theta, tanpa mengulang', function () {
    $exam = Exam::factory()->create();
    $first = bankQuestion($exam, 1);
    $close = bankQuestion($exam, 2);
    $far = bankQuestion($exam, 3);
    IrtItemParameter::create(['question_id' => $close->id, 'difficulty' => 0.4]);
    IrtItemParameter::create(['question_id' => $far->id, 'difficulty' => 2.5]);

    $session = CatSession::create([
        'uuid' => (string) Str::uuid(), 'student_id' => Student::factory()->create()->id, 'exam_id' => $exam->id,
        'current_theta' => 0, 'started_at' => now(), 'status' => 'in_progress',
    ]);
    $cat = app(CatEngineService::class);

    expect($cat->selectNextItem($session))->toBe($close->id);
    $cat->processResponse($session, $close->id, true);
    expect($cat->selectNextItem($session->fresh()))->toBe($far->id);
    $cat->processResponse($session->fresh(), $far->id, false);
    // Only the uncalibrated item is left.
    expect($cat->selectNextItem($session->fresh()))->toBe($first->id);
    $cat->processResponse($session->fresh(), $first->id, true);
    expect($cat->selectNextItem($session->fresh()))->toBeNull()
        ->and($session->responses()->pluck('question_id')->all())->toBe([$close->id, $far->id, $first->id]);
});

test('kredensial yang belum ditambatkan ke chain tidak punya nomor blok', function () {
    $credential = app(BlockchainCredentialService::class)
        ->issueCredential(User::factory()->create(['role' => 'student']), 'ijazah', 'CERT/2026/001', ['title' => 'Ijazah']);

    expect($credential->fresh()->block_number)->toBeNull()
        ->and(BlockchainCredential::count())->toBe(1);
});

test('impor pegawai tanpa NIP tidak lagi memberi password 6 digit acak', function () {
    $service = app(ImportExportService::class);
    $row = fn (string $email, ?string $nip) => [
        'name' => 'Pegawai '.$email, 'email' => $email, 'role' => 'guru', 'position' => 'Guru',
        'employment_status' => 'honorer', 'employee_number' => $nip,
    ];
    $mapping = array_combine(array_keys($row('x', null)), array_keys($row('x', null)));

    $result = $service->executeImport('employees', $mapping, [$row('tanpa@nip.test', null), $row('dengan@nip.test', '198001')], 'pegawai.xlsx', User::factory()->create(['role' => 'admin'])->id);

    expect($result['success'])->toBeTrue()->and($result['success_count'])->toBe(2);
    $withNip = User::where('email', 'dengan@nip.test')->sole();
    $withoutNip = User::where('email', 'tanpa@nip.test')->sole();

    expect(Hash::check('198001', $withNip->password))->toBeTrue()
        ->and(Employee::where('user_id', $withoutNip->id)->value('employee_number'))->toBeNull()
        ->and($withoutNip->must_change_password)->toBeTrue();
    // No 6-digit number unlocks the account any more.
    foreach ([100000, 555555, 999999] as $guess) {
        expect(Hash::check((string) $guess, $withoutNip->password))->toBeFalse();
    }
});
