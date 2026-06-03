<?php

use App\Models\User;

test('guest can switch locale and it persists in session', function () {
    $response = $this->post(route('preferences.locale', 'en'));

    $response->assertSessionHas('locale', 'en');
    $response->assertRedirect();
});

test('guest can switch theme and it persists in session', function () {
    $response = $this->post(route('preferences.theme', 'dark'));

    $response->assertSessionHas('theme', 'dark');
    $response->assertRedirect();
});

test('authenticated user locale and theme preferences are saved to database', function () {
    $user = User::factory()->create([
        'preferred_locale' => 'id',
        'preferred_theme' => 'light',
    ]);

    $response = $this->actingAs($user)
        ->post(route('preferences.locale', 'en'));

    $response->assertSessionHas('locale', 'en');
    $user->refresh();
    $this->assertEquals('en', $user->preferred_locale);

    $responseTheme = $this->actingAs($user)
        ->post(route('preferences.theme', 'dark'));

    $responseTheme->assertSessionHas('theme', 'dark');
    $user->refresh();
    $this->assertEquals('dark', $user->preferred_theme);
});

test('invalid locale and theme are rejected', function () {
    $response = $this->post(route('preferences.locale', 'invalid'));
    $response->assertSessionHas('error');

    $responseTheme = $this->post(route('preferences.theme', 'invalid'));
    $responseTheme->assertSessionHas('error');
});

test('arabic locale applies dir rtl to html tag', function () {
    $user = User::factory()->create([
        'preferred_locale' => 'ar',
        'must_change_password' => false,
    ]);

    $response = $this->actingAs($user)
        ->get('/dashboard');

    $response->assertOk();
    $response->assertSee('dir="rtl"', false);
    $response->assertSee('lang="ar"', false);
});
