import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fs from 'fs';

const host = 'sistem-terpadu.test';

// Sertifikat Herd hanya ada di mesin dev Windows; di CI/Linux file ini tidak
// ada dan readFileSync membuat `npm run build` crash walau server dev tidak dipakai.
const certDir = 'C:/Users/kemalhafizh/.config/herd/config/valet/Certificates';
const certKey = `${certDir}/${host}.key`;
const certCrt = `${certDir}/${host}.crt`;
const https = fs.existsSync(certKey) && fs.existsSync(certCrt)
    ? { key: fs.readFileSync(certKey), cert: fs.readFileSync(certCrt) }
    : undefined;

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Libraries that used to come from public CDNs; see
                // app/Support/VendorAsset.php.
                'resources/js/vendor-assets.js',
                'node_modules/katex/dist/katex.min.css',
                'node_modules/leaflet/dist/leaflet.css',
                'node_modules/@highlightjs/cdn-assets/styles/obsidian.min.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        // Small files would otherwise be inlined as data: URIs and left out of
        // the manifest, so Vite::asset() couldn't find e.g. Leaflet's marker
        // icons. Vendor files are always emitted as real files.
        assetsInlineLimit: (file) => (file.includes('/node_modules/') ? false : undefined),
    },
    server: {
        host,
        hmr: { host },
        https,
    },
});
