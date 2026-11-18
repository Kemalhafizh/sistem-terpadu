<?php

test('public registration screen is disabled', function () {
    $this->get('/register')->assertNotFound();
});

test('public users cannot self-register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'SecurePassword123',
        'password_confirmation' => 'SecurePassword123',
    ]);

    $response->assertNotFound();
    $this->assertGuest();
});
