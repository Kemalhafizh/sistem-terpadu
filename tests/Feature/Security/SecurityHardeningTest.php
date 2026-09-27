<?php

use App\Support\ContentSecurityPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Cookie;

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

test('tidak ada view yang memuat script atau stylesheet dari CDN publik (ZAP 10017, 90003)', function () {
    // Libraries come from our own build via <x-vendor.script>/<x-vendor.style>
    // (App\Support\VendorAsset). Allowed external origins: Google Fonts, and
    // Midtrans Snap through <x-midtrans-snap>. resources/views/scribe is
    // generated — `php artisan scribe:generate` puts its CDN links back.
    $pattern = '#<script[^>]*\ssrc=["\']https?://|\.src\s*=\s*["\']https?://|<link[^>]*href=["\']https?://(?!fonts\.(googleapis|gstatic)\.com)[^"\']+["\'][^>]*>#i';

    $offenders = collect(File::allFiles(resource_path('views')))
        ->flatMap(function (SplFileInfo $file) use ($pattern) {
            preg_match_all($pattern, $file->getContents(), $matches);

            return collect($matches[0])
                ->reject(fn (string $tag) => str_contains($tag, 'rel="preconnect"'))
                ->map(fn (string $tag) => $file->getRelativePathname().': '.$tag);
        });

    expect($offenders->all())->toBe([]);
});

test('CSP tidak lagi mengizinkan CDN publik dan WebSocket ke host mana pun', function () {
    $csp = $this->get('/login')->assertOk()->headers->get('Content-Security-Policy');

    expect($csp)->not->toContain('cdn.jsdelivr.net')
        ->not->toContain('unpkg.com')
        ->not->toContain('cdnjs.cloudflare.com')
        ->not->toMatch('/connect-src[^;]*\s(ws|wss):(\s|;)/');
});

test('CSP mengikuti mode Midtrans dan host Reverb dari config', function () {
    config([
        'services.midtrans.is_production' => true,
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.options' => ['host' => 'ws.sekolah.test', 'port' => 443, 'scheme' => 'https'],
    ]);

    $csp = ContentSecurityPolicy::header();

    expect($csp)->toContain("script-src 'self' 'unsafe-inline' 'unsafe-eval' https://app.midtrans.com https://api.midtrans.com")
        ->toContain('frame-src \'self\' https://app.midtrans.com https://www.google.com')
        ->toContain('connect-src \'self\' wss://ws.sekolah.test https://app.midtrans.com')
        ->not->toContain('sandbox');

    config([
        'services.midtrans.is_production' => false,
        'broadcasting.connections.reverb.options' => ['host' => 'localhost', 'port' => 8080, 'scheme' => 'http'],
    ]);

    expect(ContentSecurityPolicy::header())->toContain('connect-src \'self\' ws://localhost:8080 https://app.sandbox.midtrans.com https://api.sandbox.midtrans.com');
});

test('snap.js Midtrans dimuat dari host yang sama dengan API token Snap', function () {
    config(['services.midtrans.is_production' => false, 'services.midtrans.client_key' => 'SB-client']);
    expect(Blade::render('<x-midtrans-snap />'))
        ->toContain('src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-client"');

    config(['services.midtrans.is_production' => true]);
    expect(Blade::render('<x-midtrans-snap />'))->toContain('src="https://app.midtrans.com/snap/snap.js"');
});

test('semua cookie HttpOnly kecuali XSRF-TOKEN yang memang dibaca axios (ZAP 10010)', function () {
    $cookies = collect($this->get('/login')->assertOk()->headers->getCookies())
        ->mapWithKeys(fn (Cookie $cookie) => [$cookie->getName() => $cookie]);

    expect($cookies)->toHaveKey('XSRF-TOKEN')
        ->and($cookies['XSRF-TOKEN']->isHttpOnly())->toBeFalse();

    $cookies->except('XSRF-TOKEN')->each(
        fn (Cookie $cookie) => expect($cookie->isHttpOnly())->toBeTrue("Cookie {$cookie->getName()} tanpa HttpOnly")
    );
    expect($cookies->keys())->toContain(config('session.cookie'));
});
