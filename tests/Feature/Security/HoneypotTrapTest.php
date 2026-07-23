<?php

use Illuminate\Support\Facades\Cache;

test('honeypot trap middleware intercepts canary endpoint scanning attempts and bans IP', function () {
    $canaryPaths = ['/.env', '/phpmyadmin', '/wp-login.php', '/admin/login.php'];

    foreach ($canaryPaths as $path) {
        $response = $this->get($path);
        $response->assertStatus(403);
        $response->assertSee('CYBER THREAT INTERCEPTION NOTICE');
        $response->assertSee('ACCESS TERMINATED');
    }

    $ip = '127.0.0.1';
    $banKey = 'banned_ip_' . md5($ip);
    expect(Cache::has($banKey))->toBeTrue();
});
