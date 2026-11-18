# Panduan Penelusuran Fitur per Peran (Walkthrough)

Dokumen ini menjelaskan cara menguji alur kerja utama **SISTA** menggunakan akun data awal (*seeder*) pada lingkungan pengembangan lokal.

---

## Akun Pengujian Lokal

Kata sandi bawaan untuk seluruh akun di bawah ini adalah `password`:

| Peran | Email | Fokus Pengujian |
| :--- | :--- | :--- |
| Superadmin | `superadmin@sekolah.com` | Konfigurasi sistem, manajemen pengguna, backup, dan log audit |
| Kepala Sekolah | `kepala.sekolah@sultanagung1.sch.id` | Dasbor pimpinan, persetujuan alur kerja, RKAS |
| Waka Kurikulum | `waka.kurikulum@sultanagung1.sch.id` | Jadwal pelajaran, silabus/RPP, kalender akademik, monitoring sesi kelas |
| Waka Kesiswaan | `waka.kesiswaan@sultanagung1.sch.id` | Ekstrakurikuler, OSIS & MPK |
| Guru BK | `guru.bk@sultanagung1.sch.id` | Konseling terenkripsi, poin pelanggaran, laporan perundungan, pendidikan inklusi |
| Guru | `guru@sekolah.com` | Sesi kelas & QR presensi, jurnal mengajar, penilaian harian, UKS |
| Siswa | `demo.siswa1@student.sa1.sch.id` | Ujian CBT, e-learning, mutaba'ah harian, evaluasi guru |
| Orang Tua | `parent@sekolah.com` | Pemantauan kehadiran per mapel, nilai, tagihan SPP, dan chat wali kelas |

---

## 1. Alur Pimpinan & Persetujuan (`kepala.sekolah@sultanagung1.sch.id`)

1. Masuk menggunakan akun Kepala Sekolah.
2. Pada **Dasbor Pimpinan**, tinjau ringkasan kehadiran, akademik, dan indikator kesehatan keuangan yang dihitung dari transaksi nyata.
3. Buka menu **Persetujuan (Workflow)** untuk meninjau, menyetujui, atau menolak permohonan cuti pegawai, silabus, maupun anggaran RKAS.

---

## 2. Alur Bimbingan Konseling (`guru.bk@sultanagung1.sch.id`)

1. Masuk menggunakan akun Guru BK.
2. Buka **Bimbingan Konseling → Laporan Perundungan** untuk meninjau laporan masuk dari siswa/orang tua dan memperbarui status tindak lanjutnya.
3. Buka **Sesi Konseling** untuk mencatat sesi konseling baru; catatan masalah dan solusi dienkripsi secara otomatis di database.

---

## 3. Alur Orang Tua (`parent@sekolah.com`)

1. Masuk menggunakan akun Orang Tua.
2. Jika memiliki lebih dari satu anak terdaftar, pilih anak melalui pemilih siswa di bagian atas halaman.
3. Tinjau ringkasan kehadiran per mata pelajaran, nilai akademik, catatan mutaba'ah, serta daftar tagihan SPP anak.
4. Gunakan fitur **Pesan Wali Kelas** untuk berkomunikasi langsung dengan wali kelas anak.

---

## 4. Alur Siswa (`demo.siswa1@student.sa1.sch.id`)

1. Masuk menggunakan akun Siswa.
2. Buka menu **CBT** untuk mengikuti ujian aktif dan menguji pencatatan pelanggaran pindah tab/layar penuh.
3. Buka menu **Mutaba'ah** untuk mencatat pelaksanaan ibadah harian.

---

## 5. Alur Guru & Tata Usaha (`guru@sekolah.com` / `admin@sekolah.com`)

1. Masuk sebagai Guru untuk memulai **Sesi Kelas**, menampilkan QR presensi per mata pelajaran, mengisi **Jurnal Mengajar**, dan menginput **Penilaian Harian**.
2. Masuk sebagai Admin/TU untuk mengimpor log presensi pegawai pada menu **Kepegawaian → Impor Sidik Jari**, mengelola tagihan siswa, dan mengekspor data Dapodik/EMIS.
