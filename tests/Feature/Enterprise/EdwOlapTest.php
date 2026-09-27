<?php

use App\Models\User;
use App\Services\EdwOlapEngineService;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->olapService = app(EdwOlapEngineService::class);
});

test('edw olap service refreshes daily financials, student kpis, and employee performances', function () {
    $result = $this->olapService->refreshAllMarts('2026-07-23');

    expect($result)->toHaveKeys(['date', 'financial_mart_updated', 'student_kpis_count', 'employee_performances_count', 'status']);
    expect($result['status'])->toBe('SUCCESS');
});

test('edw olap service provides instant executive summary with low query latency', function () {
    $this->olapService->refreshAllMarts('2026-07-23');
    $summary = $this->olapService->getExecutiveOlapSummary();

    expect($summary)->toHaveKeys(['financial', 'top_students', 'top_employees', 'query_latency_ms', 'is_instant_olap']);
    expect($summary['query_latency_ms'])->toBeLessThan(50.0);
});

test('admin can view edw dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.analytics.edw.index'));
    $response->assertStatus(200);
    $response->assertSee('Enterprise Data Warehouse');
    $response->assertSee('OLTP');
});

test('admin can trigger edw data marts refresh via controller', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.analytics.edw.index'))
        ->post(route('admin.analytics.edw.refresh'), [
            'date' => '2026-07-23',
        ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('admin.analytics.edw.index'));
});

test('artisan command edw:refresh-marts executes successfully', function () {
    $this->artisan('edw:refresh-marts')
        ->assertExitCode(0);
});
