<?php

use App\Models\Billing;
use App\Models\Employee;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\User;
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
