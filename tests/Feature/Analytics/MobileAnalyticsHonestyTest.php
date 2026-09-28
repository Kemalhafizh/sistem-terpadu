<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\DailyAssessment;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;

/*
 * The student, teacher and parent analytics screens: no figure is made up
 * when nothing was recorded, and a class is the one of the active year.
 */

beforeEach(function () {
    Carbon::setTestNow('2026-09-10 09:00:00');
    AcademicYear::create(['year' => '2025/2026', 'semester' => 'Ganjil', 'is_active' => false]);
    $this->year = AcademicYear::create(['year' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
    $this->guru = User::factory()->create(['role' => 'guru', 'must_change_password' => false]);
    $this->fisika = Subject::factory()->create(['name' => 'Fisika', 'kkm' => 75]);
    $this->oldClass = Classroom::factory()->create(['name' => 'XI MIPA 2', 'academic_year' => '2025/2026']);
    $this->classroom = Classroom::factory()->create(['name' => 'XI MIPA 2', 'academic_year' => '2026/2027']);
});

afterEach(fn () => Carbon::setTestNow());

function studentIn(Classroom $classroom, array $attributes = []): Student
{
    return Student::factory()->create([
        'user_id' => User::factory()->create(['role' => 'student', 'must_change_password' => false])->id,
        'classroom_id' => $classroom->id,
        ...$attributes,
    ]);
}

function grade(Student $student, Subject $subject, float $score, int $yearId, int $recordedBy, string $type = 'uh'): Grade
{
    return Grade::create([
        'student_id' => $student->id, 'subject_id' => $subject->id, 'academic_year_id' => $yearId,
        'type' => $type, 'score' => $score, 'recorded_by' => $recordedBy,
    ]);
}

it('does not report zeros for a student with no records', function () {
    $student = studentIn($this->classroom);
    Sanctum::actingAs($student->user);

    $this->getJson('/api/v1/analytics/student/summary')->assertOk()
        ->assertJsonPath('overallAverage', null)
        ->assertJsonPath('attendanceRate', null)
        ->assertJsonPath('attendanceRecorded', 0)
        ->assertJsonPath('competencyRadar', [])
        ->assertJsonPath('subjects', []);
});

it('lists every graded subject against its KKM', function () {
    $student = studentIn($this->classroom);
    $mapel = collect(['Biologi', 'Fisika', 'Kimia', 'Matematika', 'Sejarah', 'Sosiologi', 'Ekonomi'])
        ->map(fn ($name) => $name === 'Fisika' ? $this->fisika : Subject::factory()->create(['name' => $name, 'kkm' => 70]));
    $mapel->each(fn ($subject) => grade($student, $subject, 80, $this->year->id, $this->guru->id));
    grade($student, $this->fisika, 60, $this->year->id, $this->guru->id, 'tugas');
    Sanctum::actingAs($student->user);

    $subjects = $this->getJson('/api/v1/analytics/student/summary')->assertOk()->json('subjects');

    expect($subjects)->toHaveCount(7)
        ->and($subjects[0]['name'])->toBe('Biologi')
        ->and(collect($subjects)->firstWhere('name', 'Fisika'))->toBe(['name' => 'Fisika', 'average' => 70, 'kkm' => 75, 'gradesCount' => 2]);
});

it('lists this year\'s classes from assessments and grades, with ids', function () {
    DailyAssessment::create([
        'subject_id' => $this->fisika->id, 'classroom_id' => $this->classroom->id, 'teacher_id' => $this->guru->id,
        'academic_year_id' => $this->year->id, 'title' => 'UH 1', 'type' => 'ulangan_harian',
        'assessment_date' => '2026-09-01', 'max_score' => 100, 'kkm' => 75,
    ]);
    $kimia = Subject::factory()->create(['name' => 'Kimia']);
    grade(studentIn($this->classroom), $kimia, 80, $this->year->id, $this->guru->id);
    // Last year's class of the same name is not taught this year.
    grade(studentIn($this->oldClass), $this->fisika, 90, AcademicYear::where('year', '2025/2026')->value('id'), $this->guru->id);

    Sanctum::actingAs($this->guru);
    $classes = $this->getJson('/api/v1/analytics/teacher/my-classes')->assertOk()->json();

    expect($classes)->toHaveCount(2)
        ->and(array_column($classes, 'classroomId'))->each->toBe($this->classroom->id)
        ->and(array_column($classes, 'subjectName'))->toBe(['Fisika', 'Kimia'])
        ->and($classes[0]['academicYear'])->toBe('2026/2027');
});

it('resolves a class name to the active year and refuses a class the teacher does not teach', function () {
    $ani = studentIn($this->classroom);
    $budi = studentIn($this->classroom);
    studentIn($this->classroom); // not graded yet
    grade($ani, $this->fisika, 60, $this->year->id, $this->guru->id);
    grade($budi, $this->fisika, 90, $this->year->id, $this->guru->id);
    grade($budi, $this->fisika, 70, $this->year->id, $this->guru->id, 'tugas');

    Sanctum::actingAs($this->guru);
    $this->getJson('/api/v1/analytics/teacher/class-performance?class=XI%20MIPA%202&subject=Fisika')->assertOk()
        ->assertJsonPath('classroomId', $this->classroom->id)
        ->assertJsonPath('classAverage', 73.3)
        ->assertJsonPath('scoresCount', 3)
        ->assertJsonPath('studentsGraded', 2)
        ->assertJsonPath('studentsCount', 3)
        ->assertJsonPath('atRiskStudents.0.currentScore', 60);

    $this->getJson("/api/v1/analytics/teacher/class-performance?classroom_id={$this->oldClass->id}&subject_id={$this->fisika->id}")
        ->assertForbidden();
});

it('does not report zeros for a class nobody graded', function () {
    $admin = User::factory()->create(['role' => 'admin', 'must_change_password' => false]);
    studentIn($this->classroom);
    Sanctum::actingAs($admin);

    $this->getJson("/api/v1/analytics/teacher/class-performance?classroom_id={$this->classroom->id}&subject_id={$this->fisika->id}")
        ->assertOk()
        ->assertJsonPath('classAverage', null)
        ->assertJsonPath('highestScore', null)
        ->assertJsonPath('lowestScore', null)
        ->assertJsonPath('passRatePercentage', null)
        ->assertJsonPath('scoresCount', 0);
});

it('does not invent a child\'s scores, tahfidz target or holidays', function () {
    $parent = User::factory()->create(['role' => 'parent', 'must_change_password' => false]);
    studentIn($this->classroom, ['parent_id' => $parent->id]);
    Sanctum::actingAs($parent);

    $response = $this->getJson('/api/v1/analytics/parent/child-progress')->assertOk()
        ->assertJsonPath('academicScore', null)
        ->assertJsonPath('classAverageScore', null)
        ->assertJsonPath('tahfidzCurrentJuz', null)
        ->assertJsonPath('tahfidzTargetJuz', null);

    expect($response->json('attendanceHeatmap'))->toHaveCount(10)
        ->and(array_unique(array_column($response->json('attendanceHeatmap'), 'status')))->toBe(['KOSONG']);
});
