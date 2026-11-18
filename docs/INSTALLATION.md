# Panduan Instalasi Lingkungan Pengembangan SISTA

Dokumen ini menjelaskan langkah-langkah memasang dan menjalankan **SISTA (`sistem-terpadu`)** di lingkungan pengembangan lokal.

---

## Prasyarat Sistem

- **PHP:** v8.4 atau lebih baru (ekstensi: `pdo_sqlite`, `mbstring`, `xml`, `openssl`, `zip`, `gd`, `curl`)
- **Composer:** v2.6+
- **Node.js:** v20.x+ & **NPM** v10.x+
- **Git**

---

## Langkah Instalasi

### 1. Kloning Repositori
```bash
git clone https://github.com/Kemalhafizh/sistem-terpadu.git
cd sistem-terpadu
```

### 2. Pasang Dependensi PHP (Composer)
```bash
composer install
```

### 3. Konfigurasi Lingkungan (`.env`)
Salin berkas konfigurasi contoh:
```bash
cp .env.example .env
php artisan key:generate
```

Pastikan variabel dasar pada `.env` sesuai dengan lingkungan lokal:
```env
APP_NAME="SISTA"
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database

REVERB_APP_ID=sistem-terpadu
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### 4. Siapkan Berkas Database SQLite
```bash
touch database/database.sqlite
```

### 5. Jalankan Migrasi dan Seeder
```bash
php artisan migrate:fresh --seed
```

### 6. Pasang Dependensi Frontend & Kompilasi Aset
```bash
npm install
npm run build
```

---

## Menjalankan Server Pengembangan Lokal

Untuk menjalankan server web, queue listener, server WebSocket Reverb, dan Vite secara bersamaan:
```bash
composer run dev
```
atau jalankan server aplikasi secara terpisah:
```bash
php artisan serve --port=8000
php artisan queue:listen
php artisan reverb:start --port=8080
```
