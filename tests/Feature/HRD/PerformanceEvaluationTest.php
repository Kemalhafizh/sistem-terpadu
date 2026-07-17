<?php

use App\Models\Employee;
use App\Models\PerformanceEvaluation;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'HR Admin',
        'email' => 'hradmin@sista.com',
        'role' => 'admin',
    ]);

    $this->teacherUser = User::factory()->create([
        'name' => 'Teacher One',
        'email' => 'teacherone@sista.com',
        'role' => 'guru',
    ]);

    $this->employee = Employee::create([
        'user_id' => $this->teacherUser->id,
        'employee_number' => 'NIY.202611001',
        'position' => 'Guru Agama',
        'employment_status' => 'gty',
    ]);
});

test('admin can access performance evaluation page for employee', function () {
    $response = $this->actingAs($this->admin)->get(route('hrd.employees.evaluation', $this->employee));
    $response->assertStatus(200);
});

test('admin can store performance evaluation successfully', function () {
    $response = $this->actingAs($this->admin)->post(route('hrd.employees.evaluation.store', $this->employee), [
        'period' => 'Semester Genap 2025/2026',
        'pedagogik' => 85,
        'profesional' => 90,
        'sosial' => 80,
        'kepribadian' => 88,
        'strengths' => 'Sangat menguasai materi.',
        'improvements' => 'Perlu meningkatkan keterlibatan siswa.',
        'goals' => 'Mengikuti sertifikasi lanjutan.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('performance_evaluations', [
        'employee_id' => $this->employee->id,
        'total_score' => 85.75,
        'grade' => 'A',
    ]);
});
