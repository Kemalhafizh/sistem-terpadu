# 👥 Modul Kepegawaian & Penggajian (HRD) SISTA

Modul HRD mengelola seluruh administrasi staf pendidik (guru) dan kependidikan (TU/Karyawan), pelacakan absensi GPS, serta sistem kalkulasi penggajian bulanan otomatis.

---

## 📍 1. Absensi Kehadiran Geofencing GPS (`GpsAttendanceTest`)

Sistem mengamankan presensi karyawan dari kecurangan menggunakan **Protokol Geofencing GPS**:
*   **Lokasi Presisi:** Absensi hanya dapat diinput melalui peramban HP dengan sensor GPS aktif.
*   **Geofence Radius:** Karyawan wajib berada dalam radius **100 meter** dari koordinat pusat sekolah yang diatur di sistem panel. Jika berada di luar radius, presensi otomatis ditolak.
*   **Selfie Verification:** Karyawan wajib mengunggah foto swafoto (*selfie*) saat presensi masuk. Foto disimpan di storage server sebagai bukti kehadiran otentik.

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