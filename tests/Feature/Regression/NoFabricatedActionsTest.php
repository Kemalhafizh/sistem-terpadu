<?php

use App\Models\AlumniData;
use App\Models\Student;
use App\Models\User;
use App\Services\AlumniEnhancedService;

/*
 * Entries of the made-up-value DEBT list: alumni in a city without
 * coordinates were pinned at a random spot around Semarang. (The rule engine
 * and the AI copilot that also made things up were removed in .)
 */

// ── Alumni map ──────────────────────────────────────────────────────────────

test('kota alumni tanpa koordinat tidak ditaruh acak di peta', function () {
    foreach (['Semarang', 'Kota Antah', 'Kota Antah'] as $city) {
        AlumniData::create(['student_id' => Student::factory()->create()->id, 'graduation_year' => 2024, 'current_activity' => 'study', 'city' => $city]);
    }

    $geo = app(AlumniEnhancedService::class)->getAlumniGeography()->keyBy('city');

    expect($geo['Semarang'])->lat->toBe(-6.966667)->lng->toBe(110.416664)
        ->and($geo['Kota Antah'])->lat->toBeNull()->lng->toBeNull()->total->toBe(2)
        ->and(app(AlumniEnhancedService::class)->getAlumniGeography()->all())->toBe($geo->values()->all());

    $this->actingAs(User::factory()->create(['role' => 'guru']))
        ->get(route('alumni.network-map'))->assertOk()
        ->assertSee('Belum terpetakan')->assertSee('Kota Antah (2)');
});
