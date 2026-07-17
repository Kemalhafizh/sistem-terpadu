<?php

use App\Models\Student;
use App\Models\UksMedicine;
use App\Models\UksVisit;
use App\Models\User;
use App\Models\StudentHealthRecord;
use App\Models\HealthScreening;
use App\Models\HealthScreeningResult;
use App\Models\UksMedicineRestock;
use App\Models\AcademicYear;
use App\Models\Classroom;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->student = Student::factory()->create();
    $this->classroom = Classroom::factory()->create();
    $this->student->update(['classroom_id' => $this->classroom->id]);
    
    // Retrieve or create active academic year
    $this->academicYear = AcademicYear::where('is_active', true)->first();
    if (!$this->academicYear) {
        $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
    }
});

test('staff can access health records index and detail pages', function () {
    $response = $this->actingAs($this->admin)->get(route('uks.health-records.index'));
    $response->assertStatus(200);
    $response->assertViewIs('uks.health-records.index');

    $response = $this->actingAs($this->admin)->get(route('uks.health-records.show', $this->student->id));
    $response->assertStatus(200);
    $response->assertViewIs('uks.health-records.show');
});

test('staff can update student health record', function () {
    $record = StudentHealthRecord::firstOrCreate(['student_id' => $this->student->id]);

    $response = $this->actingAs($this->admin)->post(route('uks.health-records.update'), [
        'student_id' => $this->student->id,
        'blood_type' => 'O',
        'allergies' => 'Kacang, Udang',
        'chronic_conditions' => 'Asma',
        'medications' => 'Inhaler',
        'vision_status' => 'minus',
        'height_cm' => 170.5,
        'weight_kg' => 60.0,
        'emergency_contact_name' => 'Budi',
        'emergency_contact_phone' => '0812345678',
    ]);

    $response->assertRedirect(route('uks.health-records.index'));
    
    $record->refresh();
    expect($record->blood_type)->toBe('O');
    expect($record->allergies)->toBe(['Kacang', 'Udang']);
    expect($record->chronic_conditions)->toBe(['Asma']);
    expect($record->medications)->toBe(['Inhaler']);
    expect($record->vision_status)->toBe('minus');
    expect((float)$record->height_cm)->toBe(170.5);
});

test('staff can schedule and manage health screening', function () {
    // Schedule screening
    $response = $this->actingAs($this->admin)->post(route('uks.screenings.store'), [
        'title' => 'Screening Gigi Semester 1',
        'screening_date' => now()->format('Y-m-d'),
        'academic_year_id' => $this->academicYear->id,
        'target_classrooms' => [$this->classroom->id],
    ]);

    $response->assertRedirect(route('uks.screenings.index'));
    
    $screening = HealthScreening::where('title', 'Screening Gigi Semester 1')->first();
    expect($screening)->not->toBeNull();
    expect($screening->target_classrooms)->toBe([$this->classroom->id]);

    // Go to input results page
    $response = $this->actingAs($this->admin)->get(route('uks.screenings.results', $screening->id));
    $response->assertStatus(200);

    // Save screening results
    $response = $this->actingAs($this->admin)->post(route('uks.screenings.results.store'), [
        'screening_id' => $screening->id,
        'results' => [
            [
                'student_id' => $this->student->id,
                'height_cm' => 172.0,
                'weight_kg' => 65.5,
                'vision_left' => '6/6',
                'vision_right' => '6/6',
                'dental_status' => 'Sehat',
                'findings' => 'Tidak ada temuan klinis',
                'recommendations' => 'Pertahankan kebersihan gigi',
            ]
        ]
    ]);

    $response->assertRedirect(route('uks.screenings.index'));

    $this->assertDatabaseHas('health_screening_results', [
        'screening_id' => $screening->id,
        'student_id' => $this->student->id,
        'height_cm' => 172.0,
        'dental_status' => 'Sehat',
    ]);

    // View report
    $response = $this->actingAs($this->admin)->get(route('uks.screenings.report', $screening->id));
    $response->assertStatus(200);

    // Export PDF
    $response = $this->actingAs($this->admin)->get(route('uks.screenings.export-pdf', $screening->id));
    $response->assertStatus(200);
});

test('staff can restock medicines and view logs', function () {
    $medicine = UksMedicine::create([
        'name' => 'Paracetamol 500mg',
        'category' => 'Obat Umum',
        'stock' => 10,
        'min_stock' => 5,
    ]);

    // Post restock
    $response = $this->actingAs($this->admin)->post(route('uks.medicines.restock.store'), [
        'uks_medicine_id' => $medicine->id,
        'quantity' => 20,
        'notes' => 'Restock bulanan',
    ]);

    $response->assertRedirect(route('uks.medicines.restock-log'));
    expect($medicine->refresh()->stock)->toBe(30);

    $this->assertDatabaseHas('uks_medicine_restocks', [
        'uks_medicine_id' => $medicine->id,
        'quantity' => 20,
        'restocked_by' => $this->admin->id,
    ]);

    // View logs
    $response = $this->actingAs($this->admin)->get(route('uks.medicines.dispense-log'));
    $response->assertStatus(200);

    $response = $this->actingAs($this->admin)->get(route('uks.medicines.restock-log'));
    $response->assertStatus(200);
});

test('staff can edit UKS visit record', function () {
    $medicine = UksMedicine::create([
        'name' => 'Antasida',
        'category' => 'Obat Umum',
        'stock' => 10,
        'min_stock' => 2,
    ]);

    $visit = UksVisit::create([
        'student_id' => $this->student->id,
        'visit_time' => now()->subDay()->format('Y-m-d H:i:s'),
        'complaint' => 'Sakit maag',
        'severity' => 'ringan',
        'disposition' => 'istirahat_uks',
        'handled_by' => $this->guru->id,
    ]);
    $visit->medicines()->attach($medicine->id, ['quantity' => 2]);
    $medicine->decrement('stock', 2);

    expect($medicine->refresh()->stock)->toBe(8);

    // Edit visit time and complaint, change medicine quantity to 3
    $response = $this->actingAs($this->admin)->put(route('uks.visits.update', $visit->id), [
        'student_id' => $this->student->id,
        'visit_time' => now()->format('Y-m-d\TH:i'),
        'complaint' => 'Sakit maag akut',
        'severity' => 'sedang',
        'disposition' => 'pulang',
        'medicines' => [
            ['id' => $medicine->id, 'quantity' => 3]
        ]
    ]);

    $response->assertRedirect(route('uks.visits.index'));
    
    $visit->refresh();
    expect($visit->complaint)->toBe('Sakit maag akut');
    expect($visit->severity)->toBe('sedang');
    
    // Antasida stock should be 10 (original) - 3 (new qty) = 7
    expect($medicine->refresh()->stock)->toBe(7);
});
