<?php

namespace Tests\Feature\Academic;

use App\Models\User;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create(['role' => 'superadmin']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->kepalaSekolah = User::factory()->create(['role' => 'kepala_sekolah']);
    
    $this->student = User::factory()->create(['role' => 'student']);
    $this->guru = User::factory()->create(['role' => 'guru']);

    $this->classroom = Classroom::factory()->create();
    $this->studentModel = Student::factory()->create([
        'user_id' => $this->student->id,
        'classroom_id' => $this->classroom->id,
        'status' => 'active'
    ]);
});

test('authorized executive roles can access all analytics drill-down pages', function () {
    $routes = [
        'analytics.index',
        'analytics.attendance',
        'analytics.academic',
        'analytics.financial',
        'analytics.islamic',
        'analytics.student-risk',
        'analytics.comparison',
        'analytics.heatmap',
        'analytics.custom-report',
    ];

    foreach ($routes as $route) {
        $response = $this->actingAs($this->admin)->get(route($route));
        $response->assertStatus(200);
        
        $response = $this->actingAs($this->kepalaSekolah)->get(route($route));
        $response->assertStatus(200);
        
        $response = $this->actingAs($this->superadmin)->get(route($route));
        $response->assertStatus(200);
    }
});

test('unauthorized roles are forbidden from accessing any analytics drill-down pages', function () {
    $routes = [
        'analytics.index',
        'analytics.attendance',
        'analytics.academic',
        'analytics.financial',
        'analytics.islamic',
        'analytics.student-risk',
        'analytics.comparison',
        'analytics.heatmap',
        'analytics.custom-report',
    ];

    foreach ($routes as $route) {
        $response = $this->actingAs($this->student)->get(route($route));
        $response->assertStatus(403);
        
        $response = $this->actingAs($this->guru)->get(route($route));
        $response->assertStatus(403);
    }
});

test('student risk page calculates risk logic properly without failing', function () {
    $response = $this->actingAs($this->admin)->get(route('analytics.student-risk', ['classroom_id' => $this->classroom->id]));
    $response->assertStatus(200);
    $response->assertSee('Predictive Early Warning System');
});
