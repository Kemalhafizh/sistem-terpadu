# Peta Alur Kerja Sekolah: SMA Islam Sultan Agung 1 Semarang

> **Tujuan Dokumen:** Dokumen ini mendefinisikan alur kerja operasional di SMA Islam Sultan Agung 1 Semarang: siapa melakukan apa, kapan, dan mekanisme persetujuannya di setiap modul SISTA.
>
> Status implementasi: `[Tersedia]` sudah berjalan di kode · `[Sebagian]` sebagian berjalan · `[Terjadwal]` dalam antrean pengembangan

---

## 1. Prinsip Alur

1. **Setiap alur punya satu pemilik peran.** Pemilik bertanggung jawab alur itu berjalan, walau banyak peran ikut serta.
2. **Setiap perubahan status tercatat:** siapa, kapan, dan catatannya (audit log). Tidak ada status yang berubah diam-diam.
3. **Persetujuan berjenjang memakai satu mesin:** `WorkflowService` (`workflow_definitions` → langkah per peran, bisa didelegasikan, revisi, ajukan ulang). Alur baru yang butuh persetujuan **wajib** memakainya, supaya pimpinan punya **satu kotak masuk "Menunggu Persetujuan Saya"**, bukan satu per modul.
4. **Penyusun tidak menyetujui buatannya sendiri.**
5. **Notifikasi mengikuti status, bukan tombol.** Setiap perpindahan status yang menunggu orang lain mengirim notifikasi (aplikasi/push, dan WA hanya untuk orang tua bila penting).
6. **Kanal mengikuti pemakai.** Pekerjaan meja (TU, waka, kepsek) ada di web. Pekerjaan di kelas dan di rumah (guru, siswa, orang tua) ada di Android, dan web tetap bisa sebagai cadangan.
7. **Satu aturan akses** (lihat `docs/CONTRIBUTING.md`): route memeriksa **hak akses**, bukan nama jabatan, karena nama jabatan bisa diubah Admin. Data dibatasi ke miliknya (ortu → anaknya, guru → kelas yang diajar tahun ini).
8. **Tidak ada tanggal kegiatan yang ditanam di kode.** Kapan semester mulai, kapan ujian, kapan survei peminatan, kapan rapor dibagi: semuanya berubah setiap tahun dan dibaca dari **Kalender Akademik** tahun ajaran itu (A0). Kode hanya mengenal *urutan* kegiatan, bukan bulannya.

## 1b. Anti-Macet: Alur Harus Mengalir

**Ukuran keberhasilan SISTA:** setiap alur jelas dan mengalir, tanpa ada pekerjaan yang tertahan di satu orang. Setiap alur yang punya langkah "menunggu orang" wajib memenuhi tujuh aturan ini.

1. **Mengetahui ≠ menyetujui.** Bila pimpinan cukup tahu, kirim notifikasi dan jangan tahan alurnya. Langkah persetujuan yang **menahan** hanya untuk yang memang wajib ditandatangani: rapor, kelulusan, surat keluar resmi, RKAS, gaji, mutasi, dan kalender akademik.
2. **Setiap langkah menunggu punya tenggat.** Dua jenis tenggat diatur di pengaturan alur:
   - pengingat otomatis kepada pemegang langkah sebelum tenggat;
   - setelah tenggat lewat, **eskalasi** ke cadangan yang ditetapkan (Plt, atau atasan langsung).

   Tidak ada langkah yang menunggu tanpa batas.
3. **Tidak ada langkah tanpa cadangan.** Setiap jabatan penyetuju punya pengganti saat berhalangan:
   - Plt atau delegasi lewat `WorkflowService::delegate`;
   - jabatan yang kosong (tidak ada pemegang aktif) langsung terlihat sebagai peringatan di layar Admin.
4. **Persetujuan massal untuk hal yang berulang.** Rapor disahkan per rombel atau per angkatan sekaligus, begitu pula slip gaji per bulan dan izin siswa per hari. Satu klik untuk banyak dokumen, dengan tetap bisa membuka satu per satu.
5. **Pengguna meninjau, bukan mengetik ulang.** Sistem menyiapkan draf dari data yang sudah ada:
   - jurnal dari sesi kelas;
   - kehadiran rapor dari presensi;
   - draf kenaikan dari rapor;
   - kalender, rombel, dan pejabat disalin dari tahun lalu.
6. **Batas nominal dan jenis diatur sekolah, bukan di kode.** Contohnya: kas kecil di bawah batas tertentu cukup disetujui Kepala TU, di atasnya Kepala Sekolah. Surat keterangan rutin boleh ditandatangani Kepala TU, surat keputusan oleh Kepala Sekolah. Bila batas belum diisi, langkahnya ke Kepala Sekolah. Langkah dan batas setiap alur diatur Admin seperti katalog jabatan.
7. **Antrean bisa dilihat.** Ada satu dasbor "Alur Tertahan": berapa dokumen menunggu di tiap langkah, berapa lama, dan di siapa. Bottleneck terlihat dari data, bukan dari keluhan.

### Beban persetujuan Kepala Sekolah (pengaturan bawaan sekolah)

| Alur | Sebelumnya | Bawaan baru |
|---|---|---|
| A0 Kalender akademik | Kepsek mengesahkan | Tetap (sekali setahun) |
| A4 Kenaikan kelas | Waka menetapkan, kepsek bisa menahan | Tetap (tidak menahan bila kepsek diam) |
| A5 Kelulusan | Kepsek mengesahkan | Tetap, **massal satu angkatan** |
| A6 Mutasi | Kepsek menyetujui | Tetap |
| B1 SPMB | Kepsek mengesahkan | **Sekali per gelombang** (massal) |
| D5 Rapor | Kepsek mengesahkan di SISTA | **Disahkan di e-Rapor resmi.** SISTA hanya menerbitkan PDF-nya (Waka Kurikulum, massal) |
| G2 Beasiswa | Kepsek | Massal per periode |
| G3 Kas kecil | Kepsek | **Kepala TU di bawah batas nominal**, kepsek di atasnya |
| G5 RKAS | Kepsek | Tetap (sekali setahun + revisi) |
| H2 Cuti | Waka Kurikulum → Kepsek | **Guru → Waka Kurikulum, karyawan → Kepala TU.** Kepsek hanya untuk cuti panjang (batas hari di pengaturan) |
| H3 Gaji | Kepsek | Massal per bulan |
| J1 Surat keluar | Kepsek | **Surat rutin (keterangan aktif, dll.) → Kepala TU**, surat keputusan/resmi → kepsek |
| J2 Disposisi surat masuk | Kepsek | Tetap, dengan delegasi ke waka saat berhalangan |

