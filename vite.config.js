import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fs from 'fs';

const host = 'sistem-terpadu.test';

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
        https: {
            key: fs.readFileSync(`C:/Users/kemalhafizh/.config/herd/config/valet/Certificates/${host}.key`),
            cert: fs.readFileSync(`C:/Users/kemalhafizh/.config/herd/config/valet/Certificates/${host}.crt`),
        },
    },
});
