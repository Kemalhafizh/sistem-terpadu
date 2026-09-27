import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fs from 'fs';

const host = 'sistem-terpadu.test';
const certDir = 'C:/Users/kemalhafizh/.config/herd/config/valet/Certificates';
const keyPath = `${certDir}/${host}.key`;
const certPath = `${certDir}/${host}.crt`;

export default defineConfig(({ command }) => {
    // Herd HTTPS certs only exist on the local Windows dev machine; builds and CI skip them.
    const useHerdHttps = command === 'serve' && fs.existsSync(keyPath) && fs.existsSync(certPath);

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
        server: useHerdHttps
            ? {
                  host,
                  hmr: { host },
                  https: {
                      key: fs.readFileSync(keyPath),
                      cert: fs.readFileSync(certPath),
                  },
              }
            : {},
    };
});