Hasilnya, persetujuan rutin harian dan mingguan tidak lagi menumpuk di Kepala Sekolah. Yang tersisa adalah tanda tangan penting dan massal.

### Titik rawan macet lain

| Pemegang | Risiko | Penangkal |
|---|---|---|
| Waka Kurikulum (Mei–Juni) | Rancang & plot ±360 siswa, jadwal XI, kenaikan, kalender | Statistik + saran kecocokan, plotting massal, salin dari tahun lalu, tenggat dari kalender akademik |
| Wali kelas | Menyetujui izin siswa setiap hari | Izin **sakit 1 hari tercatat otomatis** (wali tetap diberi tahu dan bisa membatalkan). Izin lain yang tidak diputuskan sampai tenggat dieskalasi ke Waka Kesiswaan. Batasnya diatur sekolah |
| Guru mapel | Batas input nilai rapor | Pengingat dari kegiatan kunci "batas input nilai". Wali kelas melihat mapel mana yang belum lengkap |
| Kepala TU | Impor sidik jari, tagihan, surat | Impor file sekali per bulan, tagihan massal, template surat |



Akses seseorang adalah **gabungan** dari tiga lapis. Satu orang boleh memegang beberapa jabatan sekaligus (*double role*).

**Lapis 1: Jenis akun (statis, satu per orang, tetap di kode)**

| Jenis akun | Kanal utama | Berakhir (dinonaktifkan, tidak dihapus) |
|---|---|---|
| Siswa | Android | lulus / mutasi keluar |
| Orang Tua | Android | semua anak lulus / keluar |
| Guru | Android di kelas, Web | resign / pensiun |
| Karyawan | Web | resign / pensiun |

**Lapis 2: Jabatan (dinamis, katalognya diatur Admin di aplikasi)**

Jabatan bawaan, sesuai struktur sekolah: **Kepala Sekolah, Waka Kurikulum, Waka Kesiswaan, Waka Sarpras, Waka Humas, Kepala Perpustakaan, Kepala TU.**
- Admin bisa **menambah, mengubah, atau menonaktifkan** jabatan (misalnya Guru BK, Bendahara, Koordinator Keislaman) tanpa developer (alur L1).
- Setiap jabatan adalah **kumpulan hak akses** yang dipilih dari daftar tetap (L1). Jabatan dipegang orang tertentu dalam periode tertentu (L2).

**Lapis 3: Penugasan dari data (otomatis, bukan jabatan)**

| Penugasan | Dibaca dari |
|---|---|
| Guru mapel | jadwal pelajaran |
| Wali kelas | rombel |
| Pembina ekskul / OSIS / MPK | data ekskul atau organisasi |
| Ketua & wakil OSIS / MPK / ekskul | hasil pemilihan atau data anggota |
| Koordinator P5 | data projek |
| Guru wali (Kepmendikdasmen 221/P/2025, bila dipakai) | data pendampingan |

Akses lapis ini **terbatas pada objeknya**: wali kelas XI-3 hanya untuk rombel XI-3.

Akun teknis `superadmin` (Admin Utama) bukan bagian dari struktur sekolah. Akun ini hanya untuk tim IT.

## 3. Urutan Kegiatan Satu Tahun Ajaran

**Tanggalnya tidak tetap.** Setiap tahun sekolah menyusun **Kalender Akademik** sendiri (A0), mengacu ke kalender pendidikan Dinas Pendidikan Provinsi Jawa Tengah dan kalender keislaman (Ramadhan, Idul Fitri, Idul Adha bergeser ±11 hari tiap tahun). Yang tetap hanyalah **urutannya**:

```
① Kalender akademik TA baru disusun & disahkan        (sebelum TA mulai)
② TA baru aktif → rombel & wali kelas → jadwal → tagihan → MPLS siswa baru
③ Semester 1: KBM, presensi, jurnal, penilaian, PTS
④ Penilaian akhir semester 1 → rapor semester 1 disahkan & terbit
⑤ Semester 2 aktif (rombel TIDAK berubah)
⑥ SPMB untuk calon siswa baru (berjalan paralel)
⑦ Survei peminatan kelas X → statistik → rancang & plot rombel XI → jadwal XI
⑧ Penilaian akhir tahun → rapor semester 2 → RAPAT KENAIKAN → kelulusan XII
⑨ Kembali ke ① untuk TA berikutnya
```

Bulan yang biasa dipakai (misalnya Juli untuk awal TA) hanya **contoh**. Di sistem, setiap langkah membaca tanggalnya dari kegiatan kunci di kalender akademik TA itu.

---

## 4. Alur per Domain

Format setiap alur: **Pemilik** · **Pemicu** · **Langkah** · **Status** · **Keluaran** · **Kanal** · **Status implementasi**.

### A. Siklus Akademik & Rombel

#### A0. Kalender Akademik · `[Tersedia]`
- **Pemilik:** Waka Kurikulum. **Mengesahkan:** Kepala Sekolah.
- **Pemicu:** sebelum TA baru dimulai, setelah kalender pendidikan Dinas terbit. Bisa direvisi di tengah tahun.
- **Langkah:**
  1. Waka menyusun kalender TA baru: hari efektif, libur nasional dan keagamaan, serta **kegiatan kunci** (lihat di bawah).
  2. Kepala sekolah mengesahkan.
  3. Kalender terbit ke semua warga sekolah di web dan Android (J5).
  4. Revisi setelah terbit tercatat, dan semua yang terdampak diberi notifikasi.
