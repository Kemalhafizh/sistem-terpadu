# 📜 Catatan Perubahan (Changelog) SISTA

Seluruh pembaruan penting pada basis kode **SISTA** dicatat di dokumen ini berdasarkan versi rilis.

---

## [2.0.0] - 2026-05-28
### 🚀 Rilis Besar: Edisi SaaS-Ready (Sistem Multi-Tenant)
Rilis ini mengubah total arsitektur SISTA dari aplikasi satu sekolah (*Single-School*) menjadi platform **Multi-Tenant SaaS Premium** skala nasional.

### Added
*   **Arsitektur Multi-Tenancy:** Integrasi `stancl/tenancy` dengan pendekatan Multi-Database (isolasi database fisik SQLite per sekolah).
*   **Onboarding Wizard Digital (`/onboard`):** Antarmuka pendaftaran sekolah mandiri berbasis AlpineJS dengan pengecekan duplikasi subdomain real-time (AJAX) dan loader migrasi visual.
*   **Landlord Super-Admin Panel (`/super-admin`):** Layar kontrol pusat untuk mengelola sekolah terdaftar, menangguhkan akun tenant, serta menghapus data tenant.
*   **Real-Time WebSocket Engine:** Mengintegrasikan **Laravel Reverb** untuk komunikasi instan.
*   **REST API Mobile-Ready (v1):** Kumpulan endpoint terstandardisasi JSON untuk aplikasi Android/iOS yang terproteksi Sanctum.
*   **Computer-Based Test (CBT):** Modul ujian online aman dengan fullscreen lock, visibility tab change blocker, mobile vibration shock, dan dasbor pemantauan real-time guru.
*   **Digital Approval Workflow:** Sistem persetujuan berjenjang dinamis polimorfik untuk penanganan cuti, surat, RPP, dll.
*   **Smart Recommendation Engine:** Deteksi dini siswa bermasalah secara akademis/BK serta prediksi keterlambatan pembayaran SPP.
*   **Custom Report Builder:** Pembuat laporan *ad-hoc* drag-drop dinamis dengan grafik ApexCharts dan ekspor Excel/PDF.
*   **e-Portfolio Siswa:** Portofolio digital mandiri dengan penganugerahan lencana prestasi terverifikasi sekolah serta halaman showcase publik berdesain glassmorphism.
*   **Security SIEM Dashboard:** Audit log autentikasi, deteksi brute-force, dan forced forced-logout sesi perangkat mencurigakan.
*   **CI/CD Pipeline:** Otomasi alur kerja pengujian dan deployment via GitHub Actions.

### Changed
*   **Refaktor Migrasi:** Memisahkan 81 tabel operasional sekolah ke dalam direktori `database/migrations/tenant/` dan mempertahankan tabel landlord (`tenants` dan `domains`) di direktori utama `database/migrations/`.
*   **Testing Bridge:** Konfigurasi khusus pada `TestCase` dan `AppServiceProvider` untuk memastikan kelancaran 80+ file pengujian legacy tetap berjalan lancar tanpa modifikasi.

---

## [1.5.0] - 2026-04-12
### Added
*   Sistem Compliance Dashboard terpadu untuk Akreditasi Sekolah.
*   Sistem Dashboard Orang Tua Premium (Parent Portal) dengan obrolan wali kelas.
*   Modul Helpdesk Internal (Tiket & SLA tracking) untuk pelaporan kerusakan fasilitas sekolah.
*   E-learning & Jurnal Mengajar Guru.

---

## [1.0.0] - 2026-02-10
### Added
*   Rilis awal fitur operasional sekolah SISTA: Akademik, Keuangan, Kepegawaian, Kesiswaan, Administrasi, Perpustakaan, SPMB, dan E-Rapor PDF.
*   Otentikasi 2FA (Email OTP), Enkripsi data sensitif, dan log audit aktivitas.