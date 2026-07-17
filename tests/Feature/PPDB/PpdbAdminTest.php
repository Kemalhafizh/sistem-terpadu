<?php

use App\Models\AcademicYear;
use App\Models\SpmbPeriod;
use App\Models\SpmbWave;
use App\Models\SpmbTrack;
use App\Models\Registration;
use App\Models\SpmbDocument;
use App\Models\User;

beforeEach(function () {
    $this->academicYear = AcademicYear::create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
        'start_date' => '2026-07-01',
        'end_date' => '2026-12-31',
    ]);

    $this->period = SpmbPeriod::create([
        'academic_year_id' => $this->academicYear->id,
        'name' => 'SPMB 2026/2027',
        'total_quota' => 100,
        'status' => 'open',
    ]);

    $this->wave = SpmbWave::create([
        'spmb_period_id' => $this->period->id,
        'name' => 'Gelombang 1',
        'registration_start' => '2026-07-01',
        'registration_end' => '2026-07-31',
        'announcement_date' => '2026-08-05',
        'quota' => 55,
        'registration_fee' => 150000,
        'status' => 'open',
    ]);

    $this->track = SpmbTrack::create([
        'spmb_period_id' => $this->period->id,
        'name' => 'Jalur Reguler',
        'quota' => 40,
    ]);

    $this->admin = User::factory()->create([
        'name' => 'PPDB Admin',
        'email' => 'ppdbadmin@sista.com',
        'role' => 'admin',
    ]);
});

test('admin can access spmb admin dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('spmb.admin.dashboard'));
    $response->assertStatus(200);
});

test('admin can create period', function () {
    $newYear = AcademicYear::create([
        'year' => '2027/2028',
        'semester' => 'Ganjil',
        'is_active' => false,
        'start_date' => '2027-07-01',
        'end_date' => '2027-12-31',
    ]);

    $response = $this->actingAs($this->admin)->post(route('spmb.admin.periods.store'), [
        'academic_year_id' => $newYear->id,
        'name' => 'SPMB Baru 2026',
        'total_quota' => 80,
        'status' => 'draft',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('spmb_periods', [
        'name' => 'SPMB Baru 2026',
    ]);
});

test('admin can create wave', function () {
    $response = $this->actingAs($this->admin)->post(route('spmb.admin.waves.store'), [
        'spmb_period_id' => $this->period->id,
        'name' => 'Gelombang Pendaftaran 3',
        'registration_start' => '2026-09-01',
        'registration_end' => '2026-09-30',
        'quota' => 30,
        'registration_fee' => 200000,
        'status' => 'upcoming',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('spmb_waves', [
        'name' => 'Gelombang Pendaftaran 3',
    ]);
});

test('admin can verify document', function () {
    $reg = Registration::create([
        'registration_number' => 'REG20260002',
        'full_name' => 'Budi Setiawan',
        'email' => 'budi@gmail.com',
        'nisn' => '3082918903',
        'previous_school' => 'SMP N 3 Semarang',
        'parent_phone' => '081299887755',
        'status' => 'draft',
        'academic_year_id' => $this->academicYear->id,
        'spmb_wave_id' => $this->wave->id,
        'spmb_track_id' => $this->track->id,
    ]);

    $doc = SpmbDocument::create([
        'registration_id' => $reg->id,
        'document_type' => 'kk',
        'file_path' => 'spmb/REG20260002/kk.pdf',
        'verification_status' => 'pending',
    ]);

    $response = $this->actingAs($this->admin)->post(route('spmb.admin.documents.verify', $doc), [
        'verification_status' => 'verified',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('spmb_documents', [
        'id' => $doc->id,
        'verification_status' => 'verified',
    ]);
});

test('admin can input entrance test scores', function () {
    $reg = Registration::create([
        'registration_number' => 'REG20260003',
        'full_name' => 'Candra Wijaya',
        'email' => 'candra@gmail.com',
        'nisn' => '3082918904',
        'previous_school' => 'SMP N 3 Semarang',
        'parent_phone' => '081299887755',
        'status' => 'verified',
        'academic_year_id' => $this->academicYear->id,
        'spmb_wave_id' => $this->wave->id,
        'spmb_track_id' => $this->track->id,
        'rapor_scores' => ['ipa' => 80],
    ]);

    $response = $this->actingAs($this->admin)->post(route('spmb.admin.scoring.store', $this->wave), [
        'scores' => [
            $reg->id => 90.00
        ]
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('registrations', [
        'id' => $reg->id,
        'entrance_test_score' => 90.00,
    ]);
});