- **Kegiatan kunci** adalah acara kalender yang diberi kode tetap, supaya alur lain bisa membaca tanggalnya, bukan menebak bulan:
  - awal/akhir TA dan tiap semester
  - PTS, penilaian akhir semester, penilaian akhir tahun
  - batas input nilai dan pembagian rapor tiap semester
  - jendela survei peminatan
  - rapat kenaikan kelas, kelulusan
  - MPLS
  - periode SPMB
- **Status:** `draf → disahkan → terbit` (revisi tercatat).
- **Kini:** `AcademicCalendar` per `SchoolYear` dilengkapi status pengesahan oleh Kepala Sekolah (`pimpinan.mengesahkan`), penanda tinjauan ulang (`needs_review`) saat disalin dari tahun sebelumnya, dan kode kegiatan kunci (`milestone`) pada `academic_events`.


#### A1. Pembukaan Tahun Ajaran & Semester · `[Tersedia]`
- **Pemilik:** Waka Kurikulum (eksekusi teknis oleh admin).
- **Pemicu:** tanggal awal TA dan awal semester 2 menurut Kalender Akademik (A0).
- **Langkah:**
  1. Buat TA baru (mis. 2027/2028) beserta dua semester. Tanggal awal dan akhirnya diambil dari kegiatan kunci kalender akademik.
  2. Salin struktur rombel dari TA lama sebagai draf.
  3. Tetapkan wali kelas.
  4. Aktifkan.

  Hanya satu TA dan satu semester yang aktif.
- **Status TA:** `draf → aktif → ditutup`.
- **Kini:** `school_years` menjadi induk tahun ajaran dengan dua semester `academic_years` dan relasi `school_year_id` pada `classrooms`.

#### A2. Penyusunan Rombel & Wali Kelas · `[Tersedia]`
- **Pemilik:** Waka Kurikulum. **Mengetahui:** Kepala Sekolah.
- **Langkah:**
  1. Rombel X diisi dari hasil SPMB.
  2. Rombel XI **dirancang dan diplot manual** oleh waka dari statistik survei peminatan (A3).
  3. Rombel XII dilanjutkan dari XI dengan susunan mapel yang sama.
  4. Wali kelas ditetapkan per rombel.
- **Keluaran:** riwayat `class_enrollments` (siswa × rombel × TA). Kolom `students.classroom_id` menjadi "rombel aktif" yang disinkronkan.

#### A3. Survei Peminatan, Rancangan Rombel XI & Plotting · `[Tersedia]`
- **Pemilik:** Waka Kurikulum (dibantu guru kurikulum dan BK). **Mengetahui:** Kepala Sekolah.
- **Pemicu:** jendela survei peminatan di Kalender Akademik (A0), biasanya di semester 2 kelas X.
- **Prinsip:** survei **hanya memetakan minat**. Sistem tidak mengalokasikan siswa sendiri dan tidak memakai kuota; Waka Kurikulum yang memutuskan berdasarkan statistik.
- **Langkah:**
  1. Waka membuka survei: mapel pilihan yang ditawarkan, dan berapa mapel yang harus dipilih siswa.
  2. Siswa kelas X memilih mapel yang diminati di Android. Orang tua bisa melihat pilihan anaknya.
  3. Survei ditutup, lalu **statistik** keluar: peminat per mapel, kombinasi yang paling sering dipilih bersama, sebaran per rombel X, dan siswa yang belum mengisi.
  4. Waka **merancang rombel XI**: jumlah rombel (misalnya 11), nama, dan susunan mapel pilihan tiap rombel.
  5. Waka **memplot siswa** ke rombel XI. Sistem hanya menampilkan kecocokan pilihan siswa dengan tiap rombel sebagai saran.
  6. Jadwal XI disusun dari susunan mapel tiap rombel (C1).
  7. Rombel tahun depan diumumkan ke siswa dan orang tua.
- **Status survei:** `draf → dibuka → ditutup → selesai`.
- **Kanal:** Web (waka: statistik, rancang rombel, plotting), Android (siswa mengisi, orang tua melihat).

#### A4. Kenaikan Kelas · `[Tersedia]`
- **Pemilik:** Waka Kurikulum, **dengan sepengetahuan Kepala Sekolah**.
- **Pemicu:** setelah rapor semester 2 disahkan, sesuai tanggal rapat kenaikan di Kalender Akademik.
- **Langkah:**
  1. Sistem menyusun **draf keputusan** per siswa: naik atau tinggal kelas, dengan dasar dari rapor dan presensi.
  2. Wali kelas meninjau draf kelasnya dan memberi catatan.
  3. Hasil dibawa ke rapat dewan guru (notulen di J8).
  4. Waka kurikulum menetapkan. Kepala sekolah menerima notifikasi "mengetahui" dan **dapat menahan** keputusan sebelum dieksekusi.
  5. Eksekusi atomik pada pembukaan TA baru (A1). Siswa X yang naik masuk ke rombel hasil plotting A3. Eksekusi bisa dibatalkan sebelum TA baru diaktifkan.
- **Status batch:** `draf → ditinjau_wali → ditetapkan → dieksekusi` (atau `dibatalkan`).
- **Keluaran:** enrollment TA lama ditutup dengan status `naik`/`tinggal`, dan enrollment TA baru dibuat.

#### A5. Kelulusan Kelas XII → Alumni · `[Sebagian]`
- **Pemilik:** Waka Kurikulum. **Mengesahkan:** Kepala Sekolah (SK kelulusan).
- **Langkah:**
  1. Disusun draf kelulusan.
  2. Rapat dewan guru.
  3. Kepala sekolah mengesahkan.
  4. Pengumuman ke siswa dan orang tua.
  5. Status siswa menjadi alumni, enrollment ditutup dengan status `lulus`.
  6. Data alumni dan tracer study (K3) dibuat.
- **Kini:** `AlumniController::bulkGraduate` sudah ada, tetapi belum terikat ke rapat atau pengesahan dan riwayat rombel.

