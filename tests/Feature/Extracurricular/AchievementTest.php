<?php

use App\Models\Extracurricular;
use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->teacher = User::factory()->create([
        'role' => 'guru',
    ]);

    $this->club = Extracurricular::create([
        'name' => 'Futsal Club',
        'advisor_id' => $this->teacher->id,
        'schedule_day' => 'Kamis',
        'schedule_time' => '15:00',
        'location' => 'Lapangan Olahraga',
        'max_members' => 30,
        'is_active' => true,
    ]);

    $this->student = Student::factory()->create();
});

test('admin can access achievements page for extracurricular club', function () {
    $response = $this->actingAs($this->admin)->get(route('ekskul.achievements', $this->club));
    $response->assertStatus(200);
});

test('admin can log extracurricular achievement successfully', function () {
    $response = $this->actingAs($this->admin)->post(route('ekskul.achievements.store', $this->club), [
        'title' => 'Juara 1 Turnamen Futsal SMA Kota Semarang',
        'level' => 'kota',
        'event_date' => '2026-07-15',
        'organizer' => 'KONI Kota Semarang',
        'participants' => [$this->student->id],
        'notes' => 'Meraih juara 1 setelah mengalahkan SMA 2 di final.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('extracurricular_achievements', [
        'extracurricular_id' => $this->club->id,
        'title' => 'Juara 1 Turnamen Futsal SMA Kota Semarang',
        'level' => 'kota',
        'organizer' => 'KONI Kota Semarang',
    ]);
});
