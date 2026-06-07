# 📜 Catatan Perubahan (Changelog) SISTA

Seluruh pembaruan penting pada basis kode **SISTA** dicatat di dokumen ini berdasarkan versi rilis.

---

## [2.0.0] - 2026-05-28
### 🚀 Rilis Besar: Fitur Premium & Hardening
Rilis ini menghadirkan berbagai peningkatan fitur premium, komunikasi real-time, sistem workflow persetujuan, modul CBT ujian online aman, portal laporan kustom, dan optimalisasi keamanan.

### Added
*   **Real-Time WebSocket Engine:** Mengintegrasikan **Laravel Reverb** untuk komunikasi instan.
*   **REST API Mobile-Ready (v1):** Kumpulan endpoint terstandardisasi JSON untuk aplikasi Android/iOS yang terproteksi Sanctum.
*   **Computer-Based Test (CBT):** Modul ujian online aman dengan fullscreen lock, visibility tab change blocker, mobile vibration shock, dan dasbor pemantauan real-time guru.
*   **Digital Approval Workflow:** Sistem persetujuan berjenjang dinamis polimorfik untuk penanganan cuti, surat, RPP, dll.
*   **Smart Recommendation Engine:** Deteksi dini siswa bermasalah secara akademis/BK serta prediksi keterlambatan pembayaran SPP.
*   **Custom Report Builder:** Pembuat laporan *ad-hoc* drag-drop dinamis dengan grafik ApexCharts dan ekspor Excel/PDF.
*   **e-Portfolio Siswa:** Portofolio digital mandiri dengan penganugerahan lencana prestasi terverifikasi sekolah serta halaman showcase publik berdesain glassmorphism.
*   **Security SIEM Dashboard:** Audit log autentikasi, deteksi brute-force, dan forced forced-logout sesi perangkat mencurigakan.
*   **CI/CD Pipeline:** Otomasi alur kerja pengujian dan deployment via GitHub Actions.

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