#### A6. Mutasi Siswa Masuk/Keluar · `[Sebagian]`
- **Pemilik:** Staf TU. **Mengetahui:** Waka Kesiswaan dan Kepala Sekolah.
- **Langkah:**
  1. TU mencatat permohonan beserta dokumen.
  2. Kepala sekolah menyetujui.
  3. Untuk mutasi keluar: enrollment ditutup (`mutasi_keluar`), tagihan berjalan ditinjau, dan akun dinonaktifkan. Untuk mutasi masuk: siswa dibuat dan dimasukkan ke rombel.
- **Kini:** `student_mutations` mencatat in/out tanpa persetujuan dan tanpa mengubah riwayat rombel.

### B. Penerimaan Siswa Baru

#### B1. SPMB · `[Tersedia]`
- **Pemilik:** Waka Humas atau panitia SPMB. **Mengesahkan:** Kepala Sekolah.
- **Langkah:**
  1. Buka periode, jalur, dan gelombang.
  2. Pendaftar mengisi form dan mengunggah berkas.
  3. Panitia memverifikasi berkas.
  4. Seleksi.
  5. Pengumuman.
  6. Daftar ulang dan pembayaran uang pangkal.
  7. **Konversi jadi siswa** (`SpmbController::bulkConvert`). Data diri pendaftar langsung menjadi profil siswa, dan akun siswa dan orang tua dibuat (K5). Kelengkapan profil dilanjutkan TU atau lewat pengajuan perubahan (K4).
  8. Siswa masuk ke rombel X (A2).
- **Status pendaftar:** `pending → reviewing → accepted/…` (`registrations.status`).
- **Kanal:** Web publik (pendaftar), Android (`SpmbRegistration`/`SpmbTracking`), Web panitia.

### C. KBM Harian

#### C1. Jadwal Pelajaran · `[Tersedia]`
- **Pemilik:** Waka Kurikulum.
- **Langkah:**
  1. Atur preset jam pelajaran.
  2. Plot guru × mapel × rombel × jam.
  3. Cek bentrok.
  4. Terbitkan.

  Setelah terbit, jadwal muncul di Android siswa dan guru.

#### C2. Sesi Kelas & Presensi QR per Mapel · `[Tersedia]`
- **Pemilik:** Guru mapel.
- **Langkah:**
  1. Guru membuka sesi dari jadwal hari ini.
  2. QR berputar setiap 30 detik.
  3. Siswa memindai QR di HP-nya.
  4. Guru mengoreksi secara manual bila perlu.
  5. Sesi diakhiri atau tertutup otomatis, dan siswa yang belum tercatat dianggap alpha.
  6. Draf jurnal (C4) terbuat dari topik sesi.
  7. Ringkasan harian dikirim ke orang tua pukul 17.00.
- **Status sesi:** `scheduled → active → completed / auto_closed / cancelled`.
- **Status presensi:** `hadir · telat · sakit · izin · alpha`.
- **Koreksi:** admin atau waka, tercatat di audit log.

#### C3. Izin / Sakit Siswa dari Orang Tua · `[Terjadwal]`
- **Pemilik:** Wali Kelas.
- **Langkah:**
  1. Orang tua mengajukan izin atau sakit di Android: tanggal, alasan, dan foto surat dokter bila sakit lebih dari 2 hari.
  2. Wali kelas menyetujui atau menolak.
  3. Bila disetujui, presensi semua sesi pada tanggal itu otomatis menjadi `izin`/`sakit`, bukan alpha.
  4. Guru mapel melihat keterangan itu di daftar sesinya.
- **Status:** `diajukan → disetujui / ditolak` (dibatalkan oleh orang tua sebelum diputuskan).
- **Mengapa penting:** sekarang izin lewat WA pribadi ke wali kelas dan tidak tercatat, sehingga rekap presensi di rapor salah.

#### C4. Jurnal Mengajar · `[Tersedia]`
- **Pemilik:** Guru mapel. **Peninjau:** Waka Kurikulum.
- **Status:** `draft → submitted → reviewed`.

#### C5. Perangkat Ajar (Modul Ajar / ATP / Silabus) · `[Tersedia]`
- **Pemilik:** Guru mapel. **Menyetujui:** Waka Kurikulum.
- **Status:** `draft → submitted → approved / revision_needed` (`lesson_plans`, `syllabi`).

### D. Penilaian & Rapor

#### D1. Penilaian Harian & Remedial · `[Tersedia]`
- **Pemilik:** Guru mapel.
- **Langkah:**
  1. Guru membuat penilaian.
  2. Guru memasukkan nilai.
  3. Siswa di bawah KKTP otomatis masuk daftar remedial.
  4. Remedial dijalankan.
  5. Nilai remedial dicatat.
- **Status remedial:** `pending → in_progress → completed / waived`.

#### D2. CBT / Ujian · `[Tersedia]`
- **Pemilik:** Waka Kurikulum (jadwal ujian), Guru (soal).
- **Langkah:**
  1. Guru menyusun bank soal.
  2. Ujian dibuat (`draft → published`).
  3. Pengawas membuka token.
  4. Siswa mengerjakan di Android dengan anti-curang.
  5. Penilaian berjalan otomatis untuk soal objektif dan manual untuk esai.
  6. Ujian selesai (`completed`).
- **Status percobaan:** `in_progress → submitted → graded` (atau `blocked_violation`, `timeout`, `force_closed`).

#### D3. Tugas E-learning · `[Tersedia]`
- **Status:** `submitted → graded` (`locked_cheated` bila melanggar).

#### D4. Projek P5 (Kurikulum Merdeka) · `[Sebagian]`
- **Pemilik:** Koordinator P5 (di bawah Waka Kurikulum).
- **Langkah:**
  1. Tetapkan tema dan dimensi.
  2. Fasilitator menilai per dimensi.
  3. Nilai masuk ke rapor P5.
- **Kini:** tabel karakter/P5 ada. Alur ujung ke ujung sampai rapor perlu diverifikasi.

