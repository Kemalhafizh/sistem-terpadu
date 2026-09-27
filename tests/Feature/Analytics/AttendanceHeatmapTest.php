<?php

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->year = AcademicYear::factory()->create(['is_active' => true]);
    $this->classA = Classroom::factory()->create(['name' => 'X-A', 'grade_level' => 'X']);
    $this->classB = Classroom::factory()->create(['name' => 'XI-B', 'grade_level' => 'XI']);
});

function recordDaily(Classroom $classroom, string $date, array $statuses, User $by, AcademicYear $year, ?int $scheduleId = null): void
{
    foreach ($statuses as $status) {
        $student = Student::factory()->create(['classroom_id' => $classroom->id]);
        Attendance::create([
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'classroom_id' => $classroom->id,
            'schedule_id' => $scheduleId,
            'date' => $date,
            'status' => $status,
            'recorded_by' => $by->id,
        ]);
    }
}

test('heatmap is computed from recorded daily attendance, not generated', function () {
    Carbon::setTestNow('2026-09-25 10:00:00');
    // X-A: 1 of 4 absent on the 23rd, 2 of 4 on the 24th. XI-B only on the 24th: 0 of 2.
    recordDaily($this->classA, '2026-09-23', ['H', 'H', 'H', 'A'], $this->admin, $this->year);
    recordDaily($this->classA, '2026-09-24', ['H', 'S', 'I', 'H'], $this->admin, $this->year);
    recordDaily($this->classB, '2026-09-24', ['H', 'H'], $this->admin, $this->year);

    $heatmap = app(AnalyticsService::class)->getClassroomAbsenceHeatmap(14);

    expect($heatmap['dates'])->toBe(['2026-09-23', '2026-09-24']);
    [$rowA, $rowB] = $heatmap['rows'];
    expect($rowA['name'])->toBe('X-A')
        ->and($rowA['cells'][0])->toMatchArray(['rate' => 25.0, 'absent' => 1, 'total' => 4])
        ->and($rowA['cells'][1])->toMatchArray(['rate' => 50.0, 'absent' => 2, 'total' => 4])
        ->and($rowA['average'])->toBe(37.5);
    // A day a class never recorded is "belum diabsen" (null), not a perfect 0%.
    expect($rowB['cells'][0])->toMatchArray(['rate' => null, 'total' => 0])
        ->and($rowB['cells'][1]['rate'])->toBe(0.0);
    expect($heatmap['summary'])->toMatchArray([
        'school_days' => 2,
        'average_rate' => 30.0,
        'worst' => ['name' => 'X-A', 'rate' => 37.5],
        'missing_cells' => 1,
    ]);
});

test('heatmap ignores per-subject attendance and future dates, and limits to the last N school days', function () {
    Carbon::setTestNow('2026-09-25 10:00:00');
    foreach (['2026-09-21', '2026-09-22', '2026-09-23', '2026-09-24'] as $day) {
        recordDaily($this->classA, $day, ['H'], $this->admin, $this->year);
    }
    recordDaily($this->classA, '2026-09-28', ['A'], $this->admin, $this->year);

    $heatmap = app(AnalyticsService::class)->getClassroomAbsenceHeatmap(3);

    expect($heatmap['dates'])->toBe(['2026-09-22', '2026-09-23', '2026-09-24'])
        ->and($heatmap['summary']['average_rate'])->toBe(0.0);
});

test('heatmap page renders real class names and an empty state without data', function () {
    $this->actingAs($this->admin)->get(route('analytics.heatmap'))
        ->assertOk()
        ->assertSee('Belum ada presensi harian yang tercatat');

    recordDaily($this->classA, now()->toDateString(), ['H', 'A'], $this->admin, $this->year);

    $this->actingAs($this->admin)->get(route('analytics.heatmap', ['days' => 7]))
        ->assertOk()
        ->assertSee('X-A')
        ->assertSee('50,0%')
        ->assertDontSee('X-IPA 1');
});
