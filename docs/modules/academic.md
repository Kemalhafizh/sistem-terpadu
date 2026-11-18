# Modul Akademik SISTA

Modul Akademik merupakan jantung operasional pembelajaran harian sekolah pada platform **SISTA**. Modul ini mengotomasi administrasi kelas, penjadwalan pelajaran, serta evaluasi belajar siswa.

---

## 1. Komponen Utama

### A. Pengelolaan Tahun Akademik (`AcademicYearController`)
*   Mengatur status aktif tahun ajaran (contoh: `2025/2026`) beserta semester aktif (`Ganjil` atau `Genap`).
*   Hanya ada satu tahun akademik yang berstatus aktif di dalam sistem pada satu waktu.

### B. Struktur Kelas & Ruang (`ClassroomController` & `RoomController`)
*   Mengatur alokasi rombongan belajar (rombel) siswa per tingkat kelas (`X`, `XI`, `XII`).
*   Setiap kelas dikaitkan dengan wali kelas (guru pengampu) dan kapasitas maksimum ruang kelas fisik.

### C. Jadwal Pelajaran (`ScheduleController`)
*   Mata pelajaran dikaitkan dengan guru pengampu, kelas target, jam mulai/selesai, serta alokasi ruangan.
*   Sistem mencegah terjadinya bentrok jadwal mengajar guru pada hari dan jam yang sama.

---

## 2. Rapor Digital & Penilaian (E-Rapor)

### A. Lembar Penilaian (`GradeController`)
*   Guru dapat menginput nilai tugas, ulangan harian, Penilaian Tengah Semester (PTS), dan Penilaian Akhir Semester (PAS) per siswa berdasarkan mata pelajaran yang diampu.

### B. Penerbitan Rapor PDF (`ReportCardController`)
*   Wali kelas dapat menginput catatan wali kelas (*homeroom notes*), presensi siswa, serta catatan ekstrakurikuler.
*   Sistem menghitung rata-rata nilai secara otomatis dan menyusun peringkat kelas.
*   Rapor dapat dicetak secara massal dalam format **PDF Landscape A4** dengan kop formal sekolah bawaan.