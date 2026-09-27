<?php

use App\Models\User;
use App\Support\VendorAsset;
use Illuminate\Support\Facades\Blade;

/*
 * Libraries that used to load from public CDNs are served from our Vite build
 * (App\Support\VendorAsset, resources/js/vendor-assets.js, vite.config.js).
 */

dataset('vendor files', function () {
    foreach (['SCRIPTS', 'STYLES', 'IMAGES'] as $group) {
        foreach (constant(VendorAsset::class.'::'.$group) as $name => $path) {
            yield "{$group} {$name}" => [$path];
        }
    }
});

test('setiap file vendor ada di node_modules dan didaftarkan ke Vite', function (string $path) {
    expect(base_path($path))->toBeFile();

    $viteSources = file_get_contents(resource_path('js/vendor-assets.js')).file_get_contents(base_path('vite.config.js'));
    expect($viteSources)->toContain(str_ends_with($path, '.css') ? "'{$path}'" : "'../../{$path}'");
})->with('vendor files');

test('komponen vendor merender tag classic script/stylesheet dari build sendiri', function () {
    $script = Blade::render('<x-vendor.script lib="katex-auto-render" defer onload="renderSistaLatex()" />');
    $style = Blade::render('<x-vendor.style lib="katex" />');

    expect($script)->toMatch('#^<script src="https?://[^"]+/build/assets/auto-render\.min-[\w-]+\.js" defer="defer" onload="renderSistaLatex\(\)"></script>#')
        ->not->toContain('type="module"')
        ->and($style)->toMatch('#^<link rel="stylesheet" href="https?://[^"]+/build/assets/katex-[\w-]+\.css"\s*>#');
});

test('nama library yang tidak dikenal langsung gagal', function () {
    VendorAsset::url('jquery');
})->throws(InvalidArgumentException::class, 'Unknown vendor asset [jquery].');

test('layout app merender script & style per halaman dari slot maupun @push', function () {
    // The layout used to print neither, so Leaflet (alumni map), Midtrans Snap
    // (checkout) and the chart code of a dozen pages never reached the browser.
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $this->get('/dashboard'); // a real request, so the layout has a route and session

    $html = Blade::render(<<<'BLADE'
        <x-app-layout>
            <x-slot name="styles"><style>.slot-style{}</style></x-slot>
            <x-slot name="scripts"><script>window.fromSlot = 1</script></x-slot>
            @push('styles')<style>.pushed-style{}</style>@endpush
            @push('scripts')<script>window.fromPush = 1</script>@endpush
            <p>isi halaman</p>
        </x-app-layout>
        BLADE);

    [$head, $body] = explode('</head>', $html, 2);
    expect($head)->toContain('.slot-style{}')->toContain('.pushed-style{}')
        ->and($body)->toContain('window.fromSlot = 1')->toContain('window.fromPush = 1')
        ->and(strpos($body, 'window.fromPush'))->toBeGreaterThan(strpos($body, 'isi halaman'));
});
