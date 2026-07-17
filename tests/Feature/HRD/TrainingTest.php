<?php

use App\Models\Employee;
use App\Models\EmployeeTraining;
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

test('admin can access trainings page for employee', function () {
    $response = $this->actingAs($this->admin)->get(route('hrd.employees.trainings', $this->employee));
    $response->assertStatus(200);
});

test('admin can log training record successfully', function () {
    $response = $this->actingAs($this->admin)->post(route('hrd.employees.trainings.store', $this->employee), [
        'title' => 'Workshop Kurikulum Merdeka',
        'type' => 'workshop',
        'organizer' => 'YBWSA Semarang',
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-02',
        'hours' => 32,
        'certificate_number' => 'CERT/WM/2026',
        'certificate_expiry' => '2028-07-01',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('employee_trainings', [
        'employee_id' => $this->employee->id,
        'title' => 'Workshop Kurikulum Merdeka',
        'hours' => 32,
    ]);
});
