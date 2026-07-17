<?php

use App\Models\Extracurricular;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->teacher = User::factory()->create([
        'role' => 'guru',
    ]);
});

test('admin can access extracurricular list', function () {
    $response = $this->actingAs($this->admin)->get(route('ekskul.manage'));
    $response->assertStatus(200);
});

test('admin can create extracurricular club', function () {
    $response = $this->actingAs($this->admin)->post(route('ekskul.store'), [
        'name' => 'Klub Robotik',
        'description' => 'Mempelajari pemrograman mikro kontroler.',
        'advisor_id' => $this->teacher->id,
        'schedule_day' => 'Jumat',
        'schedule_time' => '14:00',
        'location' => 'Laboratorium Komputer',
        'max_members' => 30,
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('extracurriculars', [
        'name' => 'Klub Robotik',
        'advisor_id' => $this->teacher->id,
    ]);
});

test('admin can update extracurricular club details', function () {
    $club = Extracurricular::create([
        'name' => 'Klub Pramuka',
        'advisor_id' => $this->teacher->id,
        'schedule_day' => 'Sabtu',
        'schedule_time' => '10:00',
        'location' => 'Lapangan Utama',
        'max_members' => 100,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin)->put(route('ekskul.update', $club), [
        'name' => 'Pramuka Wajib',
        'advisor_id' => $this->teacher->id,
        'schedule_day' => 'Sabtu',
        'schedule_time' => '08:00',
        'location' => 'Lapangan Utama',
        'max_members' => 120,
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('extracurriculars', [
        'id' => $club->id,
        'name' => 'Pramuka Wajib',
        'max_members' => 120,
    ]);
});

test('admin can delete extracurricular club', function () {
    $club = Extracurricular::create([
        'name' => 'Klub Basket',
        'advisor_id' => $this->teacher->id,
        'schedule_day' => 'Rabu',
        'schedule_time' => '15:30',
        'location' => 'Lapangan Basket',
        'max_members' => 25,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin)->delete(route('ekskul.destroy', $club));

    $response->assertRedirect();
    $this->assertDatabaseMissing('extracurriculars', [
        'id' => $club->id,
    ]);
});
