<?php

namespace App\Support;

use Illuminate\Support\Facades\Vite;
use InvalidArgumentException;

/**
 * Third-party browser libraries served from our own build instead of public
 * CDNs (jsDelivr, unpkg, cdnjs, cdn.tailwindcss.com).
 *
 * The files come from node_modules at a version locked by package-lock.json.
 * resources/js/vendor-assets.js makes Vite emit the .js files byte-for-byte
 * (hashed name, no bundling), so they still load as classic <script> tags and
 * expose the same globals (ApexCharts, Chart, Swal, QRCode, …) as the CDN
 * builds did. The .css files are Vite inputs so their relative url()s
 * (KaTeX fonts, Leaflet images) are rewritten to the emitted files.
 *
 * Usage in Blade: <x-vendor.script lib="chartjs" /> and
 * <x-vendor.style lib="katex" />, or VendorAsset::url('qrcodejs') where a
 * URL is needed inside JavaScript.
 */
class VendorAsset
{
    public const SCRIPTS = [
        'apexcharts' => 'node_modules/apexcharts/dist/apexcharts.min.js',
        'chartjs' => 'node_modules/chart.js/dist/chart.umd.min.js',
        'fullcalendar' => 'node_modules/fullcalendar/index.global.min.js',
        'fullcalendar-locale-id' => 'node_modules/@fullcalendar/core/locales/id.global.min.js',
        'highlightjs' => 'node_modules/@highlightjs/cdn-assets/highlight.min.js',
        'html2canvas' => 'node_modules/html2canvas/dist/html2canvas.min.js',
        'html5-qrcode' => 'node_modules/html5-qrcode/html5-qrcode.min.js',
        'jets' => 'node_modules/jets/jets.min.js',
        'katex' => 'node_modules/katex/dist/katex.min.js',
        'katex-auto-render' => 'node_modules/katex/dist/contrib/auto-render.min.js',
        'katex-mhchem' => 'node_modules/katex/dist/contrib/mhchem.min.js',
        'leaflet' => 'node_modules/leaflet/dist/leaflet.js',
        'lodash' => 'node_modules/lodash/lodash.min.js',
        'marked' => 'node_modules/marked/lib/marked.umd.js',
        'qrcodejs' => 'node_modules/qrcodejs/qrcode.min.js',
        'sweetalert2' => 'node_modules/sweetalert2/dist/sweetalert2.all.min.js',
        'tailwind-browser' => 'node_modules/@tailwindcss/browser/dist/index.global.js',
    ];

    public const STYLES = [
        'highlightjs-obsidian' => 'node_modules/@highlightjs/cdn-assets/styles/obsidian.min.css',
        'katex' => 'node_modules/katex/dist/katex.min.css',
        'leaflet' => 'node_modules/leaflet/dist/leaflet.css',
    ];

    public const IMAGES = [
        'leaflet-marker-icon' => 'node_modules/leaflet/dist/images/marker-icon.png',
        'leaflet-marker-icon-2x' => 'node_modules/leaflet/dist/images/marker-icon-2x.png',
        'leaflet-marker-shadow' => 'node_modules/leaflet/dist/images/marker-shadow.png',
    ];

    public static function url(string $lib): string
    {
        return Vite::asset(self::path(self::SCRIPTS, $lib));
    }

    public static function styleUrl(string $lib): string
    {
        return Vite::asset(self::path(self::STYLES, $lib));
    }

    public static function imageUrl(string $name): string
    {
        return Vite::asset(self::path(self::IMAGES, $name));
    }

    /** @param  array<string, string>  $map */
    private static function path(array $map, string $name): string
    {
        return $map[$name] ?? throw new InvalidArgumentException("Unknown vendor asset [{$name}].");
    }
}
