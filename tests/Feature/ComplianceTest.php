<?php

use App\Models\User;
use App\Models\AcademicYear;
use App\Models\EdsStandard;
use App\Models\ComplianceDocument;
use App\Models\ComplianceMilestone;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    Storage::fake('public');

    // Create users with different roles
    $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->guru = User::factory()->create(['role' => 'guru']);

    // Create active academic year
    $this->academicYear = AcademicYear::factory()->create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    // Ensure we have at least one standard
    $this->standard = EdsStandard::firstOrCreate(
        ['code' => 'SNP-1'],
        ['name' => 'Standar Kompetensi Lulusan', 'order' => 1]
    );
});

test('guest cannot access compliance index', function () {
    $response = $this->get(route('compliance.index'));
    $response->assertRedirect('/login');
});

test('unauthorized roles cannot access compliance index', function () {
    $responseGuru = $this->actingAs($this->guru)->get(route('compliance.index'));
    $responseGuru->assertStatus(403);
});

test('authorized roles can access compliance index', function () {
    $responseKepsek = $this->actingAs($this->kepsek)->get(route('compliance.index'));
    $responseKepsek->assertStatus(200);

    $responseAdmin = $this->actingAs($this->admin)->get(route('compliance.index'));
    $responseAdmin->assertStatus(200);
});

test('authorized roles can upload compliance document', function () {
    $file = UploadedFile::fake()->create('document_akreditasi.pdf', 1000);

    $response = $this->actingAs($this->kepsek)->post(route('compliance.documents.upload'), [
        'standard_id' => $this->standard->id,
        'name' => 'RKJM Tahun Ajaran Baru',
        'file' => $file,
        'notes' => 'Catatan penting visitasi',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('compliance_documents', [
        'standard_id' => $this->standard->id,
        'name' => 'RKJM Tahun Ajaran Baru',
        'notes' => 'Catatan penting visitasi',
    ]);
});

test('authorized roles can manage milestones', function () {
    $response = $this->actingAs($this->kepsek)->post(route('compliance.milestones.store'), [
        'title' => 'Rapat Audit Mutu Internal',
        'due_date' => '2026-06-15',
        'description' => 'Mengevaluasi kesiapan borang akreditasi',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('compliance_milestones', [
        'title' => 'Rapat Audit Mutu Internal',
        'due_date' => '2026-06-15 00:00:00',
        'status' => 'pending',
    ]);

    $milestone = ComplianceMilestone::first();

    // Toggle to completed
    $responseToggle = $this->actingAs($this->kepsek)->post(route('compliance.milestones.toggle', $milestone->id));
    $responseToggle->assertRedirect();
    $responseToggle->assertSessionHas('success');

    $milestone->refresh();
    expect($milestone->status)->toBe('completed');
    expect($milestone->completed_at)->not->toBeNull();
});

test('authorized roles can export pdf report', function () {
    $response = $this->actingAs($this->kepsek)->get(route('compliance.export'));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});
