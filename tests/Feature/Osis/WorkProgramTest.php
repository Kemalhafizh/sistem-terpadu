<?php

use App\Models\AcademicYear;
use App\Models\OsisWorkProgram;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->academicYear = AcademicYear::factory()->create([
        'is_active' => true,
    ]);
});

test('admin can access osis work programs page', function () {
    $response = $this->actingAs($this->admin)->get(route('osis.work-programs'));
    $response->assertStatus(200);
});

test('admin can create work program', function () {
    $response = $this->actingAs($this->admin)->post(route('osis.work-programs.store'), [
        'title' => 'Class Meeting 2026',
        'sekbid' => 'Sekbid 5 (Olahraga)',
        'description' => 'Turnamen olahraga antarkelas setelah ujian semester.',
        'budget_plan' => 5000000,
        'planned_date' => '2026-12-15',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('osis_work_programs', [
        'title' => 'Class Meeting 2026',
        'sekbid' => 'Sekbid 5 (Olahraga)',
        'budget_plan' => 5000000,
    ]);
});

test('admin can update work program status and budget actual', function () {
    $program = OsisWorkProgram::create([
        'academic_year_id' => $this->academicYear->id,
        'title' => 'LDKS 2026',
        'sekbid' => 'Sekbid 1 (Keadilan)',
        'description' => 'Latihan Dasar Kepemimpinan Siswa.',
        'budget_plan' => 8000000,
        'planned_date' => '2026-08-10',
        'status' => 'planned',
    ]);

    $response = $this->actingAs($this->admin)->put(route('osis.work-programs.update', $program), [
        'budget_actual' => 7500000,
        'actual_date' => '2026-08-12',
        'status' => 'completed',
        'report' => 'Kegiatan LDKS berjalan dengan sukses.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('osis_work_programs', [
        'id' => $program->id,
        'status' => 'completed',
        'budget_actual' => 7500000,
    ]);
});
