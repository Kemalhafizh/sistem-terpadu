// Third-party browser libraries served from our own origin instead of public
// CDNs. `?url` makes Vite emit each file unchanged (hashed name, not bundled)
// and list it in the manifest, so Blade can load it as a classic <script>
// through App\Support\VendorAsset. Keep this list in sync with that class
// (tests/Feature/Security/VendorAssetsTest.php checks it).
import.meta.glob([
    '../../node_modules/alpinejs/dist/cdn.min.js',
    '../../node_modules/apexcharts/dist/apexcharts.min.js',
    '../../node_modules/chart.js/dist/chart.umd.min.js',
    '../../node_modules/fullcalendar/index.global.min.js',
    '../../node_modules/@fullcalendar/core/locales/id.global.min.js',
    '../../node_modules/@highlightjs/cdn-assets/highlight.min.js',
    '../../node_modules/html2canvas/dist/html2canvas.min.js',
    '../../node_modules/html5-qrcode/html5-qrcode.min.js',
    '../../node_modules/jets/jets.min.js',
    '../../node_modules/katex/dist/katex.min.js',
    '../../node_modules/katex/dist/contrib/auto-render.min.js',
    '../../node_modules/katex/dist/contrib/mhchem.min.js',
    '../../node_modules/leaflet/dist/leaflet.js',
    '../../node_modules/leaflet/dist/images/marker-icon.png',
    '../../node_modules/leaflet/dist/images/marker-icon-2x.png',
    '../../node_modules/leaflet/dist/images/marker-shadow.png',
    '../../node_modules/lodash/lodash.min.js',
    '../../node_modules/marked/lib/marked.umd.js',
    '../../node_modules/qrcodejs/qrcode.min.js',
    '../../node_modules/sweetalert2/dist/sweetalert2.all.min.js',
    '../../node_modules/@tailwindcss/browser/dist/index.global.js',
], { query: '?url', import: 'default', eager: true });
