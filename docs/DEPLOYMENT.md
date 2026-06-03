# 🚀 Panduan Deployment Produksi SISTA

Dokumen ini memuat prosedur deployment **SISTA** di server produksi menggunakan arsitektur modern (VPS Ubuntu, Nginx, Systemd, dan Docker).

---

## 🖥️ Pendekatan 1: Deployment VPS Klasik (Ubuntu 22.04 / 24.04 LTS)

### 1. Instalasi Stack PHP 8.4 & Nginx
Tambahkan repositori PHP Ondrej, lalu instal Nginx beserta ekstensi PHP yang diperlukan:
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install nginx php8.4-fpm php8.4-cli php8.4-sqlite3 php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-gd -y
```

### 2. Kloning Projek & Konfigurasi Direktori
Kloning projek Anda ke `/var/www/sistem-terpadu`, sesuaikan kepemilikan folder ke user `www-data`:
```bash
sudo chown -R www-data:www-data /var/www/sistem-terpadu
sudo chmod -R 775 /var/www/sistem-terpadu/storage
sudo chmod -R 775 /var/www/sistem-terpadu/bootstrap/cache
```

### 3. Konfigurasi Nginx Server Block (Support Subdomain Wildcard)
Buat file konfigurasi Nginx baru di `/etc/nginx/sites-available/sista`:
```nginx
server {
    listen 80;
    server_name sista.test *.sista.test; # Ubah ke domain produksi Anda
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
Aktifkan site dan restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/sista /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 4. Menjalankan Laravel Reverb sebagai Background Service (Systemd)
Server WebSocket Reverb harus berjalan terus di latar belakang. Buat file systemd baru di `/etc/systemd/system/reverb.service`:
```ini
[Unit]
Description=Laravel Reverb Daemon
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php8.4 /var/www/sistem-terpadu/artisan reverb:start --host=127.0.0.1 --port=8080

[Install]
WantedBy=multi-user.target
```
Aktifkan dan jalankan service Reverb:
```bash
sudo systemctl daemon-reload
sudo systemctl enable reverb.service
sudo systemctl start reverb.service
```

---

## 🐳 Pendekatan 2: Deployment Berbasis Docker (Containerized)

SISTA menyediakan konfigurasi Docker multi-stage produksi siap pakai:

### 1. File: `Dockerfile`
Dockerfile dioptimasi menggunakan multi-stage build:
*   **Stage 1 (Node Builder):** Mengompilasi aset frontend menggunakan NPM.
*   **Stage 2 (PHP-Apache):** Menyalin kode sumber, dependensi Composer, aset frontend yang dikompilasi, serta mengonfigurasi Apache server dengan `mod_rewrite`.

### 2. File: `docker-compose.yml`
Menjalankan container aplikasi web SISTA dan penampung data database:
```yaml
version: '3.8'

services:
  web:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "80:80"
      - "8080:8080" # Untuk port websocket Reverb
    volumes:
      - ./database/tenants:/var/www/html/database/tenants
      - ./storage:/var/www/html/storage
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    restart: always
```
Jalankan container dengan perintah:
```bash
docker-compose up -d --build
```