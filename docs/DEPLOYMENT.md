# Panduan Deployment Produksi SISTA

Dokumen ini menjelaskan prosedur deployment **SISTA** pada server produksi (VPS Ubuntu/Debian) menggunakan Docker Compose maupun instalasi langsung Nginx + PHP-FPM + Systemd.

---

## 1. Deployment Menggunakan Docker Compose

Repositori menyediakan berkas `Dockerfile` (multi-stage build) dan `docker-compose.prod.yml` untuk menjalankan aplikasi di server produksi.

### Langkah Deployment
1. **Kloning repositori di server:**
   ```bash
   git clone https://github.com/Kemalhafizh/sistem-terpadu.git /var/www/sistem-terpadu
   cd /var/www/sistem-terpadu
   ```

2. **Siapkan berkas `.env` produksi:**
   ```bash
   cp .env.example .env
   ```
   Pastikan variabel berikut diatur untuk lingkungan produksi:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://sista.sultanagung1.sch.id
   ```

3. **Bangun dan jalankan container:**
   ```bash
   docker compose -f docker-compose.prod.yml up -d --build
   docker compose -f docker-compose.prod.yml exec app php artisan key:generate --force
   docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
   docker compose -f docker-compose.prod.yml exec app php artisan config:cache
   docker compose -f docker-compose.prod.yml exec app php artisan route:cache
   docker compose -f docker-compose.prod.yml exec app php artisan view:cache
   ```

---

## 2. Deployment VPS Langsung (Ubuntu / Debian, Nginx, PHP 8.4, Systemd)

### A. Instalasi Paket Server
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install nginx php8.4-fpm php8.4-cli php8.4-sqlite3 php8.4-mysql php8.4-pgsql \
  php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-gd unzip git -y
```

### B. Penyiapan Aplikasi
```bash
cd /var/www/sistem-terpadu
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.example .env
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo chown -R www-data:www-data /var/www/sistem-terpadu/storage /var/www/sistem-terpadu/bootstrap/cache
```

### C. Konfigurasi Nginx (`/etc/nginx/sites-available/sista`)
```nginx
server {
    listen 80;
    server_name sista.sultanagung1.sch.id;
    root /var/www/sistem-terpadu/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### D. Layanan Latar Belakang (Queue Worker, Reverb, dan Scheduler)

Buat unit systemd `/etc/systemd/system/sista-queue.service`:
```ini
[Unit]
Description=SISTA Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
WorkingDirectory=/var/www/sistem-terpadu
ExecStart=/usr/bin/php8.4 /var/www/sistem-terpadu/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

Buat unit systemd `/etc/systemd/system/sista-reverb.service`:
```ini
[Unit]
Description=SISTA Reverb WebSocket Server
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
WorkingDirectory=/var/www/sistem-terpadu
ExecStart=/usr/bin/php8.4 /var/www/sistem-terpadu/artisan reverb:start --host=127.0.0.1 --port=8080

[Install]
WantedBy=multi-user.target
```

Aktifkan kedua layanan dan tambahkan cron untuk scheduler Laravel (`* * * * * cd /var/www/sistem-terpadu && php artisan schedule:run >> /dev/null 2>&1`):
```bash
sudo systemctl daemon-reload
sudo systemctl enable --now sista-queue.service sista-reverb.service
```