#### D5. Rapor Semester (e-Rapor resmi Kemendikdasmen) · `[Sebagian]`
- **Prinsip integrasi:** sekolah tetap memakai **aplikasi e-Rapor resmi**. SISTA **tidak** membuat rapor resmi kedua, dan **tidak ada input nilai dua kali**.
- **Pemilik:** Waka Kurikulum (penerbitan). Wali kelas memeriksa kelengkapan. Operator e-Rapor mengimpor. Pengesahan dan tanda tangan kepala sekolah terjadi di e-Rapor atau di dokumen fisik, bukan di SISTA.
- **Langkah:**
  1. Guru mengisi nilai harian, ujian, dan tugas di SISTA (D1–D3), lalu menyusun nilai akhir dan deskripsi capaian di SISTA.
  2. Wali kelas melihat **dasbor kelengkapan**: mapel mana yang belum lengkap, dengan pengingat dari kegiatan kunci "batas input nilai". Kehadiran (dari C2/C3), catatan wali, ekskul, dan P5 juga disiapkan.
  3. SISTA **mengekspor ke template impor e-Rapor**, per rombel dan per mapel, mengikuti format template yang diunduh dari aplikasi e-Rapor sekolah.
  4. Operator e-Rapor mengimpor. Rapor resmi dicetak dan disahkan dari e-Rapor.
  5. PDF rapor resmi **diunggah balik ke SISTA secara massal per rombel**. Setiap file dicocokkan ke siswa lewat NISN, dan file yang tidak cocok dilaporkan.
  6. Waka kurikulum **menerbitkan** rapor ke orang tua dan siswa di Android, massal per angkatan.
- **Status:** `nilai_disusun → nilai_lengkap → diekspor → rapor_resmi_diunggah → terbit`.
- **Format rapor sekolah**:
  - **Kepala rapor:** nama, NIS/NISN, nama sekolah, alamat, kelas (rombel), **fase** (E untuk X, F untuk XI–XII), **semester ke-n** (1–6, berlanjut lintas tingkat), dan tahun pelajaran.
  - **I. Nilai akademik**, berisi nilai bulat dan *capaian kompetensi*, dikelompokkan:
    - **Kelompok A (Umum)**, termasuk muatan lokal Bahasa Jawa. Di kelas X, IPA dan IPS ditulis sebagai judul kelompok dengan sub-mapel bernilai sendiri (a. Fisika, b. Kimia, …).
    - **Kelompok B (Mata Pelajaran Pilihan)**, hanya XI–XII. Contoh rombel XI-3: Matematika Lanjut, Prakarya & Kewirausahaan, Bahasa Inggris Lanjut, Fisika, Informatika. **Susunan yang sama berlanjut ke XII-3**.
    - **Kelompok C (Ciri Khusus).** Isinya berbeda per tingkat: di X Fiqih, Aqidah Akhlak, Bahasa Arab, Al-Qur'an Hadits, SKI; di XI–XII Bahasa Arab dan Pembiasaan Sikap Baik.
  - **II. Ekstrakurikuler:** nama dan keterangan.
  - **III. Ketidakhadiran:** Sakit, Izin, dan Tanpa Keterangan, **dihitung dalam hari**.
  - **IV. Catatan Wali Kelas.**
  - **Semester genap:** "Keterangan Kenaikan Kelas" (misalnya "NAIK ke kelas XI"), dari keputusan A4.
  - **Tanda tangan:** orang tua/wali, wali kelas, dan Kepala Sekolah ("Mengetahui"), dengan tempat dan tanggal.
- **Kalimat capaian kompetensi** berpola "Menunjukkan penguasaan yang sangat baik dalam *[TP terkuat]* dan perlu bimbingan dalam *[TP terlemah]*". Pada contoh rapor ada kalimat yang berbunyi **"sangat baik dalam FALSE"**, tanda proses sekarang memakai rumus Excel yang gagal. SISTA menyusun **draf kalimat ini otomatis dari nilai per Tujuan Pembelajaran (TP)**, lalu guru meninjau dan bisa mengubah. Bila data TP kosong, kalimat dibiarkan kosong untuk diisi guru, tidak pernah menulis "FALSE" atau kalimat karangan.
- **Aturan hitung ketidakhadiran rapor** (pengaturan bawaan sekolah):
  - Satu hari dihitung **Sakit/Izin** bila ada izin yang disetujui (C3) untuk hari itu.
  - Satu hari dihitung **Tanpa Keterangan** bila siswa tidak tercatat hadir atau telat di **semua** sesi hari itu dan tidak ada izin.
  - Tidak hadir di sebagian jam pelajaran tidak dihitung sebagai hari di rapor, tetapi terlihat oleh wali kelas dan BK.
- **Rapor tengah semester** (contoh keluaran e-Rapor, semester genap 2025/2026):
  - Rapor terbit **dua kali per semester**: tengah semester (PTS) dan akhir semester.
  - Rapor tengah semester lebih ringkas: kop yayasan dan sekolah, identitas, nilai + capaian per Kelompok, dan tanda tangan. Tidak ada ekskul, ketidakhadiran, atau catatan wali.
  - Pembagian kedua jenis rapor menjadi kegiatan kunci di kalender akademik (A0).
- **Nilai yang belum ada ditulis "-"** di e-Rapor (contoh: dua mapel pilihan di tengah semester). Ekspor SISTA mengirim **kosong**, bukan 0 (lihat `docs/CONTRIBUTING.md`).
- **Pelajaran dari kalimat capaian di e-Rapor:**
  - Bila TP terkuat sama dengan TP terlemah (misalnya baru satu TP dinilai), e-Rapor mengulang kalimat yang sama: "Sangat baik dalam X dan perlu bimbingan dalam X". Lebih parah lagi, "Perlu bimbingan dalam X dan perlu bimbingan dalam X".
  - Draf SISTA **tidak mengulang**: bila hanya ada satu TP atau TP-nya sama, kalimatnya cukup satu klausa.
  - Kata awal ("Sangat baik", "Baik", "Perlu bimbingan") mengikuti **ambang nilai yang diatur sekolah**.
  - Bila format impor e-Rapor ternyata meminta TP terkuat/terlemah, bukan kalimat jadi, SISTA mengekspor pilihan TP itu dan kalimatnya disusun e-Rapor.
