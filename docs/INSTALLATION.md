# ⚙️ Panduan Instalasi Lokal SISTA

Ikuti panduan ini untuk memasang dan menjalankan **SISTA** di lingkungan pengembangan lokal Anda.

---

## 📋 Prasyarat Sistem

Pastikan komputer Anda memenuhi spesifikasi berikut sebelum memulai:
*   **PHP:** v8.4 atau lebih baru (wajib memiliki ekstensi `sqlite3`, `mbstring`, `xml`, `openssl`, `zip`, `gd`).
*   **Composer:** v2.6+
*   **Node.js:** v20.x+ & **NPM** v10.x+
*   **Git**
*   **OS:** Windows, macOS, atau Linux (Ubuntu/Debian).

---

## 🚀 Langkah Demi Langkah Instalasi

### Langkah 1: Kloning Repositori
Masuk ke terminal atau PowerShell Anda, klon kode sumber:
```bash
git clone https://github.com/portfolio/sista-terpadu.git sistem-terpadu
cd sistem-terpadu
```

### Langkah 2: Instal Dependensi PHP (Composer)
Jalankan instalasi dependensi vendor Laravel:
```bash
composer install
```

### Langkah 3: Konfigurasi Lingkungan (`.env`)
Salin file konfigurasi lingkungan bawaan:
```bash
cp .env.example .env
```
Buka file `.env` baru menggunakan editor teks, lalu pastikan parameter berikut teratur:
```env
APP_NAME="SISTA"
APP_ENV=local
APP_KEY= # Akan digenerate pada langkah berikutnya
APP_URL=http://sistem-terpadu.test:8000

DB_CONNECTION=sqlite
DB_DATABASE="C:\project\portofolio\project-super-web\sistem-terpadu\database\database.sqlite"

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database

REVERB_APP_ID=sistem-terpadu
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Langkah 4: Generate Application Key
```bash
php artisan key:generate
```

### Langkah 5: Persiapan Berkas Database Pusat
Buat berkas database SQLite kosong untuk landlord:
```bash
# Windows PowerShell
New-Item -ItemType File -Path database\database.sqlite -Force

# Linux / macOS
touch database/database.sqlite
```

### Langkah 6: Jalankan Migrasi Database Pusat (Landlord)
Jalankan migrasi tabel central untuk SaaS:
```bash
php artisan migrate
```
*(Ini akan membuat tabel `tenants` dan `domains` di dalam `database.sqlite`)*

### Langkah 7: Instal Dependensi Frontend (NPM)
Instal seluruh package javascript dan build asetnya:
```bash
npm install
npm run build
```

---

## 🖥️ Menjalankan Server Pengembangan Lokal

Untuk menjalankan seluruh ekosistem SISTA secara lokal (Web Server, Queue Jobs, dan Server WebSocket Reverb), jalankan satu perintah berikut:

```bash
npm run dev
```

Secara default, skrip `dev` di `package.json` telah dikonfigurasi untuk mengeksekusi `concurrently` guna menjalankan:
1.  **Laravel Web Server:** `php artisan serve --port=8000`
2.  **Queue Listener:** `php artisan queue:listen`
3.  **Reverb WebSocket Server:** `php artisan reverb:start --port=8080`
4.  **Vite Bundler:** `npm run dev`

---

## 🖧 Konfigurasi Local Virtual Hosts (Domain Mapping)

SaaS SISTA mendeteksi tenant berdasarkan subdomain. Silakan daftarkan domain-domain lokal berikut pada berkas hosts sistem operasi Anda:

*   **Berkas Linux/macOS:** `/etc/hosts`
*   **Berkas Windows (Notepad Admin):** `C:\Windows\System32\drivers\etc\hosts`

Tambahkan baris:
```text
127.0.0.1 sistem-terpadu.test
127.0.0.1 sista.test
127.0.0.1 sma1.sistem-terpadu.test
127.0.0.1 smp2.sistem-terpadu.test
```

Buka **`http://sistem-terpadu.test:8000/onboard`** di browser Anda untuk mulai menguji onboarding wizard digital SISTA!