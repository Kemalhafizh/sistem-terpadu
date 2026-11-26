# Dokumentasi Teknis SISTA - SMA Islam Sultan Agung 1 Semarang

Dokumen ini merupakan indeks panduan teknis bagi pengembang dan administrator sistem **SISTA (Sistem Terpadu SMA Islam Sultan Agung 1 Semarang)**.

---

## Peta Navigasi Dokumentasi

### Alur Kerja Bisnis
- [**ALUR_KERJA.md**](ALUR_KERJA.md): Peta alur kerja operasional sekolah (pemilik proses, pemicu, langkah, dan aturan persetujuan) untuk setiap modul.

### Arsitektur & Database
- [**ARCHITECTURE.md**](ARCHITECTURE.md): Struktur arsitektur aplikasi, pemisahan controller/service/policy, mesin persetujuan (`WorkflowService`), dan komunikasi WebSocket (Laravel Reverb).
- [**DATABASE.md**](DATABASE.md): Diagram relasi entitas (ERD) dan penjelasan tabel utama.
- [**adr/**](adr/): Catatan keputusan arsitektur (*Architecture Decision Records*).

### Instalasi, Pengujian & Operasional
- [**INSTALLATION.md**](INSTALLATION.md): Panduan instalasi lingkungan pengembangan lokal.
- [**DEPLOYMENT.md**](DEPLOYMENT.md): Panduan deployment server produksi menggunakan Nginx/Systemd atau Docker Compose.
- [**TESTING.md**](TESTING.md): Panduan menjalankan pengujian otomatis (Pest PHP), guard arsitektur, dan analisis statis (PHPStan).
- [**CONTRIBUTING.md**](CONTRIBUTING.md): Standar rekayasa perangkat lunak, aturan akses, kontrak API mobile, dan alur kerja Git.
- [**WALKTHROUGH.md**](WALKTHROUGH.md): Panduan penelusuran fitur berdasarkan peran pengguna pada lingkungan pengujian.
- [**CHANGELOG.md**](CHANGELOG.md): Riwayat perubahan versi sistem.

### API & Keamanan
- [**API.md**](API.md): Dokumentasi endpoint REST API v1 untuk aplikasi Android (`sista-android`).
- [**SECURITY.md**](SECURITY.md): Kebijakan autentikasi Sanctum & 2FA, kontrol akses berbasis peran, enkripsi data sensitif BK, dan pencatatan audit.

### Dokumentasi per Modul
- [**modules/academic.md**](modules/academic.md): Jadwal, penilaian, jurnal mengajar, dan rapor.
- [**modules/class_sessions.md**](modules/class_sessions.md): Sesi kelas langsung dan presensi QR berputar per mata pelajaran.
- [**modules/finance.md**](modules/finance.md): Tagihan siswa, pembayaran Virtual Account, kas kecil, RKAS, dan buku besar.
- [**modules/hrd.md**](modules/hrd.md): Impor presensi mesin sidik jari, rekap kehadiran bulanan, cuti, dan penggajian.
- [**modules/elearning.md**](modules/elearning.md): Materi pembelajaran, penugasan, bank soal, dan ujian CBT.
- [**modules/communication.md**](modules/communication.md): Pengumuman, WhatsApp Gateway (Fonnte), dan obrolan wali murid–wali kelas.
- [**modules/import_export.md**](modules/import_export.md): Impor dan ekspor data massal.
- [**modules/integration.md**](modules/integration.md): Ekspor data Dapodik Kemendikdasmen dan EMIS Kemenag.
- [**modules/reporting.md**](modules/reporting.md): Laporan terjadwal otomatis.
- [**modules/workflow.md**](modules/workflow.md): Mesin persetujuan berjenjang polimorfik (`WorkflowService`).
- [**modules/security.md**](modules/security.md): Pencatatan percobaan login dan manajemen sesi aktif.