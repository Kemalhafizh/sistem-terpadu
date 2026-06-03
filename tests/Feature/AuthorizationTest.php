<?php

/** @var TestCase|stdClass $this */

use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah']);
    $this->wakaSarpras = User::factory()->create(['role' => 'waka_sarpras']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->student = User::factory()->create(['role' => 'student']);
});

test('admin and kepsek can access admin logs', function () {
    $this->actingAs($this->admin)->get(route('admin.logs'))->assertStatus(200);
    $this->actingAs($this->kepsek)->get(route('admin.logs'))->assertStatus(200);
});

test('guru and student cannot access admin logs', function () {
    $this->actingAs($this->guru)->get(route('admin.logs'))->assertStatus(403);
    $this->actingAs($this->student)->get(route('admin.logs'))->assertStatus(403);
});

test('admin and kepsek can access users management', function () {
    $this->actingAs($this->admin)->get(route('users.index'))->assertStatus(200);
    $this->actingAs($this->kepsek)->get(route('users.index'))->assertStatus(200);
});

test('guru cannot access users management', function () {
    $this->actingAs($this->guru)->get(route('users.index'))->assertStatus(403);
});

test('admin and waka sarpras can access assets', function () {
    $this->actingAs($this->admin)->get(route('assets.index'))->assertStatus(200);
    $this->actingAs($this->wakaSarpras)->get(route('assets.index'))->assertStatus(200);
});

test('guru cannot access assets', function () {
    $this->actingAs($this->guru)->get(route('assets.index'))->assertStatus(403);
});
