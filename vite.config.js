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
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host,
        hmr: { host },
        https,
    },
});
