# 👥 Modul Kepegawaian & Penggajian (HRD) SISTA

Modul HRD mengelola seluruh administrasi staf pendidik (guru) dan kependidikan (TU/Karyawan), presensi dari mesin sidik jari, serta sistem kalkulasi penggajian bulanan otomatis.

---

## 📍 1. Presensi dari Mesin Sidik Jari (`FingerprintImportTest`)

Guru dan karyawan presensi di **mesin sidik jari** sekolah; aplikasi tidak menyediakan check-in pegawai (FASE 119). Tata Usaha mengimpor log yang diekspor mesin di menu **Administrasi → Impor Sidik Jari** (`hrd/fingerprint-import`):
*   **Format fleksibel:** CSV, TXT, DAT, atau Excel. Nama kolom (ID mesin, tanggal/jam, atau tanggal dan jam terpisah), pemisah, dan format tanggal diatur di `config/fingerprint.php`, jadi tidak terikat satu merek mesin.
*   **Pemetaan ID:** setiap pegawai diisi "ID di Mesin Sidik Jari" pada data pegawainya. ID mesin yang belum dipetakan dilaporkan beserta jumlah scan-nya, bukan diabaikan diam-diam.
*   **Idempoten:** scan pertama sehari menjadi jam masuk, scan terakhir menjadi jam pulang, digabung dengan data yang sudah ada. Mengimpor file yang sama dua kali tidak menggandakan apa pun. Hari yang sudah tercatat izin, sakit, atau cuti tidak ditimpa.
*   **Terlambat:** scan pertama setelah `FINGERPRINT_LATE_AFTER` (bawaan 07:00) menandai hari itu terlambat.
*   **Rekap bulanan** (`EmployeeAttendanceRecap`): hadir, terlambat, izin/sakit/cuti, dan tidak hadir per pegawai. Hari kerja dihitung dari hari yang tercakup data presensi, bukan diasumsikan 25; bulan tanpa data bernilai kosong (`null`), bukan 0.

---

## 💵 2. Penggajian Karyawan (`PayrollController`)

Staf HRD dapat mengelola perhitungan gaji bulanan secara komprehensif:
*   **Gaji Pokok & Tunjangan:** Ditetapkan berdasarkan golongan jabatan, pangkat PNS/Yayasan, dan jumlah tanggungan.
*   **Kalkulasi Otomatis:** Sistem mendeteksi log kehadiran karyawan bulanan untuk menghitung potongan keterlambatan (*late deduction*) atau alpa secara otomatis.
*   **Slip Gaji:** Staf menerima slip gaji digital terperinci yang dapat diunduh dalam format PDF.

---

## 📝 3. Jurnal Mengajar Guru (`TeachingJournalController`)

Setiap guru diwajibkan mengisi jurnal mengajar harian kelas setelah selesai jam pelajaran:
*   Jurnal mencatat hari/tanggal, kelas target, mata pelajaran, topik materi yang diajarkan, aktivitas siswa, serta kendala belajar mengajar.
*   Jurnal ditinjau secara berkala oleh Wakil Kepala Sekolah (Waka) bidang Kurikulum untuk menjaga standar mutu pembelajaran digital.