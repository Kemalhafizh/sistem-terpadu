# Sistem Terpadu SMA Islam Sultan Agung 1 Semarang (SISTA)

Sistem Terpadu SMA Islam Sultan Agung 1 (SISTA) adalah aplikasi manajemen operasional sekolah (ERP) berbasis web dan REST API untuk **SMA Islam Sultan Agung 1 Semarang**. Aplikasi web digunakan untuk operasional administrasi, tata usaha, wakil kepala sekolah, dan kepala sekolah, sedangkan REST API (`/api/v1`) melayani aplikasi Android (`sista-android`) bagi guru, siswa, dan orang tua.

---

## Cakupan Modul

1. **Akademik:** Jadwal pelajaran, sesi kelas dan presensi QR per mata pelajaran, jurnal mengajar, perangkat ajar (silabus dan RPP), penilaian harian, remedial, ujian berbasis komputer (CBT) dan bank soal, e-learning (materi dan tugas), P5, serta rapor Kurikulum Merdeka.
2. **Kesiswaan:** Bimbingan Konseling (konseling terenkripsi, poin kedisiplinan, laporan perundungan TPPK, dan pendidikan inklusi/IEP), Unit Kesehatan Sekolah (UKS), prestasi siswa, ekstrakurikuler, OSIS/MPK, dan perpustakaan.
3. **Keislaman:** Tahfidz, Mutaba'ah Yaumiyah, dan Tahsin.
4. **Keuangan:** Tagihan siswa (SPP dan biaya pendidikan), pembayaran melalui Midtrans Virtual Account, beasiswa dan potongan, kas kecil, pembayaran vendor, RKAS/RAPBS, infaq, serta rekonsiliasi bank dan jurnal akuntansi.
5. **Kepegawaian (HRD):** Data guru dan karyawan, impor log presensi dari mesin sidik jari, pengajuan cuti berjenjang, penggajian (payroll), serta evaluasi kinerja guru (siswa dan 360 derajat).
6. **Sarana & Prasarana:** Inventaris aset, pelabelan QR, peminjaman aset, pelaporan kerusakan/perawatan aset, dan pemesanan ruangan.
7. **Administrasi & Komunikasi:** Penerimaan murid baru (SPMB), surat-menyurat, helpdesk internal, pengumuman, integrasi notifikasi WhatsApp (Fonnte), kalender akademik, obrolan wali murid dan wali kelas (Laravel Reverb), penjadwalan pertemuan orang tua dan guru, notulen rapat, survei, ekspor Dapodik/EMIS, serta direktori alumni dan tracer study.

---

## Spesifikasi Teknis

- **Backend:** Laravel 13, PHP 8.4
- **Database:** SQLite (pengembangan lokal dan pengujian), MySQL / PostgreSQL (produksi)
- **Frontend Web:** Blade, Tailwind CSS v4, Alpine.js, ApexCharts, FullCalendar v6
- **Real-Time & WebSocket:** Laravel Reverb
- **Autentikasi API:** Laravel Sanctum
- **Dokumen & Ekspor:** DomPDF (`barryvdh/laravel-dompdf`), Laravel Excel (`maatwebsite/excel`)
- **Pengujian & Analisis Statis:** Pest PHP / PHPUnit, PHPStan, Laravel Pint

---

## Instalasi Lokal

### Prasyarat
- PHP >= 8.4 (ekstensi: `pdo_sqlite` atau `pdo_mysql`/`pdo_pgsql`, `openssl`, `gd`, `zip`, `xml`, `mbstring`, `curl`)
- Composer >= 2.x
- Node.js >= 20.x & NPM

### Langkah Instalasi

1. **Salin repositori dan pasang dependensi:**
   ```bash
   git clone https://github.com/Kemalhafizh/sistem-terpadu.git
   cd sistem-terpadu
   composer install
   npm install && npm run build
   ```

2. **Konfigurasi lingkungan:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Siapkan database SQLite dan jalankan migrasi beserta data awal:**
   ```bash
   touch database/database.sqlite
   php artisan migrate:fresh --seed
   ```

4. **Jalankan server pengembangan:**
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses di `http://localhost:8000`.

---

## Menjalankan dengan Docker

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app npm install && docker compose exec app npm run build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh --seed
```

Untuk lingkungan produksi satu VPS, gunakan `docker-compose.prod.yml` sesuai panduan di [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).

---

## Akun Pengujian Lokal (Seeder)

Kata sandi bawaan untuk seluruh akun seeder pengembangan adalah `password`:

| Peran | Email | Cakupan Akses Utama |
| :--- | :--- | :--- |
| Superadmin | `superadmin@sekolah.com` | Konfigurasi sistem, manajemen pengguna, backup |
| Admin | `admin@sekolah.com` | Administrasi sistem, audit log, pengumuman |
| Kepala Sekolah | `kepala.sekolah@sultanagung1.sch.id` | Dasbor pimpinan, persetujuan alur kerja, RKAS |
| Waka Kurikulum | `waka.kurikulum@sultanagung1.sch.id` | Jadwal, kurikulum, kalender akademik, sesi kelas |
| Waka Kesiswaan | `waka.kesiswaan@sultanagung1.sch.id` | Ekstrakurikuler, OSIS/MPK, kesiswaan |
| Guru BK | `guru.bk@sultanagung1.sch.id` | Konseling, poin pelanggaran, laporan perundungan, inklusi |
| Guru | `guru@sekolah.com` | Sesi kelas, presensi, jurnal mengajar, penilaian |
| Siswa | `demo.siswa1@student.sa1.sch.id` | Portal siswa, CBT, e-learning, mutaba'ah |
| Orang Tua | `parent@sekolah.com` | Portal orang tua, perkembangan anak, tagihan, chat wali kelas |

---

## Pengujian dan Pemeriksaan Kualitas Kode

```bash
# Format kode (PSR-12)
vendor/bin/pint --test

# Analisis statis
vendor/bin/phpstan analyse

# Seluruh pengujian otomatis
php artisan test
```

---

## Dokumentasi Teknis

Dokumentasi lengkap tersedia di direktori [`docs/`](docs/README.md):
- [Peta Alur Kerja Sekolah (`docs/ALUR_KERJA.md`)](docs/ALUR_KERJA.md)
- [Arsitektur Sistem (`docs/ARCHITECTURE.md`)](docs/ARCHITECTURE.md)
- [Skema Database (`docs/DATABASE.md`)](docs/DATABASE.md)
- [Dokumentasi REST API v1 (`docs/API.md`)](docs/API.md)
- [Panduan Kontribusi & Standar Kode (`docs/CONTRIBUTING.md`)](docs/CONTRIBUTING.md)
- [Panduan Deployment (`docs/DEPLOYMENT.md`)](docs/DEPLOYMENT.md)
