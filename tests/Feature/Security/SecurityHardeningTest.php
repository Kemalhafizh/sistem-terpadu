<?php

use Illuminate\Support\Facades\Route;

/*
 * Findings from the OWASP ZAP baseline scan that runs in CI (security-scan.yml).
 */

test('CSP menetapkan direktif yang tidak punya fallback ke default-src (ZAP 10055)', function () {
    $csp = $this->get('/login')->assertOk()->headers->get('Content-Security-Policy');

    expect($csp)->toContain("base-uri 'self'")
        ->toContain("form-action 'self'")
        ->toContain("frame-ancestors 'none'")
        ->toContain("object-src 'none'");
});

test('header X-Powered-By tidak pernah dikirim (ZAP 10037)', function () {
    Route::get('/_test/powered-by', fn () => response('ok')->header('X-Powered-By', 'PHP/8.4.19'))->middleware('web');

    $response = $this->get('/_test/powered-by')->assertOk();

    expect($response->headers->has('X-Powered-By'))->toBeFalse();
});

test('pencarian berita memantulkan input dalam bentuk ter-escape, bukan HTML (ZAP 10031)', function () {
    $payload = '"><script>alert(1)</script><x a="';

    $this->get('/berita?'.http_build_query(['search' => $payload, 'category' => 'berita"><img src=x onerror=alert(2)>']))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('onerror=alert(2)>', false)
        ->assertSee('value="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;&lt;x a=&quot;"', false);
});

test('dokumentasi API tidak memuat lodash versi rentan', function () {
    // resources/views/scribe is generated from Scribe's template, which still
    // pins lodash 4.17.10 (prototype pollution, fixed in 4.17.21). Regenerating
    // the docs brings it back — bump it again when this fails.
    $docs = file_get_contents(resource_path('views/scribe/index.blade.php'));

    preg_match_all('#lodash@(\d+)\.(\d+)\.(\d+)#', $docs, $versions, PREG_SET_ORDER);

    expect($versions)->not->toBeEmpty();
    foreach ($versions as [, $major, $minor, $patch]) {
        expect(version_compare("{$major}.{$minor}.{$patch}", '4.17.21', '>='))->toBeTrue();
    }
});
