# 🧪 Panduan & Manual Pengujian SISTA

Kualitas sistem SISTA dijaga ketat oleh ratusan skenario pengujian otomatis menggunakan **Pest PHP**. Dokumen ini menjelaskan cara menjalankan test, cakupan skenario utama, serta arsitektur database testing.

---

## 🚀 1. Cara Menjalankan Test Suite

Pastikan dependensi dev sudah terpasang, lalu jalankan perintah berikut di direktori root projek Anda:

### A. Menjalankan Semua Test
```bash
vendor/bin/pest
```

### B. Menjalankan Test untuk Modul Spesifik
```bash
# Uji modul keamanan (SIEM, Forced Logout)
vendor/bin/pest tests/Feature/Security/SecurityMonitorTest

# Uji modul custom report builder
vendor/bin/pest tests/Feature/ReportBuilderTest
```

### C. Menjalankan Test dengan Laporan Cakupan (Coverage)
```bash
vendor/bin/pest --coverage
```

---

## 📊 2. Cakupan Skenario Pengujian Utama

SISTA memiliki **428 skenario pengujian** (1.738 assertions) yang mencakup:

| Kelompok Fitur | Nama Berkas Test | Aspek Penting yang Diuji |
| :--- | :--- | :--- |
| **Security & SIEM** | `SecurityMonitorTest.php` | Pencatatan audit trail login sukses/gagal, kalkulasi brute-force, remote session tracking, serta pengujian forced-logout middleware saat sesi diterminasi admin. |
| **Report Builder** | `ReportBuilderTest.php` | Penyusunan query SQL dinamis yang aman dari SQL Injection, live preview AJAX (limit 10 baris), ekspor landscape PDF, dan ekspor biner Excel. |
| **Workflow Engine** | `WorkflowEngineTest.php` | Inisiasi alur persetujuan polimorfik, kenaikan langkah persetujuan bertahap, proteksi otorisasi peninjau, dan penolakan instan. |
| **e-Portfolio Siswa** | `PortfolioTest.php` | Pengunggahan draf portofolio siswa, pembatasan edit portofolio terverifikasi, penganugerahan lencana oleh BK, dan validasi tautan publik glassmorphism. |

---

## 💾 3. Konfigurasi SQLite Database Testing

Pengujian otomatis SISTA menggunakan database in-memory pengujian (`:memory:`) untuk kecepatan eksekusi maksimum. 