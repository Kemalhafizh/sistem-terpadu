# Riwayat Perubahan (Changelog) SISTA

Dokumen ini mencatat perubahan utama pada sistem **SISTA (Sistem Terpadu SMA Islam Sultan Agung 1 Semarang)**.

---

## [2.2.0] - 2026-11
### Penyelesaian Penuh Peta Alur Operasional Sekolah (`ALUR_KERJA.md`)
- **Jabatan Dinamis & Siklus Akademik Tahunan (`L1`–`L2`, `A0`–`A6`):** Katalog jabatan dan penugasan pejabat/Plt berbasis periode SK, kalender akademik berjenjang, survei peminatan kelas X dan plotting rombel XI, kenaikan kelas terintegrasi riwayat rombel (`class_enrollments`), kelulusan massal kelas XII ke buku alumni melalui pengesahan Kepala Sekolah (`graduation_approval`), serta mutasi siswa masuk/keluar (`mutation_approval`) dengan sinkronisasi status akun otomatis.
- **Layanan Mandiri Warga Sekolah (`C3`, `K1`, `K4`, `K5`):** Pengajuan izin/sakit siswa dari orang tua (termasuk persetujuan otomatis sakit 1 hari dan eskalasi tenggat), impor awal Dapodik, pengajuan perubahan data identitas berlampiran bukti privat, serta pembuatan akun massal dan reset password bertingkat.
- **e-Rapor Resmi, Projek P5, Persuratan & Buku Induk (`D4`, `D5`, `J1`, `J2`, `K2`):** Ekspor template nilai ke e-Rapor Kemendikdasmen dan penerbitan PDF rapor resmi per angkatan, penilaian Projek P5 (`MB`/`SB`/`BSH`/`SAB`) beserta Rapor Projek P5 terpisah, persetujuan surat keluar resmi ber-QR verifikasi, disposisi surat masuk, dan pencetakan Buku Induk Siswa lintas tahun ajaran.
- **Kedisiplinan Dinamis, Sarpras QR Terpadu & Mesin Anti-Macet (`E1`, `I3`, Kotak Masuk Persetujuan):** Ambang batas `SP1`/`SP2`/`SP3` dinamis yang memicu konseling BK wajib secara otomatis, konsolidasi tiket kerusakan QR Sarpras dengan penyimpanan foto bukti privat dan sinkronisasi kondisi aset, serta kotak masuk persetujuan terpadu lengkap dengan tenggat SLA (`due_at`), pengingat, dan eskalasi otomatis.

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