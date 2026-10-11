# 🚀 Sistem Terpadu SMA Islam Sultan Agung 1 (SISTA)

[![Laravel](https://img.shields.io/badge/Laravel-13.x-red.svg?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-blue.svg?style=flat-square&logo=php)](https://php.net)
[![Pest](https://img.shields.io/badge/Pest%20PHP-Testing-green.svg?style=flat-square)](https://pestphp.com)
[![PWA](https://img.shields.io/badge/PWA-Fully%20Supported-blueviolet.svg?style=flat-square)](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps)
[![Style](https://img.shields.io/badge/Code%20Style-Laravel%20Pint-lightgrey.svg?style=flat-square)](https://laravel.com/docs/11.x/pint)
[![Tailwind](https://img.shields.io/badge/TailwindCSS-v4.0-38bdf8.svg?style=flat-square&logo=tailwind-css)](https://tailwindcss.com)

**Sistem Terpadu SMA Islam Sultan Agung 1 (SISTA)** adalah platform manajemen digital yang dirancang **khusus dan eksklusif untuk SMA Islam Sultan Agung 1 Semarang** — sekolah Islam di bawah naungan **Yayasan Badan Wakaf Sultan Agung (YBWSA)**. Platform ini mendigitalisasi, menyatukan, dan mengoptimalkan seluruh roda operasional sekolah dalam satu sistem terintegrasi. Mengusung arsitektur modern berkinerja tinggi, sistem ini menjamin keamanan data sensitif, keandalan luring (*offline resilience*), serta integrasi analitik eksekutif secara *real-time*.

Platform ini dirancang dengan antarmuka **Premium Glassmorphism** yang adaptif di berbagai ukuran perangkat, mendukung mode gelap/terang secara mulus, dan kaya akan mikro-animasi interaktif menggunakan TailwindCSS v4 & Alpine.js.

---

## 🎨 Modul & Arsitektur Utama Sistem

Sistem Terpadu SISTA terbagi ke dalam modul-modul kokoh yang saling terintegrasi:

### 1. 🛡️ Keamanan Enterprise & RBAC (Role-Based Access Control)
* **Two-Factor Authentication (2FA)**: Pengamanan sesi masuk ganda berbasis email/SMS OTP.
* **Enkripsi Transparan**: Enkripsi data sensitif (catatan medis UKS, catatan konseling BK, data personal) langsung pada tingkat database menggunakan standard industri.
* **Audit Trail**: Logging komprehensif atas setiap aktivitas CRUD, perubahan status, dan login user ke database.

### 2. 📊 Analitik Eksekutif Lanjutan (`AnalyticsService`)
* **Dashboard Kepala Sekolah & Waka**: Menyajikan visualisasi interaktif perbandingan nilai, tingkat kehadiran, status keuangan, dan grafik radar kedisiplinan BK per kelas.
* **ApexCharts & Exporters**: Integrasi grafik interaktif ApexCharts.js serta ekspor laporan multi-sheet Excel dan PDF formal.

### 3. 📱 Progressive Web App (PWA) & Ketahanan Luring
* **Service Worker Cerdas**: Strategi caching tingkat lanjut (Cache-First untuk berkas statis, Stale-While-Revalidate untuk jadwal/kalender, Network-First untuk antarmuka HTML).
* **Glassmorphic Offline Page**: Halaman khusus luring premium yang responsif dengan status deteksi koneksi real-time.

### 4. 🧑‍⚕️ UKS Digital (Unit Kesehatan Sekolah)
* **Log Kunjungan Medis**: Rekam medis harian kunjungan siswa, diagnosis, tindakan, dan disposisi.
* **Manajemen Logistik Obat**: Pengurangan otomatis stok obat saat tindakan diberikan, lengkap dengan indikator kedaluwarsa dan ambang batas minimum.
* **Emergency WhatsApp Dispatcher**: Notifikasi otomatis via Fonnte WA Gateway kepada orang tua jika siswa mengalami keluhan medis dengan keparahan `berat` atau dirujuk ke RS.

### 5. 🧑‍🤝‍🧑 Konseling BK Online & Poin Kedisiplinan
* **Siklus Hidup Konseling**: Alur pengajuan sesi konseling daring terenkripsi bagi Siswa, Guru BK, dan Orang Tua.
* **Auto-Trigger Pelanggaran**: Hook otomatis yang akan langsung menginisiasi draf sesi konseling wajib dan mengirim WA darurat ke orang tua jika akumulasi poin pelanggaran siswa mencapai **`>= 50 Poin`**.

---

## 📦 FASE 5 & 6: Komunikasi, Kolaborasi, dan Tata Kelola Sekolah (NEW)

Fase terbaru ini melengkapi platform SISTA menjadi solusi 360-derajat paling komprehensif di Indonesia:

### 💬 1. Forum Diskusi & Ruang Kelas Virtual (Modul 1 - ✅ DONE)
* **Kolaborasi Kelas**: Guru dan siswa dapat membuka topik diskusi akademik terarah per kelas atau per mata pelajaran.
* **Fitur Premium**: Mendukung *pinned threads*, penguncian topik, markup "Jawaban Terbaik" oleh guru, unggahan lampiran materi, dan sistem MorphReactions (Like, Helpful, Insightful).

### 📋 2. Survei & Kuesioner Digital (Modul 2 - ✅ DONE)
* **Dynamic Survey Builder**: Pembuatan kuesioner secara dinamis didukung berbagai tipe pertanyaan (Likert Scale, Checkbox, Radio, Textarea, Date) menggunakan AlpineJS.
* **Target & Distribusi**: Distribusi tertarget kepada peran spesifik (Siswa, Orang Tua, Guru) atau kelas tertentu, mendukung respons anonim, serta visualisasi data analitik real-time.

### 🏗️ 3. Manajemen Inventaris & Peminjaman Aset Lanjutan (Modul 3 - ✅ DONE)
* **Workflow Peminjaman**: Pengajuan peminjaman sarana prasarana sekolah secara daring oleh guru/staf dengan approval Wakasarpras.
* **Kalkulasi Penyusutan & QR Code**: Generator stiker QR Code unik per aset untuk kemudahan pelabelan, serta kalkulasi otomatis nilai buku aset tahunan menggunakan metode garis lurus.

### 🎭 4. Pengelolaan Ekstrakurikuler & OSIS (Modul 4 - ✅ DONE)
* **Katalog & Keanggotaan**: Katalog pendaftaran ekskul bagi siswa, presensi presisi kegiatan ekskul, galeri dokumentasi foto, dan buku kas transaksi keuangan OSIS (Pemasukan/Pengeluaran).

### 🛡️ 5. Raport Mutu Sekolah (Evaluasi Diri Sekolah / EDS) (Modul 5 - ✅ DONE)
* **Evaluasi 8 SNP**: Evaluasi mandiri berkala berdasar 8 Standar Nasional Pendidikan (SNP) untuk persiapan akreditasi sekolah.
* **Visualisasi Radar**: ApexCharts Spider Chart rata-rata skor per standar, penyaringan otomatis *Gap Analysis* untuk indikator berskor rendah (< 3.00), serta cetak laporan PDF A4 dinas pendidikan.

### 👨‍👩‍👧‍👦 6. Dashboard Orang Tua Premium (Parent Portal) (Modul 6 - ✅ DONE)
* **Multi-Child Switcher**: Tab navigasi interaktif bagi orang tua yang memiliki lebih dari satu anak terdaftar.
* **Transparansi Penuh**: Akses langsung ke rapor online anak, jadwal pelajaran harian, log poin BK, riwayat kunjungan UKS, status pelunasan SPP, serta jalur pesan langsung ke Wali Kelas.

### 📚 7. Manajemen Kurikulum & Silabus Digital (Modul 7 - ✅ DONE)
* **Bank Silabus**: Sentralisasi dokumen silabus per jenjang dengan review approval Wakakurikulum.
* **RPP Creator**: Pembuatan rencana pelaksanaan pembelajaran (RPP) digital terstruktur dilengkapi file PDF lampiran pendukung.

### 🎫 8. Sistem Tiket Helpdesk Internal (Modul 8 - ✅ DONE)
* **Tiket Lifecycle**: Jalur pengaduan masalah sarpras dan IT internal bagi guru dan staf.
* **SLA & Komentar**: Penghitungan otomatis batas penanganan (SLA) berdasarkan prioritas (Low, Medium, High, Critical), penugasan staf TU, dan pelacakan timeline komentar internal.

### 📑 9. Audit & Compliance Dashboard (Persiapan Akreditasi) (Modul 9 - ✅ DONE)
* **10 Key Metrics Aggregator**: Dashboard pengambil kebijakan yang secara otomatis mengkalkulasi 10 metrik kesiapan akreditasi (rasio guru, rasio buku, kepatuhan RPP, dll) menjadi skor persentase kesiapan.
* **Milestones Timeline**: Daftar tugas persiapan visitasi interaktif, repositori arsip berkas bukti fisik per SNP, serta unduhan laporan PDF formal kesiapan akreditasi.

### 🌐 10. Landing Page CMS & Profil Publik (Modul 10 - ✅ DONE)
* **CMS Ringan**: Halaman depan sekolah dinamis yang dapat dikelola oleh Humas tanpa keahlian pemrograman (Visi-misi, pendaftaran SPMB, slider berita/prestasi, galeri foto, dan testimoni).

---

## ⚡ FASE 16-20: Layanan Penerimaan Siswa Baru, Manajemen Alumni, Tata Kelola SDM, OSIS & Ekstrakurikuler, Notifikasi Otomatis, dan Alur Persetujuan Kerja (NEW)

Fase ini menghadirkan fitur-fitur skala *enterprise-grade* untuk operasional dan keterlibatan komunitas sekolah:

### 🎓 1. Sistem SPMB Online Terpadu (Modul 16.1 - ✅ DONE)
* **Penerimaan Murid Baru**: Pendaftaran online terotomasi terhubung dengan gelombang pendaftaran, jalur masuk (Prestasi, Afiliasi, Reguler), pengunggahan berkas digital (KK, SKL), ujian masuk CBT, kelulusan otomatis, dan daftar ulang.

### 🎓 2. Tracer Study & Overhaul Portal Alumni (Modul 16.2 - ✅ DONE)
* **Pelacakan Karir**: Pengisian survei karir (kuliah, bekerja, wirausaha) pasca-kelulusan oleh alumni untuk menyusun metrik keterserapan industri pendukung akreditasi sekolah.

### 💼 3. Penguatan Kepegawaian & HRD (Modul 16.3 - ✅ DONE)
* **Manajemen SDM**: Evaluasi kinerja pegawai bulanan/semester (PKG), tracking keikutsertaan pelatihan Kurikulum Merdeka, serta manajemen arsip dokumen kepegawaian (SK, ijazah) lengkap dengan pengingat otomatis tanggal kedaluwarsa.

### ⚽ 4. Manajemen Ekstrakurikuler & OSIS (Modul 16.4 & 16.5 - ✅ DONE)
* **Kegiatan & Pemilu**: Pendaftaran anggota ekskul, pencatatan prestasi lomba, log kehadiran ekskul, kas keuangan OSIS, serta sistem Pemilu OSIS digital (E-Voting anonim dengan voter hash unik terverifikasi).

### 💬 5. Fonnte WhatsApp Gateway & Automations (Modul 18.1 - ✅ DONE)
* **Notifikasi Otomatis**: Integrasi Gateway WhatsApp untuk mengirim pesan tagihan SPP otomatis, presensi masuk/pulang, dan peringatan kesehatan UKS langsung ke nomor WhatsApp orang tua murid.

### 🔄 6. Digital Approval Workflow Engine (Modul 18.2 - ✅ DONE)
* **Alur Persetujuan**: Engine pengajuan berkas (cuti, surat dinas, silabus) berjenjang dengan SLA penanganan otomatis.

---

## ✂️ Cakupan: ERP untuk Satu Sekolah (FASE 119)

SISTA adalah ERP untuk **satu SMA Islam**, bukan platform korporat. Pada FASE 119 modul yang tidak dipakai siapa pun di sekolah dihapus permanen (kode, view, model, migrasi, dan test), antara lain: tools developer dan teknologi pamer (blockchain, IoT, observability), manajemen korporat dan marketing (RENSTRA, SOP, rekrutmen, omnichannel, majalah/podcast), serta gimmick (AI tutor/chatbot, gamifikasi XP, presensi GPS/QR dinamis/wajah, OCR, tanda tangan digital, smart campus, green school, knowledge hub, yayasan). Rinciannya ada di `implementation_plan.md` bagian FASE 119.

Yang dipertahankan dan diperkuat: data induk, akademik (jadwal, sesi kelas + presensi QR per mapel, jurnal, penilaian, rapor, CBT, e-learning), kesiswaan (BK termasuk inklusi dan perundungan, UKS, ekskul, OSIS/MPK, perpustakaan), keislaman (Tahfidz, Mutaba'ah, Tahsin), keuangan, kepegawaian, administrasi, komunikasi, dan sistem.

---

## 💻 Tech Stack Utama

* **Core Framework:** Laravel 13 & PHP 8.4 (PSR-12 Standardized via Laravel Pint)
* **Database Engine:** SQLite (Lokal) / MySQL & PostgreSQL (Produksi)
* **Frontend Core:** Tailwind CSS v4, Alpine.js, Blade Components
* **Dynamic Charting:** ApexCharts.js
* **Event Calendar:** FullCalendar.js v6
* **PDF Exporter:** Barryvdh Laravel-DomPDF
* **Excel Engine:** Maatwebsite Excel
* **Testing Library:** Pest PHP Testing Framework

---

## 🚀 Panduan Instalasi Lokal

### Persyaratan Awal
Pastikan server lokal Anda telah memiliki:
* PHP >= 8.4 (ekstensi: `pdo_sqlite`, `openssl`, `gd`, `zip`, `xml`, `mbstring`)
* Composer >= 2.x
* Node.js & NPM
* Database driver

### Langkah Instalasi
1. **Clone & Masuk Direktori**:
   ```bash
   git clone <repo-url> sista-sekolah
   cd sista-sekolah
   ```
2. **Pasang Dependensi**:
   ```bash
   composer install
   npm install && npm run build
   ```
3. **Konfigurasi Lingkungan (`.env`)**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. **Setup Database SQLite**:
   Buat file database kosong pada SQLite:
   ```bash
   # Windows PowerShell
   New-Item -ItemType File -Path database/database.sqlite -Force
   
   # Linux / macOS
   touch database/database.sqlite
   ```
5. **Migrasi & Seeding**:
   Jalankan migrasi database serta penyuntikan data seeder demo (menyediakan ~860 record demo akademik dan tata kelola sekolah):
   ```bash
   php artisan migrate:fresh --seed
   ```
6. **Jalankan Aplikasi**:
   ```bash
   php artisan serve
   ```
   Aplikasi siap diakses pada alamat [http://localhost:8000](http://localhost:8000).

---

## 🐳 Pemasangan Cepat dengan Docker

SISTA dilengkapi dengan konfigurasi Docker Compose teroptimasi menggunakan Multi-Stage Build:

1. **Salin Environment**:
   ```bash
   cp .env.example .env
   ```
2. **Up Container**:
   ```bash
   docker compose up -d
   ```
3. **Inisiasi Aplikasi Di Dalam Container**:
   ```bash
   docker compose exec app composer install
   docker compose exec app npm install
   docker compose exec app npm run build
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate:fresh --seed
   ```
   Aplikasi Anda siap diakses di alamat [http://localhost:8080](http://localhost:8080).

---

## 🔑 Kredensial Uji Coba Peran (Testing Accounts)

Password default untuk semua akun demo adalah **`password`**:

| Peran (Role) | Alamat Email Uji Coba | Akses Utama Modul |
| :--- | :--- | :--- |
| **Superadmin** | `superadmin@sekolah.com` | Akses Global, Backup, Kelola User, Konfigurasi |
| **Admin** | `admin@sekolah.com` | Penuh, Audit Trail, Pengumuman |
| **Kepala Sekolah** | `kepala.sekolah@sultanagung1.sch.id` | Dashboard Kepatuhan, Laporan Analitik, Eksport Excel |
| **Waka Kurikulum** | `waka.kurikulum@sultanagung1.sch.id` | Kurikulum, Bank Silabus, Evaluasi EDS, Kalender |
| **Waka Kesiswaan** | `waka.kesiswaan@sultanagung1.sch.id` | Ekstrakurikuler, Pendaftaran OSIS, Approval Kegiatan |
| **Guru BK (Konselor)** | `guru.bk@sultanagung1.sch.id` | Konseling BK Terenkripsi, Deteksi Akumulasi Pelanggaran |
| **Petugas UKS (Guru)** | `guru@sekolah.com` | Log Kunjungan Medis UKS, Pengurangan Stok Obat |
| **Siswa (Student)** | `demo.siswa1@student.sa1.sch.id` | Portal PWA, Forum Kelas, Pengajuan Konseling BK, Mutaba'ah |
| **Orang Tua (Parent)** | `parent@sekolah.com` | Parent Portal Premium, Mutaba'ah, Chat Wali Kelas |

---

## 🧪 Validasi Pengujian Otomatis (Pest PHP Test Suite)

Logika bisnis, RBAC, proteksi form, enkripsi, dan keandalan PDF dilindungi oleh pengujian unit dan feature otomatis:

```bash
# Jalankan seluruh test suite
php artisan test
```

### Menjalankan Uji Spesifik Modul Baru:
* **Uji Compliance & Akreditasi**: `php artisan test tests/Feature/ComplianceTest.php`
* **Uji Evaluasi Mutu EDS**: `php artisan test tests/Feature/EdsTest.php`
* **Uji Portal Orang Tua**: `php artisan test tests/Feature/ParentPortalTest.php`
* **Uji Kurikulum & Silabus**: `php artisan test tests/Feature/Academic/CurriculumTest.php`
* **Uji Helpdesk Internal**: `php artisan test tests/Feature/TicketTest.php`
* **Uji Forum Diskusi**: `php artisan test tests/Feature/Security/ForumTest.php`
* **Uji Kuesioner & Survei**: `php artisan test tests/Feature/Security/SurveyTest.php`

---

## 📚 Integrasi API Seluler & Scribe

SISTA menyediakan endpoint RESTful API teramankan menggunakan **Laravel Sanctum**:
* **Base URL Endpoint:** `/api/v1/`
* **Dokumentasi Scribe Interaktif:** Akses browser langsung pada [http://localhost:8000/docs](http://localhost:8000/docs) (setelah menjalankan server lokal).
