# Modul Kepegawaian & Penggajian (HRD) SISTA

Modul HRD mengelola seluruh administrasi staf pendidik (guru) dan kependidikan (TU/Karyawan), presensi dari mesin sidik jari, serta sistem kalkulasi penggajian bulanan otomatis.

---

## 1. Presensi dari Mesin Sidik Jari (`FingerprintImportTest`)

Guru dan karyawan presensi di **mesin sidik jari** sekolah; aplikasi tidak menyediakan check-in pegawai. Tata Usaha mengimpor log yang diekspor mesin di menu **Administrasi → Impor Sidik Jari** (`hrd/fingerprint-import`):
*   **Format fleksibel:** CSV, TXT, DAT, atau Excel. Nama kolom (ID mesin, tanggal/jam, atau tanggal dan jam terpisah), pemisah, dan format tanggal diatur di `config/fingerprint.php`, jadi tidak terikat satu merek mesin.
*   **Pemetaan ID:** setiap pegawai diisi "ID di Mesin Sidik Jari" pada data pegawainya. ID mesin yang belum dipetakan dilaporkan beserta jumlah scan-nya, bukan diabaikan diam-diam.
*   **Idempoten:** scan pertama sehari menjadi jam masuk, scan terakhir menjadi jam pulang, digabung dengan data yang sudah ada. Mengimpor file yang sama dua kali tidak menggandakan apa pun. Hari yang sudah tercatat izin, sakit, atau cuti tidak ditimpa.
*   **Terlambat:** scan pertama setelah `FINGERPRINT_LATE_AFTER` (bawaan 07:00) menandai hari itu terlambat.
*   **Rekap bulanan** (`EmployeeAttendanceRecap`): hadir, terlambat, izin/sakit/cuti, dan tidak hadir per pegawai.
    *   **Hari kerja** = hari kerja sekolah (`HRD_WORK_DAYS`, bawaan Senin–Jumat) yang tercakup data presensi (ada pegawai yang scan atau tercatat izin). Hari libur, saat tidak ada yang scan, dan hari sebelum log diimpor tidak dihitung. Scan di acara hari Sabtu tidak menjadikan Sabtu hari kerja bagi pegawai lain.
    *   Hari kerja tanpa scan dihitung **izin/sakit/cuti** bila ada pengajuan yang disetujui, selain itu **tidak hadir**. Hari sebelum tanggal mulai kerja pegawai tidak dihitung.
    *   Bulan tanpa data bernilai kosong (`null`), bukan 0.

---

## 2. Penggajian Karyawan (`PayrollController`)

Menu **Kepegawaian → Payroll** (admin dan kepala sekolah):
*   **Komponen slip:** gaji pokok pegawai, tunjangan dan potongan lain (kasbon, BPJS, dll.) yang diisi per slip, serta potongan kehadiran yang dihitung dari rekap presensi.
*   **Tarif potongan** per hari terlambat dan per hari tidak hadir diatur sekolah di halaman *Hitung Payroll*. Selama belum diatur, tidak ada potongan kehadiran; tidak ada tarif bawaan.
*   **Hitung ulang** aman: slip draf dihitung ulang dengan tunjangan dan potongan lain yang sudah diisi; slip yang sudah diterbitkan atau dibayar tidak diubah. Bulan tanpa data presensi tidak dipotong dan diberi peringatan.
*   **Terbitkan & bayar** bisa per slip atau sekaligus satu bulan. Slip yang sudah dibayar terkunci.
*   **Slip Gaji:** pegawai mengunduh slip yang sudah diterbitkan dalam format PDF dari portal pegawai.

## Pengajuan Izin/Cuti

Pegawai mengajukan izin, sakit, cuti, umroh/haji, atau dinas luar dari **Portal Pegawai** (lampiran opsional). Pengajuan langsung masuk alur persetujuan `leave_approval` (bawaan: Waka Kurikulum lalu Kepala Sekolah, bisa diubah di *Workflow → Definisi*). Persetujuan terakhir menandai pengajuan disetujui, sehingga hari-harinya tidak dihitung tidak hadir di rekap dan payroll. Pegawai melihat statusnya ("Menunggu Kepala Sekolah", "Disetujui", "Ditolak") di portal.

---

## 3. Jurnal Mengajar Guru (`TeachingJournalController`)

Setiap guru diwajibkan mengisi jurnal mengajar harian kelas setelah selesai jam pelajaran:
*   Jurnal mencatat hari/tanggal, kelas target, mata pelajaran, topik materi yang diajarkan, aktivitas siswa, serta kendala belajar mengajar.
*   Jurnal ditinjau secara berkala oleh Wakil Kepala Sekolah (Waka) bidang Kurikulum untuk menjaga standar mutu pembelajaran digital.