- **Nama pejabat menurut tanggal.** Pada contoh rapor, Kepala Sekolah berganti **tiga kali dalam tiga tahun** (semester 1 dan semester 2 TA 2023/2024 pun berbeda). Nama Kepala Sekolah dan wali kelas yang dicetak atau diekspor diambil dari pemegang jabatan **pada tanggal rapor**, bukan pejabat sekarang.
- **Kini:** ada `ERaporPdfService` yang membuat PDF rapor sendiri. Ini **harus dialihkan** menjadi pratinjau nilai untuk wali kelas, atau dihapus, supaya tidak ada dua rapor berbeda di tangan orang tua.

### E. Kesiswaan, BK & Layanan Siswa

#### E1. Poin Pelanggaran & Surat Peringatan · `[Sebagian]`
- **Pemilik:** Waka Kesiswaan. **Pencatat:** guru dan BK.
- **Langkah:**
  1. Guru atau BK mencatat pelanggaran beserta kategori dan poin.
  2. Bila akumulasi poin melewati ambang, surat peringatan diterbitkan (`warning_letters`), sesi konseling wajib dibuat (E2), dan orang tua diberi tahu.
- **Perlu dicek:** ambang dan tahapan SP1/SP2/SP3 ditetapkan di pengaturan, bukan di kode.

#### E2. Konseling · `[Tersedia]`
- **Pemilik:** Guru BK.
- **Status:** `requested → scheduled → in_progress → completed / cancelled`. Catatan konseling dienkripsi.

#### E3. Laporan Perundungan (TPPK, Permendikbudristek 46/2023) · `[Tersedia]`
- **Pemilik:** Guru BK / TPPK. **Pelapor:** semua warga sekolah, bisa anonim.
- **Status:** `reported → under_review → investigating → action_taken → closed / dismissed`.

#### E4. Pendidikan Inklusi (ABK) · `[Tersedia]`
- **Pemilik:** Guru BK / koordinator inklusi.
- **Langkah:**
  1. Skrining.
  2. Program Pembelajaran Individual (IEP).
  3. Akomodasi per mapel atau ujian.
  4. Evaluasi berkala.
  5. Orang tua dilibatkan.

#### E5. UKS · `[Tersedia]`
- **Pemilik:** Petugas UKS.
- **Langkah:**
  1. Catat kunjungan, keluhan, dan tindakan.
  2. Stok obat berkurang otomatis.
  3. Bila keluhan berat atau dirujuk, orang tua langsung diberi tahu lewat WA.
  4. Riwayat kesehatan bisa dilihat siswa dan orang tua.

#### E6. Prestasi Siswa · `[Tersedia]`
- **Langkah:** siswa atau guru mengunggah prestasi, lalu diverifikasi kesiswaan.
- **Status:** `PENDING_REVIEW → VALIDATED / REJECTED`. Prestasi yang tervalidasi masuk rapor dan buku induk.

#### E7. Ekstrakurikuler · `[Tersedia]`
- **Pemilik:** Pembina ekskul.
- **Langkah:** pendaftaran → keanggotaan (`active/inactive`) → presensi kegiatan → nilai ekskul ke rapor.

#### E8. OSIS / MPK & Pemilihan Ketua · `[Tersedia]`
- **Status pemilihan:** `upcoming → active → closed → announced`.

### F. Keislaman (ciri khas sekolah)

#### F1. Tahfidz · `[Tersedia]`
- **Pemilik:** Guru tahfidz.
- **Langkah:** tetapkan target per siswa per TA → catat setoran (bisa massal) → pantau progres (dilihat siswa dan orang tua).

#### F2. Mutabaah Yaumiyah · `[Tersedia]`
- **Langkah:** siswa mengisi amalan harian di Android → wali kelas atau guru memantau → rekap ke orang tua.

#### F3. Tahsin · `[Tersedia]`
- **Langkah:** siswa merekam bacaan di Android (`pending`) → guru meninjau dan menilai (`reviewed`) → penilaian guru dicatat (`TahsinRecordController`).

#### F4. Jadwal Sholat (informasi) · `[Tersedia]`
- **Sumber:** server menghitung jadwal harian untuk koordinat sekolah dengan parameter Kemenag RI. Satu sumber untuk web, beranda Android, dan widget.
- Bila koordinat belum diatur, jadwal tampil "belum tersedia", tanpa jadwal contoh.

### G. Keuangan

#### G1. Tagihan & Pembayaran Siswa · `[Tersedia]`
- **Pemilik:** Staf TU / Bendahara.
- **Langkah:**
  1. Tetapkan jenis biaya per TA.
  2. **Generate tagihan massal** per rombel atau angkatan.
  3. Orang tua melihat tagihan di Android.
  4. Orang tua membayar lewat VA (Midtrans webhook) atau tunai di TU (dicatat manual).
  5. Kuitansi.
  6. Pengingat otomatis tanggal 1 setiap bulan.
- **Status tagihan:** `unpaid → partial → paid`.

#### G2. Beasiswa / Keringanan · `[Tersedia]`
- **Status:** `pending → shortlisted → approved / rejected → disbursed`. Beasiswa mengurangi tagihan G1.

#### G3. Kas Kecil · `[Tersedia]`
- **Langkah:** pengajuan → disetujui atau ditolak (`approval_status`) → realisasi masuk buku kas.

#### G4. Pembayaran Vendor · `[Tersedia]`
- **Status:** `scheduled → paid / overdue / cancelled`.

#### G5. RKAS / RAPBS · `[Tersedia]`
- **Pemilik:** TU / Bendahara. **Menyetujui:** Kepala Sekolah. Penyusun tidak bisa menyetujui RKAS buatannya sendiri.
- **Status:** `draft → submitted → approved / revised`. Realisasi dibaca dari buku besar.

#### G6. Infaq / Donasi · `[Tersedia]`
- **Status donasi:** `pending → verified / cancelled`. Potongan tabungan hanya dari anak di keluarga donatur sendiri.

#### G7. Rekonsiliasi Bank · `[Tersedia]`
- **Langkah:** impor rekening koran → cocokkan transaksi → tindak lanjut selisih.

### H. Kepegawaian

