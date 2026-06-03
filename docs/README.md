# 🚀 SISTA Developer Portal & Documentation Hub

Selamat datang di Portal Pengembang **SISTA (Sistem Informasi Sekolah Terpadu & SaaS Platform)**. Dokumen ini adalah panduan teknis komprehensif bagi developer, administrator sistem, dan kontributor yang ingin memahami, menjalankan, serta mengembangkan platform SISTA.

SISTA dirancang sebagai platform manajemen sekolah komersial skala **Enterprise-Grade** dengan dukungan **Multi-Tenancy SaaS**, WebSocket real-time, kecerdasan buatan prediktif, API mobile-ready, dan sistem pengawasan ujian online berkinerja tinggi.

---

## 🗺️ Peta Navigasi Dokumentasi

Gunakan menu berikut untuk menelusuri dokumentasi teknis secara menyeluruh:

### 🏗️ Arsitektur & Infrastruktur
*   [**ARCHITECTURE.md**](ARCHITECTURE.md): Struktur arsitektur sistem, desain database, dan diagram sequence alur multi-tenancy serta workflows.
*   [**MULTI_TENANCY.md**](MULTI_TENANCY.md): Panduan mendalam tentang setup multi-database tenant, subdomain routing, dan testing bridge.
*   [**adr/**](adr/) (Architecture Decision Records): Catatan keputusan arsitektural utama (Service Pattern, Reverb, SQLite Isolation).

### 💻 Panduan Developer & Operasional
*   [**INSTALLATION.md**](INSTALLATION.md): Langkah-langkah instalasi lokal lengkap (PHP 8.4, Composer, NPM, SQLite).
*   [**DEPLOYMENT.md**](DEPLOYMENT.md): Panduan deployment produksi menggunakan VPS, Docker, Systemd, dan Nginx.
*   [**TESTING.md**](TESTING.md): Manual pengujian menggunakan Pest PHP, coverage report, dan simulasi transaksi.
*   [**CONTRIBUTING.md**](CONTRIBUTING.md): Aturan kontribusi, standarisasi PSR-12, Laravel Pint, dan alur git.

### 🔌 API & Integrasi
*   [**API.md**](API.md): Dokumentasi lengkap REST API v1 untuk integrasi aplikasi mobile (Siswa, Wali, Guru).
*   [**SECURITY.md**](SECURITY.md): Kebijakan keamanan, otentikasi Sanctum, enkripsi BK, dan perlindungan brute force SIEM.

### 📦 Panduan Fitur & Modul Bisnis
*   [**modules/academic.md**](modules/academic.md): E-Rapor, jadwal, penjurnalan kelas, dan presensi.
*   [**modules/finance.md**](modules/finance.md): Billing massal, payment gateway Midtrans, audit ledger double-entry.
*   [**modules/hrd.md**](modules/hrd.md): GPS & Geofencing kehadiran, penggajian staf, dan jurnal mengajar.
*   [**modules/elearning.md**](modules/elearning.md): E-Learning, bank soal, pengerjaan ujian online, visibility tracking & anti-cheat CBT.
*   [**modules/communication.md**](modules/communication.md): WhatsApp Gateway transaksional, obrolan wali-guru real-time Reverb, forum diskusi akademik.
*   [**modules/import_export.md**](modules/import_export.md): Hub migrasi data massal, column mapping wizard, transactional imports, cascading rollbacks.
*   [**modules/workflow.md**](modules/workflow.md): Polymorphic approval workflow engine, visual stepper progress, automatic callback triggers.
*   [**modules/portfolio.md**](modules/portfolio.md): Portofolio pencapaian siswa, shareable public UUID links, lencana prestasi terverifikasi sekolah.
*   [**modules/security.md**](modules/security.md): Security monitoring SIEM, brute-force alarms, manajemen sesi aktif & forced logout.
*   [**modules/report_builder.md**](modules/report_builder.md): Drag-drop custom report builder, dynamic SQL query compiler, ApexCharts visualizer, PDF/Excel exports.
*   [**modules/chatbot.md**](modules/chatbot.md): Asisten cerdas chatbot sekolah, fuzzy navigation matching, dan secure Eloquent query data translations (100% lokal bebas biaya).
*   [**modules/gamification.md**](modules/gamification.md): Sistem gamifikasi sekolah terpadu, papan peringkat visual 3 besar podium, level, streak presensi masuk kelas, setoran hafalan Al-Quran Tahfidz, mutaba'ah ibadah yaumi harian, lencana kelayakan otomatis.

---

## 🛠️ Ringkasan Fitur Premium (Fase 7 & 8)

SISTA bertransformasi menjadi platform SaaS premium dengan 10 modul mutakhir:
1.  **Real-Time WebSocket Engine:** Mengintegrasikan **Laravel Reverb** untuk obrolan pesan wali-guru real-time dan notifikasi in-app tanpa refresh.
2.  **REST API Mobile-Ready (v1):** Mendukung aplikasi mobile dengan perlindungan Sanctum ter-rotasi, rate limiting ketat, dan standardisasi envelope JSON.
3.  **Smart Recommendation Engine:** Deteksi dini siswa bermasalah secara akademis/BK serta prediksi keterlambatan pembayaran SPP menggunakan analitik prediktif harian.
4.  **Computer-Based Test (CBT) Aman:** Ujian online terproteksi fullscreen blocker, visibility tab tracking, copy-paste disabled, dan mobile vibration warning untuk mencegah kecurangan.
5.  **Digital Approval Workflow Engine:** Sistem pengajuan cuti, surat, dan dokumen berjenjang universal polimorfik.
6.  **Multi-Tenancy SaaS Architecture:** Arsitektur multi-database (SQLite terisolasi per sekolah) dengan onboarding wizard otomatis dan Super-Admin panel.
7.  **Custom Report Builder:** Pembuat laporan *ad-hoc* drag-drop dinamis dengan grafik ApexCharts dan ekspor Excel/PDF.
8.  **e-Portfolio Siswa:** Showcase publik pencapaian, organisasi, dan sertifikat berdesain glassmorphism terverifikasi sekolah dengan sistem penganugerahan lencana prestasi.
9.  **Monitoring Keamanan SIEM:** Dasbor audit trail login, pencegahan brute-force, dan forced-logout sesi mencurigakan secara real-time.
10. **CI/CD & Automated Pipeline:** Integrasi pipeline GitHub Actions untuk unit testing otomatis dan auto-deployment server.

---

*Hak Cipta © 2026 SISTA Integrated Smart School Platform. Seluruh Hak Cipta Dilindungi.*