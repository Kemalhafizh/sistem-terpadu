<?php

use App\Models\Student;
use App\Models\User;
use App\Models\SyncConfig;
use App\Models\SyncReport;
use App\Services\Integration\SyncMapperService;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->syncService = app(SyncMapperService::class);
});

test('admin can access sync dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.integration.sync.dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Dapodik');
    $response->assertSee('EMIS');
});

test('admin can access and save column mapping configuration', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.integration.sync.mapping', ['dapodik', 'students']));
    $response->assertStatus(200);

    // Save a custom mapping config
    $responseSave = $this->actingAs($this->admin)->post(route('admin.integration.sync.mapping.store'), [
        'system_target' => 'dapodik',
        'local_table' => 'students',
        'mapping' => [
            'nis' => 'custom_nis',
            'nisn' => 'custom_nisn',
            'name' => 'custom_nama',
        ]
    ]);

    $responseSave->assertStatus(302);
    
    $this->assertDatabaseHas('sync_configs', [
        'system_target' => 'dapodik',
        'local_table' => 'students',
    ]);

    $config = SyncConfig::where('system_target', 'dapodik')->where('local_table', 'students')->first();
    expect($config->field_mapping['nis'])->toBe('custom_nis');
});

test('admin can run audit validation checking data issues', function () {
    // Create a student with invalid NISN
    $student = Student::factory()->create([
        'nisn' => '123' // Not 10 digits
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.integration.sync.validation', ['dapodik', 'students']));
    $response->assertStatus(200);
    $response->assertSee('NISN tidak valid (harus 10 digit angka).');
});

test('admin can export mapped data to json ready for upload', function () {
    // Seed at least one student
    $student = Student::factory()->create([
        'nisn' => '1234567890'
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.integration.sync.export', ['dapodik', 'students']));
    
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/json');

    $content = json_decode($response->getContent(), true);
    expect($content)->toHaveKey('metadata');
    expect($content)->toHaveKey('data');

    // It should have logged a SyncReport
    $this->assertDatabaseHas('sync_reports', [
        'system_target' => 'dapodik',
        'local_table' => 'students',
    ]);
});