#### H1. Presensi Pegawai (mesin sidik jari) · `[Tersedia]`
- **Pemilik:** Staf TU.
- **Langkah:** unggah file ekspor mesin → petakan ID mesin ke pegawai → rekap bulanan. Impor idempoten: file yang sama tidak menggandakan data.

#### H2. Cuti / Izin Pegawai · `[Tersedia]` (memakai mesin persetujuan)
- **Langkah:** pegawai mengajukan → Waka Kurikulum → Kepala Sekolah (`leave_approval`). Cuti yang disetujui masuk ke rekap presensi.

#### H3. Penggajian · `[Tersedia]`
- **Pemilik:** Bendahara. **Menyetujui:** Kepala Sekolah.
- **Langkah:** rekap presensi H1 + cuti H2 → slip gaji dengan potongan sesuai tarif di pengaturan.
- **Status:** `draft → approved → paid`, per slip.

#### H4. Evaluasi Kinerja Guru · `[Tersedia]`
- **Langkah:** siklus evaluasi dibuka → siswa menilai guru yang mengajar di kelasnya, rekan dan pimpinan menilai (360) → hasil hanya untuk pimpinan dan guru yang dinilai.

### I. Sarana Prasarana

#### I1. Inventaris Aset · `[Tersedia]`
- **Status aset:** `available · borrowed · repairing · lost`. Stiker QR per aset.

#### I2. Peminjaman Aset · `[Tersedia]`
- **Pemilik:** Waka Sarpras.
- **Status:** `pending → approved → borrowed → returned` (atau `overdue`, `lost`, `rejected`).

#### I3. Laporan Kerusakan & Perawatan · `[Sebagian]` (ada 3 controller dobel)
- **Langkah:**
  1. Siapa pun memindai QR aset dan melaporkan kerusakan.
  2. Sarpras menangani.
  3. Selesai atau aset dihapus dari daftar.
- **Status:** `pending → investigating → repairing → resolved / scrapped`.

#### I4. Booking Ruang / Alat · `[Tersedia]`
- **Status:** `pending → approved / rejected / cancelled`.

### J. Administrasi & Komunikasi

#### J1. Surat Keluar · `[Sebagian]`
- **Pemilik:** Staf TU. **Menandatangani:** Kepala Sekolah.
- **Langkah:** pilih template → isi → persetujuan kepala sekolah → nomor surat otomatis → terbit (PDF dengan QR verifikasi).
- **Status:** `draft → issued / revoked`.
- **Lubang:** definisi `letter_approval` sudah ada di seeder tetapi **belum dipakai**, sehingga surat bisa terbit tanpa persetujuan kepala sekolah.

#### J2. Surat Masuk & Disposisi · `[Terjadwal]`
- **Pemilik:** Staf TU.
- **Langkah:**
  1. TU mencatat dan memindai surat masuk.
  2. Kepala sekolah memberi **disposisi** ke waka atau guru, dengan instruksi dan tenggat.
  3. Penerima menindaklanjuti dan melaporkan.
  4. Surat diarsipkan.
- **Status:** `diterima → didisposisikan → ditindaklanjuti → selesai`.

#### J3. Helpdesk Internal · `[Tersedia]`
- **Status:** `open → in_progress → waiting → resolved → closed`, dengan SLA per prioritas.

#### J4. Pengumuman & WA Broadcast · `[Tersedia]`
- **Langkah:** buat pengumuman dengan sasaran peran atau kelas → tampil di Android dan web → WA broadcast opsional ke orang tua (antrean setiap menit).

#### J5. Kalender Sekolah · `[Tersedia]`
- Satu kalender (`AcademicEvent::visibleTo`) dengan pengingat harian pukul 07.00. Isinya berasal dari Kalender Akademik (A0) ditambah acara biasa.

#### J6. Chat Orang Tua ↔ Wali Kelas · `[Tersedia]`

#### J7. Pertemuan Orang Tua–Guru · `[Tersedia]`
- **Status slot:** `available → booked → completed / cancelled / no_show`. Orang tua hanya memesan untuk anaknya sendiri.

#### J8. Notulen Rapat · `[Tersedia]`
- **Status:** `draft → finalized`. Rapat kenaikan kelas (A4) dan kelulusan (A5) merujuk ke notulen ini.

#### J9. Survei Sekolah · `[Tersedia]`
- **Status:** `draft → active → closed → archived`. Survei peminatan (A3) adalah alur khusus, **bukan** survei umum, karena hasilnya menjadi data alokasi.

### K. Data & Pelaporan

#### K1. Dapodik / EMIS: Impor Awal, Ekspor, dan Selisih · `[Sebagian]`
- **Prinsip:** **Dapodik tetap sumber resmi identitas** (siswa, PTK, rombel). e-Rapor juga menarik data dari Dapodik, jadi NISN, NIS, dan nama rombel di SISTA harus sama dengan Dapodik.
- **Impor awal (sekali saat go-live):** file ekspor Excel dari aplikasi Dapodik (peserta didik, PTK, rombel) diimpor ke SISTA. Siswa yang sudah bersekolah **tidak diketik ulang**. Baris yang gagal dicocokkan dilaporkan, tidak diabaikan diam-diam.
- **Selama berjalan:** Dapodik tidak punya API tulis. Setiap perubahan identitas yang disetujui di SISTA (K4) masuk ke daftar **"Perlu diperbarui di Dapodik"** untuk operator. Operator menandai selesai setelah menyalinnya.
- **Ekspor:** kolom yang kosong dilaporkan sebagai "data belum lengkap", tidak diisi karangan (sudah tersedia).

#### K4. Pengajuan Perubahan Data Diri · `[Terjadwal]`
- **Pemilik:** Staf TU (verifikasi). **Pengaju:** siswa, orang tua, guru, karyawan.
- **Langkah:**
  1. Pengguna membuka profilnya dan mengajukan perubahan: kolom, nilai baru, dan **bukti** (foto KK, akta, atau ijazah) untuk data identitas.
  2. TU memverifikasi, menyetujui atau menolak dengan alasan. Bisa massal, dengan tenggat dan eskalasi (1b).
  3. Bila disetujui, perubahan diterapkan, tercatat di audit log, dan masuk daftar "Perlu diperbarui di Dapodik" bila kolomnya ada di Dapodik.
