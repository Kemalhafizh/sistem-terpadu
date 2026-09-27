<?php

use App\Models\AcademicYear;
use App\Models\Registration;
use App\Models\SpmbPeriod;
use App\Models\SpmbTrack;
use App\Models\SpmbWave;

// The SPMB endpoints no longer fall back to invented waves/tracks, so each
// test runs against a real open wave (same fixture shape as tests/Feature/PPDB).
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
        'name' => 'Jalur Tahfidz',
        'quota' => 20,
    ]);
});

function spmbRegistrationPayload(): array
{
    return [
        'full_name' => 'Fulan bin Fulan',
        'nisn' => '0098712345',
        'school_origin' => 'SMP Islam Sultan Agung 1',
        'phone_number' => '081234567890',
        'track_name' => 'Jalur Tahfidz',
    ];
}

test('public can view spmb waves and tracks', function () {
    $response = $this->getJson('/api/v1/spmb/waves');
    $response->assertStatus(200)
             ->assertJsonStructure(['success', 'data'])
             ->assertJsonPath('data.0.name', 'Gelombang 1')
             ->assertJsonPath('data.0.fee', 150000)
             ->assertJsonPath('data.0.tracks.0.name', 'Jalur Tahfidz');
});

test('public can submit spmb registration', function () {
    $response = $this->postJson('/api/v1/spmb/register', spmbRegistrationPayload());

    $response->assertStatus(201)
             ->assertJson(['success' => true])
             ->assertJsonPath('data.track', 'Jalur Tahfidz');

    $this->assertDatabaseHas('registrations', [
        'registration_number' => $response->json('data.registration_number'),
        'nisn' => '0098712345',
        'spmb_wave_id' => $this->wave->id,
        'spmb_track_id' => $this->track->id,
        'status' => 'pending',
    ]);
});

test('public can track spmb registration status', function () {
    $number = $this->postJson('/api/v1/spmb/register', spmbRegistrationPayload())
        ->assertStatus(201)
        ->json('data.registration_number');

    $response = $this->getJson("/api/v1/spmb/status/{$number}");
    $response->assertStatus(200)
             ->assertJson(['success' => true])
             ->assertJsonPath('data.registration_number', $number)
             ->assertJsonPath('data.verification_status', 'menunggu')
             // No schedule columns exist yet — must stay null, not a made-up date.
             ->assertJsonPath('data.cbt_test_date', null)
             ->assertJsonPath('data.final_status', null);

    Registration::where('registration_number', $number)->update(['status' => 'accepted']);
    $this->getJson("/api/v1/spmb/status/{$number}")
         ->assertJsonPath('data.verification_status', 'terverifikasi')
         ->assertJsonPath('data.final_status', 'diterima');
});

test('unknown registration number returns 404', function () {
    $this->getJson('/api/v1/spmb/status/SPMB-2026-TIDAKADA')->assertStatus(404)
         ->assertJsonPath('success', false);
});

test('registration is refused when no wave is open', function () {
    $this->wave->update(['status' => 'closed']);

    $this->postJson('/api/v1/spmb/register', spmbRegistrationPayload())
         ->assertStatus(422)
         ->assertJsonPath('success', false);
    $this->assertDatabaseCount('registrations', 0);
});
