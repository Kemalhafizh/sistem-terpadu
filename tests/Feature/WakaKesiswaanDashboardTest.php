<?php

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;

test('the waka kesiswaan dashboard loads its recent students', function () {
    // Student::with('classroom.user') asked for a relation Classroom does not have,
    // so this dashboard failed as soon as one student existed.
    Student::factory()->create([
        'user_id' => User::factory()->create(['role' => 'student', 'name' => 'Ani'])->id,
        'classroom_id' => Classroom::factory()->create()->id,
    ]);
    $waka = User::factory()->create(['role' => 'waka_kesiswaan', 'must_change_password' => false]);

    $this->actingAs($waka)->get(route('dashboard'))->assertOk();
});