- **Anti-macet:** data kontak (nomor HP, email) langsung tersimpan tanpa persetujuan, karena tidak mengubah identitas resmi. Hanya data identitas (nama, tanggal lahir, NIK, NISN, nama orang tua, alamat KK) yang butuh verifikasi.
- **Status:** `diajukan → disetujui / ditolak` (dibatalkan oleh pengaju sebelum diputuskan).
- **Kegunaan saat go-live:** setelah impor dari Dapodik, siswa dan orang tua diminta **memeriksa data dirinya sendiri** dan mengajukan koreksi. TU tidak perlu memeriksa ribuan data satu per satu.

#### K5. Akun Pengguna Massal & Reset Password · `[Terjadwal]`
- **Konteks:** sekolah **tidak punya Google Workspace for Education**, jadi login memakai akun SISTA sendiri.
- **Langkah:**
  1. Akun dibuat massal dari data impor (K1) dan dari konversi SPMB (B1).
  2. Siswa login dengan NIS atau NISN.
  3. Password awal dibagikan lewat wali kelas (lembar akun per rombel). Pengguna **wajib mengganti** password saat login pertama.
- **Reset password (anti-macet):**
  - Wali kelas bisa me-reset password siswa di kelasnya.
  - TU bisa me-reset semua akun.
  - Bila WA gateway aktif, pengguna bisa reset sendiri lewat OTP WA ke nomor yang terdaftar.
- **Orang tua:**
  - satu akun per orang tua/wali, tertaut ke semua anaknya;
  - username = nomor HP;
  - password awal lewat lembar akun anak;
  - OTP WA menyusul bila WA gateway aktif.

#### K2. Buku Induk Siswa · `[Terjadwal]`
- **Isi:** identitas, riwayat rombel per TA, mutasi, nilai rapor per semester, prestasi, kelulusan. Dicetak per siswa sesuai format dinas.

#### K3. Alumni & Tracer Study · `[Tersedia]`

---

### L. Sistem & Akses

#### L1. Katalog Jabatan & Hak Akses · `[Tersedia]`
- **Pemilik:** Admin. Hak akses sensitif hanya oleh Admin Utama (`superadmin`).
- **Langkah:**
  1. Admin membuat atau mengubah jabatan: nama, deskripsi, dan **centang hak akses** dari daftar tetap yang dikelompokkan per modul.
  2. Sebelum menyimpan, sistem menampilkan **dampaknya**, misalnya "3 orang akan mendapat akses Keuangan" atau "2 orang kehilangan akses BK".
  3. Simpan. Perubahan tercatat di audit log.
- **Pagar pengaman:**
  - **Hak akses sensitif** hanya bisa diberikan atau dicabut Admin Utama: keuangan dan gaji, catatan BK/perundungan, data kesehatan, serta pengelolaan akun admin.
  - **Kepala Sekolah** adalah jabatan sistem: tidak bisa dihapus, dan hak "pengesahan kepala sekolah" tidak bisa dicabut darinya.
  - Jabatan yang pernah dipegang hanya bisa **dinonaktifkan**, tidak dihapus, supaya riwayat persetujuan tetap terbaca.
  - Jabatan yang masih dipakai sebagai langkah persetujuan (`workflow_definitions`) tidak bisa dinonaktifkan sebelum langkah itu dialihkan.

#### L2. Penetapan Pejabat (siapa memegang jabatan apa) · `[Tersedia]`
- **Pemilik:** Admin / Kepala TU, berdasarkan SK Kepala Sekolah.
- **Langkah:**
  1. Pilih orang (Guru atau Karyawan; jabatan tertentu boleh dibatasi ke jenis akun tertentu).
  2. Pilih jabatan.
  3. Isi periode (tanggal mulai–selesai, biasanya satu TA) dan nomor SK (opsional).
  4. Simpan.
- **Plt / pengganti sementara:** jabatan yang sama untuk orang lain dengan periode pendek. Aksesnya hilang otomatis setelah lewat.
- **Pergantian TA:** daftar pejabat disalin sebagai draf untuk TA baru dan ditinjau, seperti rombel.
- **Aturan:** penyusun dan penyetuju dalam satu alur tetap harus orang berbeda, walau satu orang memegang dua jabatan.

## 5. Prioritas Penyempurnaan Alur

| # | Alur | Catatan Kebutuhan |
|---|---|---|
| 1 | L1–L2 Jenis akun & jabatan, serta A0–A6 kalender akademik, rombel, survei peminatan & plotting XI, kenaikan, kelulusan, mutasi | Fondasi seluruh data akademik tahunan dan riwayat rombel |
| 2 | C3 Izin/sakit siswa dari orang tua | Integrasi otomatis ke rekap presensi harian dan rapor |
| 3 | D5 Ekspor nilai ke e-Rapor & penerbitan PDF rapor resmi | Menghindari input nilai ganda antara SISTA dan e-Rapor |
| 4 | K1 Impor awal Dapodik, K4 pengajuan perubahan data, K5 akun massal | Otomasi onboarding data siswa dan orang tua |
| 5 | J1 Persetujuan surat keluar + J2 surat masuk & disposisi | Integrasi penuh dengan alur persetujuan kepala sekolah |
| 6 | Kotak masuk terpadu "Menunggu Persetujuan Saya" | Sentralisasi antrean persetujuan lintas modul |
| 7 | K2 Buku Induk Siswa | Pencetakan buku induk sesuai format dinas berdasarkan riwayat tahun ajaran |

## 6. Cara Memakai Dokumen Ini

- **Sebelum membangun atau mengubah fitur:** cari alurnya di sini. Bila belum ada, tulis dulu alurnya (pemilik, langkah, status, persetujuan) dan minta persetujuan pemilik produk.
- **Setelah PR selesai:** perbarui baris "Status" alur yang tersentuh.
- **Test mengikuti alur:** setiap perpindahan status punya test, termasuk peran yang **tidak** boleh memindahkannya.
