<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\PredictiveAnalyticsService;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->academicYear = AcademicYear::factory()->create([
        'is_active' => true,
    ]);

    $this->classroom = Classroom::create([
        'name' => 'Kelas X-A',
        'academic_year' => $this->academicYear->year,
        'grade_level' => 'X',
        'major' => 'Umum',
    ]);

    $this->student = Student::factory()->create([
        'classroom_id' => $this->classroom->id,
    ]);

    $this->subject = Subject::create([
        'name' => 'Matematika',
        'code' => 'MTK',
        'category' => 'Nasional',
        'kkm' => 75,
    ]);

    Cache::flush();
});

test('at risk student identification logic works correctly', function () {
    // 1. Initially, student should be Aman since they have no grades, attendance logs, or penalties.
    $service = new PredictiveAnalyticsService();
    $initialAtRiskList = $service->identifyAtRiskStudents($this->classroom->id);
    expect($initialAtRiskList->count())->toBe(0);

    // 2. Clear cache to recalculate
    Cache::flush();

    // 3. Add a low grade to trigger academic risk
    DB::table('grades')->insert([
        'student_id' => $this->student->id,
        'subject_id' => $this->subject->id,
        'academic_year_id' => $this->academicYear->id,
        'recorded_by' => $this->admin->id,
        'type' => 'uh',
        'score' => 50.00,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 4. Identify at risk students
    $atRiskList = $service->identifyAtRiskStudents($this->classroom->id);
    expect($atRiskList->count())->toBe(1);
    expect($atRiskList->first()->student->id)->toBe($this->student->id);
    expect($atRiskList->first()->total_risk_score)->toBe(40.0);
    expect($atRiskList->first()->category)->toBe('Waspada');
});
