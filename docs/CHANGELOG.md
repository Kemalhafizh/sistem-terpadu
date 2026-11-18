# Riwayat Perubahan (Changelog) SISTA

Dokumen ini mencatat perubahan utama pada sistem **SISTA (Sistem Terpadu SMA Islam Sultan Agung 1 Semarang)**.

---

## [2.1.0] - 2026-10
### Perbaikan & Penyempurnaan Inti ERP Sekolah
- **Sesi Kelas & Presensi QR per Mata Pelajaran:** Penambahan modul sesi kelas langsung dengan QR berputar setiap 30 detik, integrasi otomatis ke draf jurnal mengajar, serta endpoint kehadiran harian untuk widget dan aplikasi Android.
- **Kepegawaian & Penggajian:** Penambahan fitur impor log mesin sidik jari pegawai (`FingerprintImportController`), perhitungan rekap bulanan (`EmployeeAttendanceRecap`), integrasi alur persetujuan cuti pegawai (`leave_approval`), dan perhitungan slip gaji dari data kehadiran nyata.
- **Kesiswaan & BK:** Penambahan endpoint dan antarmuka pelaporan perundungan (TPPK) dengan dukungan laporan anonim, serta penggabungan modul pendidikan inklusi di bawah Bimbingan Konseling.
- **Keuangan & RKAS:** Perbaikan alur persetujuan RKAS antara penyusun (TU) dan pengesah (Kepala Sekolah), pembacaan realisasi dari buku besar, serta isolasi pemotongan tabungan infaq per keluarga.
- **Integrasi Pemerintah:** Penyempurnaan validasi dan ekspor data Dapodik Kemendikdasmen serta EMIS Kemenag tanpa nilai bawaan fiktif, ditambah pemetaan lengkap konversi pendaftar SPMB menjadi siswa.
- **Keamanan & Kontrol Akses:** Penerapan *fail-closed* pada `RouteAccess`, pembatasan cakupan data siswa/kelas melalui `Student::visibleTo` dan `Classroom::isVisibleTo`, serta perampingan modul agar berfokus penuh pada kebutuhan operasional SMA Islam Sultan Agung 1 Semarang.

---

## [2.0.0] - 2026-05
### Penambahan
- Integrasi komunikasi waktu nyata menggunakan **Laravel Reverb** untuk obrolan wali murid–wali kelas dan notifikasi in-app.
- Penyediaan REST API v1 berbasis **Laravel Sanctum** untuk aplikasi Android.
- Modul Computer-Based Test (CBT) dengan pengacakan soal ber-seed dan pemantauan ujian.
- Mesin persetujuan berjenjang polimorfik (`WorkflowService`) untuk pengajuan surat, cuti, dan dokumen akademik.

---

## [1.0.0] - 2026-02
### Penambahan
- Rilis fondasi operasional sekolah: Akademik, Keuangan, Kepegawaian, Kesiswaan, Administrasi, Perpustakaan, UKS, SPMB, Tahfidz, Mutaba'ah, serta autentikasi 2FA dan enkripsi catatan konseling BK.