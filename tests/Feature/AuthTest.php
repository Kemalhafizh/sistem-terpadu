<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user can see login page', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});

test('user can login with correct credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'must_change_password' => false,
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/dashboard');
});

test('user is forced to change password if flag is set', function () {
    $user = User::factory()->create([
        'must_change_password' => true,
    ]);

    $this->actingAs($user);

    // Try to access dashboard
    $response = $this->get('/dashboard');

    // Should be redirected to force-change-password
    $response->assertRedirect(route('password.force-change'));
});

test('user can change forced password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
        'must_change_password' => true,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('password.force-change.update'), [
        'current_password' => 'old-password',
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $response->assertRedirect('/dashboard');

    $user->refresh();
    $this->assertFalse($user->must_change_password);
    $this->assertTrue(Hash::check('new-secure-password', $user->password));
});
