<?php

use App\Models\User;
use App\Models\Setting;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Subject;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

test('cache warmup command caches all required settings and metadata', function () {
    // 1. Setup mock database records
    Setting::create(['key' => 'school_name', 'value' => 'SMA Islam Sultan Agung 1', 'group' => 'general', 'type' => 'text']);
    Setting::create(['key' => 'school_address', 'value' => 'Semarang', 'group' => 'general', 'type' => 'text']);
    
    AcademicYear::create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
        'start_date' => '2026-07-01',
        'end_date' => '2026-12-31'
    ]);

    Classroom::create([
        'name' => 'X-A', 
        'grade_level' => 'X', 
        'major' => 'MIPA', 
        'academic_year' => '2026/2027'
    ]);
    Subject::create([
        'name' => 'Matematika', 
        'code' => 'MTK', 
        'category' => 'Nasional', 
        'kkm' => 75
    ]);

    // Ensure cache is completely clear before warmup
    Cache::flush();

    // 2. Execute warmup command
    $exitCode = Artisan::call('cache:warmup');
    $this->assertEquals(0, $exitCode);

    // 3. Assert keys are cached successfully
    $this->assertTrue(Cache::has('appearance_settings'));
    $this->assertTrue(Cache::has('appearance_css_variables'));
    $this->assertTrue(Cache::has('settings_all'));
    $this->assertTrue(Cache::has('academic_year_current'));
    $this->assertTrue(Cache::has('classrooms_all'));
    $this->assertTrue(Cache::has('subjects_all'));

    // Assert specific settings cache keys
    $this->assertTrue(Cache::has('setting_school_name'));
    $this->assertEquals('SMA Islam Sultan Agung 1', Cache::get('setting_school_name'));

    // 4. Assert model operations read from cache
    // Let's clear the DB table to prove Setting::get reads from cache
    Setting::query()->delete();
    $this->assertEquals('SMA Islam Sultan Agung 1', Setting::get('school_name'));
});
