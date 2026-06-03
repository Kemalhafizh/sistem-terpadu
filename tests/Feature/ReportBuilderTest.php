<?php

/** @var TestCase|stdClass $this */

use App\Models\User;
use App\Models\Student;
use App\Models\CustomReport;
use App\Services\ReportBuilderService;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    config(['security.test_force_logout' => false]);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->waka = User::factory()->create(['role' => 'waka_kurikulum']);
    $this->student = User::factory()->create(['role' => 'student']);
});

test('unauthorized users cannot access report builder', function () {
    // Student should be blocked (403)
    $this->actingAs($this->student)
        ->get(route('admin.report-builder.index'))
        ->assertStatus(403);

    // Admin should be allowed (200)
    $this->actingAs($this->admin)
        ->get(route('admin.report-builder.index'))
        ->assertStatus(200);

    // Waka Kurikulum should be allowed (200)
    $this->actingAs($this->waka)
        ->get(route('admin.report-builder.index'))
        ->assertStatus(200);
});

test('user can store custom report template successfully', function () {
    $this->actingAs($this->admin);

    $response = $this->post(route('admin.report-builder.store'), [
        'name' => 'Laporan Kustom Prestasi',
        'description' => 'Laporan kustom performansi siswa berprestasi',
        'source' => 'students',
        'columns' => ['nis', 'name', 'classroom_name'],
        'filters' => [
            ['column' => 'gender', 'operator' => '=', 'value' => 'L']
        ],
        'chartType' => 'bar',
        'is_shared' => '1',
    ]);

    $response->assertRedirect(route('admin.report-builder.index'));

    $this->assertDatabaseHas('custom_reports', [
        'name' => 'Laporan Kustom Prestasi',
        'is_shared' => true,
    ]);
});

test('user can trigger live preview ajax endpoint', function () {
    // Seed sample student via factory
    $student = Student::factory()->create([
        'gender' => 'L',
        'nis' => '12345',
    ]);
    
    // Update name to Ahmad Faisal for exact matching
    $studentUser = $student->user;
    $studentUser->update(['name' => 'Ahmad Faisal']);

    $this->actingAs($this->admin);

    $response = $this->postJson(route('admin.report-builder.preview'), [
        'source' => 'students',
        'columns' => ['nis', 'name', 'classroom_name'],
        'filters' => [
            ['column' => 'gender', 'operator' => '=', 'value' => 'L']
        ],
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data',
            'headings',
        ])
        ->assertJsonPath('data.0.name', 'Ahmad Faisal')
        ->assertJsonPath('data.0.nis', '12345');
});

test('user can run and view custom report', function () {
    $report = CustomReport::create([
        'name' => 'Laporan Siswa Aktif',
        'created_by' => $this->admin->id,
        'config' => [
            'source' => 'students',
            'columns' => ['nis', 'name'],
            'filters' => [],
            'chartType' => 'table',
        ],
        'is_shared' => true,
    ]);

    $this->actingAs($this->waka)
        ->get(route('admin.report-builder.show', $report->uuid))
        ->assertStatus(200)
        ->assertSee('Laporan: Laporan Siswa Aktif');
});

test('user can export custom report as pdf and excel', function () {
    $student = Student::factory()->create();

    $report = CustomReport::create([
        'name' => 'Laporan Prestasi Ekspor',
        'created_by' => $this->admin->id,
        'config' => [
            'source' => 'students',
            'columns' => ['nis', 'name'],
            'filters' => [],
            'chartType' => 'table',
        ],
        'is_shared' => true,
    ]);

    $this->actingAs($this->admin);

    // PDF download test
    $pdfResponse = $this->get(route('admin.report-builder.export.pdf', $report->uuid));
    $pdfResponse->assertStatus(200)
        ->assertHeader('content-type', 'application/pdf');

    // Excel download test
    $excelResponse = $this->get(route('admin.report-builder.export.excel', $report->uuid));
    $excelResponse->assertStatus(200)
        ->assertHeader('content-disposition', 'attachment; filename=Laporan_Laporan_Prestasi_Ekspor.xlsx');
});

test('user can delete their own report template', function () {
    $report = CustomReport::create([
        'name' => 'Laporan Sampah',
        'created_by' => $this->admin->id,
        'config' => [
            'source' => 'students',
            'columns' => ['nis', 'name'],
            'filters' => [],
        ],
    ]);

    // Admin can delete it
    $this->actingAs($this->admin)
        ->delete(route('admin.report-builder.destroy', $report->uuid))
        ->assertRedirect(route('admin.report-builder.index'));

    $this->assertDatabaseMissing('custom_reports', [
        'id' => $report->id,
    ]);
});
