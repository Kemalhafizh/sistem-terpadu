# Modul 8: Scheduled Reporting & Email Digest SISTA

Modul Scheduled Reporting & Email Digest merupakan mesin pelaporan otomatis (*automated reporting engine*) pada platform **SISTA** yang mengompilasi statistik operasional, finansial, dan akademik sekolah untuk dikirimkan secara berkala kepada pemangku kepentingan (Admin, Kepala Sekolah, Divisi Keuangan, dan Orang Tua Siswa) melalui saluran **Email** dan **WhatsApp**.

---

## 1. Komponen Utama & Arsitektur

### A. Model Data & Migrasi (`ScheduledReport`)
*   **Tabel Database (`scheduled_reports`)**: Menyimpan konfigurasi jadwal laporan.
*   **Kolom Penting**:
    *   `name`: Nama laporan (contoh: *Daily Admin Digest*).
    *   `report_type`: Kategori laporan (`daily_admin`, `weekly_kepsek`, `monthly_finance`, `weekly_parent`).
    *   `cron_expression`: Format cron standar untuk penjadwalan (contoh: `0 7 * * *` untuk setiap jam 7 pagi).
    *   `delivery_method`: Saluran pengiriman (`email`, `whatsapp`, atau `both`).
    *   `recipients`: Array JSON berisi penerima statis (email langsung) maupun dinamis (berdasarkan peran pengguna).
    *   `attach_pdf`: Flag boolean untuk menyertakan lampiran dokumen PDF resmi.
    *   `is_active`: Status aktif/nonaktif jadwal laporan.

### B. Mesin Digest & Pelaporan (`DigestService`)
*   **Kompilasi Metrik**:
    *   **Daily Admin**: Mengompilasi jumlah siswa alpa hari ini, tiket bantuan helpdesk baru dan tagihan SPP baru.
    *   **Weekly Kepsek**: Menghitung rata-rata tingkat kehadiran kelas mingguan, total pembayaran SPP masuk, jurnal mengajar terhambat, dan target pencapaian standar mutu sekolah.
    *   **Monthly Finance**: Menghitung total uang masuk bulanan, total sisa tunggakan, serta klasemen 5 siswa penunggak SPP terbesar.
    *   **Weekly Parent**: Menyusun laporan performa anak meliputi data alpa/terlambat, akumulasi poin BK baru, nilai akademik terbaru, dan pekerjaan rumah (PR) e-learning yang masih tertunda.
*   **Rendering HTML & PDF**:
    *   Mengonversi data statistik ke struktur tabel HTML responsif yang estetik di dalam badan email.
    *   Menghasilkan dokumen PDF formal dengan **Kop Surat Resmi SMA Islam Sultan Agung 1 Semarang** menggunakan pustaka `Barryvdh\DomPDF`.

---

## 2. Mekanisme Pengiriman Otomatis

### A. Console Command (`sista:send-digests`)
*   Perintah Artisan `php artisan sista:send-digests` berjalan secara otomatis di latar belakang (melalui Scheduler Laravel).
*   Perintah ini mengevaluasi ekspresi cron dari setiap laporan aktif menggunakan pustaka parser cron. Jika jadwal saat ini cocok, mesin akan mengeksekusi pengiriman laporan tersebut.

### B. Saluran Pengiriman Multi-Channel
1.  **Email (`ScheduledDigestMail`)**: Mengirimkan email berbasis HTML responsif dengan opsi lampiran PDF laporan resmi yang dibuat secara dinamis.
2.  **WhatsApp (`WhatsappService`)**: Mengirimkan pesan ringkasan singkat dalam format teks tebal (*bold markdown*) langsung ke nomor telepon penerima/orang tua siswa.

---

## 3. Dashboard Admin CRUD

*   **Antarmuka Pengguna (`ScheduledReportController` & `scheduled-reports/index.blade.php`)**:
    *   Menggunakan desain **glassmorphic dark-mode** premium yang responsif dengan aksen warna Emerald/Indigo.
    *   Menyediakan manajemen lengkap untuk membuat, melihat, memperbarui, dan menghapus jadwal pelaporan.
    *   Dilengkapi tombol interaktif untuk memicu pengiriman laporan langsung secara instan (*trigger manual*) guna keperluan pengujian/kebutuhan mendesak.

---

## 4. Pengujian & Verifikasi

Pengujian dilakukan secara otomatis menggunakan **Pest PHP** untuk menjamin kestabilan modul:
```bash
# Jalankan pengujian modul digest terjadwal
vendor/bin/pest tests/Feature/DigestTest.php
```
Pengujian ini mencakup:
*   Verifikasi hak akses (*middleware auth* dan proteksi admin).
*   Verifikasi CRUD konfigurasi laporan terjadwal.
*   Kompilasi metrik harian admin, mingguan kepsek, bulanan keuangan, dan mingguan orang tua agar 100% akurat.
*   Simulasi pengiriman email dan pengoperasian scheduler console command.
