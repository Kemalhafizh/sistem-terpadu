<?php

use App\Models\ExportJob;
use App\Models\User;
use App\Services\Content\ExportEngineService;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin', 'name' => 'Admin Export Interop']);
    $this->exportService = app(ExportEngineService::class);
});

test('export engine service queues and processes multi format export jobs', function () {
    $jobPdf = $this->exportService->queueExport(
        'Arsip PDF/A Siswa',
        'students',
        'pdf_a',
        $this->user->id
    );

    expect($jobPdf->id)->not()->toBeNull();
    expect($jobPdf->status)->toBe('completed');
    expect($jobPdf->file_path)->toContain('.pdf');

    $jobXml = $this->exportService->queueExport(
        'Sinkron Dapodik XML',
        'teachers',
        'xml_dapodik',
        $this->user->id
    );

    expect($jobXml->status)->toBe('completed');
    expect($jobXml->file_path)->toContain('.xml');
});

test('admin can view format selector dashboard and trigger batch export via controller', function () {
    $indexResponse = $this->actingAs($this->user)->get(route('exports.index'));
    $indexResponse->assertStatus(200);

    $generateResponse = $this->actingAs($this->user)->post(route('exports.generate'), [
        'job_name' => 'Ekspor Laporan Keuangan XLSX',
        'entity_type' => 'finance',
        'format' => 'xlsx',
    ]);

    $generateResponse->assertRedirect();
    expect(ExportJob::where('job_name', 'Ekspor Laporan Keuangan XLSX')->exists())->toBeTrue();
});

test('user can download completed export job stream', function () {
    $job = $this->exportService->queueExport('Download Test Job', 'inventory', 'csv', $this->user->id);

    $downloadResponse = $this->actingAs($this->user)->get(route('exports.download', $job->id));
    $downloadResponse->assertStatus(200);
    $downloadResponse->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
});

test('user can view export monitoring history log', function () {
    $this->exportService->queueExport('History Job 1', 'students', 'json_ld', $this->user->id);

    $historyResponse = $this->actingAs($this->user)->get(route('exports.history'));
    $historyResponse->assertStatus(200);
    $historyResponse->assertSee('History Job 1');
});
