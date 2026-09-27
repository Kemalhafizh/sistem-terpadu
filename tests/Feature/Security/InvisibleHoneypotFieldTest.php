<?php

use App\Http\Middleware\InvisibleHoneypotMiddleware;
use Illuminate\Support\Facades\Cache;

test('invisible honeypot middleware intercepts bot form submissions with filled decoy field', function () {
    $field = InvisibleHoneypotMiddleware::HONEYPOT_FIELD;

    $response = $this->post('/login', [
        'email' => 'admin@sultanagung1.sch.id',
        'password' => 'password',
        $field => 'Automated Bot Input Value',
    ]);

    $response->assertStatus(403);
    $response->assertSee('CYBER THREAT INTERCEPTION NOTICE');

    $ip = '127.0.0.1';
    $banKey = 'banned_ip_'.md5($ip);
    expect(Cache::has($banKey))->toBeTrue();
});
