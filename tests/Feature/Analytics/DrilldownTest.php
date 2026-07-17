<?php

use App\Models\User;
use App\Models\AcademicYear;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->academicYear = AcademicYear::factory()->create([
        'is_active' => true,
    ]);
});

test('admin can access advanced executive analytics main dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('analytics.index'));
    $response->assertStatus(200);
});

test('admin can access analytics attendance drilldown page', function () {
    $response = $this->actingAs($this->admin)->get(route('analytics.attendance'));
    $response->assertStatus(200);
});

test('admin can access analytics academic drilldown page', function () {
    $response = $this->actingAs($this->admin)->get(route('analytics.academic'));
    $response->assertStatus(200);
});

test('admin can access analytics financial drilldown page', function () {
    $response = $this->actingAs($this->admin)->get(route('analytics.financial'));
    $response->assertStatus(200);
});

test('admin can access analytics islamic drilldown page', function () {
    $response = $this->actingAs($this->admin)->get(route('analytics.islamic'));
    $response->assertStatus(200);
});

test('admin can access analytics student risk drilldown page', function () {
    $response = $this->actingAs($this->admin)->get(route('analytics.student-risk'));
    $response->assertStatus(200);
});

test('admin can access analytics comparison drilldown page', function () {
    $response = $this->actingAs($this->admin)->get(route('analytics.comparison'));
    $response->assertStatus(200);
});

test('admin can access analytics heatmap drilldown page', function () {
    $response = $this->actingAs($this->admin)->get(route('analytics.heatmap'));
    $response->assertStatus(200);
});
