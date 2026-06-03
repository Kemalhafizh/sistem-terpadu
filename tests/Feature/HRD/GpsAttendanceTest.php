<?php

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Seed GPS & geofencing configurations
    Setting::updateOrCreate(['key' => 'school_latitude'], ['value' => '-6.9535', 'group' => 'system', 'type' => 'text']);
    Setting::updateOrCreate(['key' => 'school_longitude'], ['value' => '110.4355', 'group' => 'system', 'type' => 'text']);
    Setting::updateOrCreate(['key' => 'school_attendance_radius'], ['value' => '100', 'group' => 'system', 'type' => 'text']);

    Storage::fake('public');

    // Create user and employee
    $this->user = User::factory()->create(['role' => 'guru']);
    $this->employee = Employee::create([
        'user_id' => $this->user->id,
        'employee_number' => 'GUR-2026-0001',
        'position' => 'Guru Matematika',
        'employment_status' => 'gty',
        'hire_date' => '2026-01-01',
        'base_salary' => 3500000.00,
    ]);
});

test('employee check-in fails if outside geofence radius', function () {
    $photo = UploadedFile::fake()->image('selfie.jpg');

    // Attempt check-in from very far away (e.g. latitude -7.95, longitude 111.4)
    $response = $this->actingAs($this->user)->post(route('my-hrd.attendance'), [
        'type' => 'in',
        'lat' => '-7.9535',
        'long' => '111.4355',
        'photo' => $photo,
    ]);

    $response->assertStatus(302);
    $response->assertSessionHas('error');

    // Assert no attendance record was created
    expect(EmployeeAttendance::count())->toBe(0);
});

test('employee check-in fails if photo selfie is missing', function () {
    // Attempt check-in from exact school location, but without photo
    $response = $this->actingAs($this->user)->post(route('my-hrd.attendance'), [
        'type' => 'in',
        'lat' => '-6.9535',
        'long' => '110.4355',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHas('error', 'Foto selfie check-in wajib diunggah.');

    // Assert no attendance record was created
    expect(EmployeeAttendance::count())->toBe(0);
});

test('employee check-in succeeds within geofence radius with selfie', function () {
    $photo = UploadedFile::fake()->image('selfie.jpg');

    // Attempt check-in from inside the geofence (exact school coordinate)
    $response = $this->actingAs($this->user)->post(route('my-hrd.attendance'), [
        'type' => 'in',
        'lat' => '-6.9535',
        'long' => '110.4355',
        'photo' => $photo,
    ]);

    $response->assertStatus(302);
    $response->assertSessionHas('success');

    // Assert attendance record exists
    $attendance = EmployeeAttendance::first();
    expect($attendance)->not->toBeNull();
    expect($attendance->employee_id)->toBe($this->employee->id);
    expect($attendance->location_lat)->toBe('-6.9535');
    expect($attendance->location_long)->toBe('110.4355');
    expect($attendance->distance_meters)->toBeLessThanOrEqual(5.0); // exact coordinates should be ~0 meters
    expect($attendance->photo_path)->not->toBeNull();

    // Assert photo is stored
    Storage::disk('public')->assertExists($attendance->photo_path);
});

test('employee can check-out after checking in', function () {
    // Pre-record a check-in attendance today
    $attendance = EmployeeAttendance::create([
        'employee_id' => $this->employee->id,
        'date' => Carbon::today(),
        'check_in_time' => '07:00:00',
        'status' => 'hadir',
        'location_lat' => '-6.9535',
        'location_long' => '110.4355',
        'distance_meters' => 0,
        'photo_path' => 'hrd/attendance/dummy.jpg',
    ]);

    // Attempt check-out from school location
    $response = $this->actingAs($this->user)->post(route('my-hrd.attendance'), [
        'type' => 'out',
        'lat' => '-6.9535',
        'long' => '110.4355',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHas('success');

    $attendance->refresh();
    expect($attendance->check_out_time)->not->toBeNull();
});
