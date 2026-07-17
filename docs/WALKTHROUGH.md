# 🚶 Panduan Alur Demo Peran (Walkthrough Guide) SISTA

Panduan ini menjelaskan langkah-langkah demo sistem SISTA berdasarkan masing-masing peran (*role*) pengguna untuk membantu evaluasi fitur dan presentasi portofolio.

---

## 🔑 Kredensial Akses Uji Coba

Password untuk semua akun pengujian di bawah ini adalah **`password`**:

| Peran (Role) | Alamat Email Demo | Tujuan Utama Tinjauan |
| :--- | :--- | :--- |
| **Superadmin** | `superadmin@sekolah.com` | Konfigurasi sistem global, backup, log audit, and kelola akun. |
| **Kepala Sekolah** | `kepala.sekolah@sultanagung1.sch.id` | Dashboard analitik eksekutif, rekapitulasi mutu EDS, approval workflow. |
| **Waka Kurikulum** | `waka.kurikulum@sultanagung1.sch.id` | Manajemen kurikulum, approval bank silabus, kalender akademik. |
| **Waka Kesiswaan** | `waka.kesiswaan@sultanagung1.sch.id` | Manajemen ekstrakurikuler, pembinaan OSIS & MPK, approval proposal kegiatan. |
| **Guru BK (Konselor)** | `guru.bk@sultanagung1.sch.id` | Konseling BK terenkripsi, pemantauan status siswa beresiko (Smart Alerts). |
| **Guru / Petugas UKS** | `guru@sekolah.com` | Absensi harian kelas, rekam medis harian UKS, kurangi stok obat. |
| **Siswa (Student)** | `demo.siswa1@student.sa1.sch.id` | Pengerjaan ujian online (CBT), jurnal mutaba'ah ibadah, forum kelas. |
| **Orang Tua (Parent)** | `parent@sekolah.com` | Parent Portal (pantau rapor, kehadiran, keuangan SPP, & mutaba'ah anak). |

---

## 🚶 1. Alur Demo: Kepala Sekolah & Manajemen (Executive Dashboard & Approval)

Tujuan: Menguji analisis data eksekutif dan persetujuan pengajuan dokumen secara digital.

1. **Login** menggunakan akun **`kepala.sekolah@sultanagung1.sch.id`**.
2. **Lihat Dashboard Eksekutif**:
   - Amati grafik ApexCharts rata-rata nilai, status pembayaran SPP siswa, dan radar chart kepatuhan kurikulum.
3. **Approval Alur Kerja (Workflow Engine)**:
   - Masuk ke menu **Approval Workflow** > **Pending Approvals**.
   - Di sini Anda dapat menyetujui, menolak, atau meminta revisi pengajuan dari guru (seperti pengajuan cuti atau draf silabus mata pelajaran).
4. **Raport Mutu EDS**:
   - Masuk ke menu **Raport Mutu EDS** untuk melihat capaian 8 Standar Nasional Pendidikan (SNP) dalam diagram laba-laba (*spider chart*) beserta analisis kesenjangan (*Gap Analysis*).

---

## 🚶 2. Alur Demo: Guru BK & Smart Alerts (Intervensi Dini Siswa)

Tujuan: Menguji sistem analitik prediktif dan konseling siswa.

1. **Login** menggunakan akun **`guru.bk@sultanagung1.sch.id`**.
2. **Dashboard Smart Alerts**:
   - Masuk ke menu **Smart Alerts**. Anda akan melihat daftar siswa yang masuk kategori *At-Risk* secara otomatis terpicu oleh sistem (seperti kehadiran di bawah 80% atau nilai rata-rata di bawah KKM).
3. **Konseling BK Terenkripsi**:
   - Pilih salah satu siswa bermasalah, lalu buat pengajuan sesi konseling daring.
   - Isi formulir catatan konseling. Data catatan konseling ini akan langsung terenkripsi di database agar terjaga kerahasiaannya.

---

## 🚶 3. Alur Demo: Wali Murid / Orang Tua (Parent Portal Switching)

Tujuan: Menguji transparansi laporan perkembangan anak untuk wali murid.

1. **Login** menggunakan akun **`parent@sekolah.com`**.
2. **Dashboard Portal Orang Tua**:
   - Amati panel perkembangan akademis anak, sisa tagihan SPP, dan log catatan pelanggaran BK anak.
3. **Multi-Child Switcher**:
   - Jika orang tua terikat dengan lebih dari satu anak, gunakan dropdown **Pilih Anak** di bagian kanan atas dashboard untuk beralih data perkembangan anak secara instan.
4. **Log Mutaba'ah & UKS**:
   - Masuk ke tab **Mutaba'ah Ibadah** untuk memantau ibadah harian anak, serta tab **Rekam Medis UKS** untuk melihat log keluhan kesehatan anak selama di sekolah.

---

## 🚶 4. Alur Demo: Siswa (CBT Aman & Kolaborasi)

Tujuan: Menguji sistem e-learning terproteksi dan jurnal harian.

1. **Login** menggunakan akun **`demo.siswa1@student.sa1.sch.id`**.
2. **Ujian Online CBT**:
   - Masuk ke menu **CBT Online** > **Daftar Ujian**.
   - Mulai salah satu ujian aktif. Coba keluar dari layar fullscreen atau pindah tab browser; sistem akan mencatat deteksi kecurangan tersebut dan melarang pengerjaan jika batas pelanggaran terlampaui.
3. **Jurnal Mutaba'ah Harian**:
   - Masuk ke menu **Mutaba'ah Ibadah**. Isi setoran ibadah harian Anda (seperti shalat fardhu, tilawah Quran, dan bakti orang tua) untuk dinilai oleh guru/ortu.
4. **Forum Kelas**:
   - Buka menu **Forum Kelas** untuk berdiskusi dengan teman sekelas dan menanyakan pertanyaan kepada guru.

---

## 🚶 5. Alur Demo: Guru & Staf (Administrasi Kelas & UKS)

Tujuan: Menguji pengelolaan presensi, silabus kurikulum, dan pelayanan kesehatan UKS.

1. **Login** menggunakan akun **`guru@sekolah.com`**.
2. **Absensi & Jurnal Kelas**:
   - Buka menu **Akademik** > **Input Presensi**. Input data kehadiran siswa hari ini.
   - Buat entri **Jurnal Mengajar** harian (materi, penugasan).
3. **Buku Kunjungan UKS**:
   - Masuk ke menu **UKS** > **Kunjungan Baru**.
   - Input siswa yang berobat (misal: "Sakit Kepala"), berikan obat dari inventaris UKS. Sistem akan langsung memotong stok obat secara otomatis.
   - Jika keluhan berkategori `berat`, sistem secara otomatis akan mengirimkan pesan WhatsApp darurat ke orang tua siswa.

---

*Panduan alur demo ini mempermudah proses verifikasi fitur di lingkungan sandbox lokal.*
