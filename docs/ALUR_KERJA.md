# Peta Alur Kerja Sekolah: SMA Islam Sultan Agung 1 Semarang

> **Untuk apa dokumen ini.** Satu sumber kebenaran tentang **siapa melakukan apa, kapan, dan disetujui siapa** di setiap fitur SISTA. Kode mengikuti dokumen ini, bukan sebaliknya. Bila sebuah fitur tidak punya alur di sini, fitur itu belum jelas dan tidak boleh dibangun dulu.
>
> **Status per 11 Oktober 2026**, setelah FASE 119 (debloat) dan FASE 78 (Android). Kolom "Status" dibaca dari tabel, route, dan service yang ada di kode. Pelaksana yang menyentuh sebuah alur wajib memverifikasinya ujung ke ujung dan memperbarui baris itu.
>
> Legenda: ✅ ada di kode · 🟡 sebagian (ada lubang di alurnya) · ❌ belum ada

---

## 1. Prinsip Alur

1. **Setiap alur punya satu pemilik peran.** Pemilik bertanggung jawab alur itu berjalan, walau banyak peran ikut serta.
2. **Setiap perubahan status tercatat:** siapa, kapan, dan catatannya (audit log). Tidak ada status yang berubah diam-diam.
3. **Persetujuan berjenjang memakai satu mesin:** `WorkflowService` (`workflow_definitions` → langkah per peran, bisa didelegasikan, revisi, ajukan ulang). Alur baru yang butuh persetujuan **wajib** memakainya, supaya pimpinan punya **satu kotak masuk "Menunggu Persetujuan Saya"**, bukan satu per modul.
4. **Penyusun tidak menyetujui buatannya sendiri.**
5. **Notifikasi mengikuti status, bukan tombol.** Setiap perpindahan status yang menunggu orang lain mengirim notifikasi (aplikasi/push, dan WA hanya untuk orang tua bila penting).
6. **Kanal mengikuti pemakai.** Pekerjaan meja (TU, waka, kepsek) ada di web. Pekerjaan di kelas dan di rumah (guru, siswa, orang tua) ada di Android, dan web tetap bisa sebagai cadangan.
7. **Satu aturan akses** (CLAUDE.md aturan 3): role di route menentukan siapa boleh membuka, dan data dibatasi ke miliknya (ortu → anaknya, guru → kelas yang diajar tahun ini).

## 2. Peran (Aktor)

| Peran | Kode di sistem | Kanal utama |
|---|---|---|
| Kepala Sekolah | `kepala_sekolah` | Web (+ Android untuk persetujuan) |
| Waka Kurikulum / Kesiswaan / Sarpras / Humas | `waka_*` | Web |
| Guru mapel | `guru` (`TEACHER_ROLES`) | Android di kelas, Web untuk perangkat ajar |
| Wali Kelas | guru dengan `classrooms.teacher_id` | Android + Web |
| Guru BK | `bk` | Web + Android |
| Staf TU / Bendahara | `staf_tu` | Web |
| Petugas UKS | (peran UKS di route `uks`) | Web + Android |
| Siswa | `STUDENT_ROLES` | Android |
| Orang Tua / Wali | `PARENT_ROLES` | Android |
| Admin sistem | `admin` (operator) / `superadmin` | Web |

## 3. Siklus Satu Tahun Ajaran

```
JUL  ── Tahun ajaran baru: aktifkan TA + semester Ganjil, rombel baru terbit, wali kelas ditetapkan,
        jadwal disusun, tagihan SPP setahun digenerate, MPLS siswa baru (hasil SPMB)
AGU–NOV ── KBM: sesi kelas + presensi QR, jurnal, penilaian harian, remedial, CBT/PTS
DES  ── PAS Ganjil → nilai rapor → pengesahan → rapor Ganjil terbit ke ortu
JAN  ── Semester Genap aktif (rombel TIDAK berubah)
FEB–MAR ── SPMB dibuka (gelombang), seleksi
APR  ── Survei peminatan kelas X (pemetaan minat mapel pilihan)
MEI  ── Statistik survei → Waka rancang rombel XI + plot siswa + jadwal XI; ujian akhir kelas XII
JUN  ── PAT → rapor Genap → RAPAT KENAIKAN KELAS → pengesahan → kelulusan XII → alumni
        SPMB: pengumuman, daftar ulang, konversi jadi siswa
JUL  ── Kenaikan dieksekusi → rombel TA baru → siklus berulang
```

Rombel hanya berubah sekali setahun (Juli). Semester Genap memakai rombel yang sama.

---

## 4. Alur per Domain

Format setiap alur: **Pemilik** · **Pemicu** · **Langkah** · **Status** · **Keluaran** · **Kanal** · **Status implementasi**.

### A. Siklus Akademik & Rombel

#### A1. Pembukaan Tahun Ajaran & Semester · 🟡 → FASE 120
- **Pemilik:** Waka Kurikulum (eksekusi teknis oleh admin).
- **Pemicu:** Juli (TA baru) dan Januari (semester Genap).
- **Langkah:**
  1. Buat TA baru (mis. 2027/2028) beserta dua semester.
  2. Salin struktur rombel dari TA lama sebagai draf.
  3. Tetapkan wali kelas.
  4. Aktifkan.

  Hanya satu TA dan satu semester yang aktif.
- **Status TA:** `draf → aktif → ditutup`.
- **Kini:** `academic_years` per semester dengan `is_active`. Rombel memakai teks `classrooms.academic_year`, belum terikat. Lihat FASE 120.

#### A2. Penyusunan Rombel & Wali Kelas · ❌ → FASE 120
- **Pemilik:** Waka Kurikulum. **Mengetahui:** Kepala Sekolah.
- **Langkah:**
  1. Rombel X diisi dari hasil SPMB.
  2. Rombel XI **dirancang dan diplot manual** oleh waka dari statistik survei peminatan (A3).
  3. Rombel XII dilanjutkan dari XI dengan susunan mapel yang sama.
  4. Wali kelas ditetapkan per rombel.
- **Keluaran:** riwayat `class_enrollments` (siswa × rombel × TA). Kolom `students.classroom_id` menjadi "rombel aktif" yang disinkronkan.

#### A3. Survei Peminatan, Rancangan Rombel XI & Plotting · ❌ → FASE 120
- **Pemilik:** Waka Kurikulum (dibantu guru kurikulum dan BK). **Mengetahui:** Kepala Sekolah.
- **Pemicu:** semester Genap kelas X (± April).
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

#### A4. Kenaikan Kelas · ❌ → FASE 120
- **Pemilik:** Waka Kurikulum, **dengan sepengetahuan Kepala Sekolah** (keputusan pemilik produk, 11 Okt 2026).
- **Pemicu:** setelah rapor Genap disahkan (Juni).
- **Langkah:**
  1. Sistem menyusun **draf keputusan** per siswa: naik atau tinggal kelas, dengan dasar dari rapor dan presensi.
  2. Wali kelas meninjau draf kelasnya dan memberi catatan.
  3. Hasil dibawa ke rapat dewan guru (notulen di J8).
  4. Waka kurikulum menetapkan. Kepala sekolah menerima notifikasi "mengetahui" dan **dapat menahan** keputusan sebelum dieksekusi.
  5. Eksekusi atomik pada pembukaan TA baru (A1). Siswa X yang naik masuk ke rombel hasil plotting A3. Eksekusi bisa dibatalkan sebelum TA baru diaktifkan.
- **Status batch:** `draf → ditinjau_wali → ditetapkan → dieksekusi` (atau `dibatalkan`).
- **Keluaran:** enrollment TA lama ditutup dengan status `naik`/`tinggal`, dan enrollment TA baru dibuat.

#### A5. Kelulusan Kelas XII → Alumni · 🟡 → FASE 120
- **Pemilik:** Waka Kurikulum. **Mengesahkan:** Kepala Sekolah (SK kelulusan).
- **Langkah:**
  1. Disusun draf kelulusan.
  2. Rapat dewan guru.
  3. Kepala sekolah mengesahkan.
  4. Pengumuman ke siswa dan orang tua.
  5. Status siswa menjadi alumni, enrollment ditutup dengan status `lulus`.
  6. Data alumni dan tracer study (K3) dibuat.
- **Kini:** `AlumniController::bulkGraduate` sudah ada, tetapi belum terikat ke rapat atau pengesahan dan riwayat rombel.

#### A6. Mutasi Siswa Masuk/Keluar · 🟡 → FASE 120
- **Pemilik:** Staf TU. **Mengetahui:** Waka Kesiswaan dan Kepala Sekolah.
- **Langkah:**
  1. TU mencatat permohonan beserta dokumen.
  2. Kepala sekolah menyetujui.
  3. Untuk mutasi keluar: enrollment ditutup (`mutasi_keluar`), tagihan berjalan ditinjau, dan akun dinonaktifkan. Untuk mutasi masuk: siswa dibuat dan dimasukkan ke rombel.
- **Kini:** `student_mutations` mencatat in/out tanpa persetujuan dan tanpa mengubah riwayat rombel.

### B. Penerimaan Siswa Baru

#### B1. SPMB · ✅
- **Pemilik:** Waka Humas atau panitia SPMB. **Mengesahkan:** Kepala Sekolah.
- **Langkah:**
  1. Buka periode, jalur, dan gelombang.
  2. Pendaftar mengisi form dan mengunggah berkas.
  3. Panitia memverifikasi berkas.
  4. Seleksi.
  5. Pengumuman.
  6. Daftar ulang dan pembayaran uang pangkal.
  7. **Konversi jadi siswa** (`SpmbController::bulkConvert`).
  8. Siswa masuk ke rombel X (A2).
- **Status pendaftar:** `pending → reviewing → accepted/…` (`registrations.status`).
- **Kanal:** Web publik (pendaftar), Android (`SpmbRegistration`/`SpmbTracking`), Web panitia.

### C. KBM Harian

#### C1. Jadwal Pelajaran · ✅
- **Pemilik:** Waka Kurikulum.
- **Langkah:**
  1. Atur preset jam pelajaran.
  2. Plot guru × mapel × rombel × jam.
  3. Cek bentrok.
  4. Terbitkan.

  Setelah terbit, jadwal muncul di Android siswa dan guru.

#### C2. Sesi Kelas & Presensi QR per Mapel · ✅ (FASE 117 / Android 77)
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

#### C3. Izin / Sakit Siswa dari Orang Tua · ❌ → FASE 121
- **Pemilik:** Wali Kelas.
- **Langkah:**
  1. Orang tua mengajukan izin atau sakit di Android: tanggal, alasan, dan foto surat dokter bila sakit lebih dari 2 hari.
  2. Wali kelas menyetujui atau menolak.
  3. Bila disetujui, presensi semua sesi pada tanggal itu otomatis menjadi `izin`/`sakit`, bukan alpha.
  4. Guru mapel melihat keterangan itu di daftar sesinya.
- **Status:** `diajukan → disetujui / ditolak` (dibatalkan oleh orang tua sebelum diputuskan).
- **Mengapa penting:** sekarang izin lewat WA pribadi ke wali kelas dan tidak tercatat, sehingga rekap presensi di rapor salah.

#### C4. Jurnal Mengajar · ✅
- **Pemilik:** Guru mapel. **Peninjau:** Waka Kurikulum.
- **Status:** `draft → submitted → reviewed`.

#### C5. Perangkat Ajar (Modul Ajar / ATP / Silabus) · ✅
- **Pemilik:** Guru mapel. **Menyetujui:** Waka Kurikulum.
- **Status:** `draft → submitted → approved / revision_needed` (`lesson_plans`, `syllabi`).

### D. Penilaian & Rapor

#### D1. Penilaian Harian & Remedial · ✅
- **Pemilik:** Guru mapel.
- **Langkah:**
  1. Guru membuat penilaian.
  2. Guru memasukkan nilai.
  3. Siswa di bawah KKTP otomatis masuk daftar remedial.
  4. Remedial dijalankan.
  5. Nilai remedial dicatat.
- **Status remedial:** `pending → in_progress → completed / waived`.

#### D2. CBT / Ujian · ✅
- **Pemilik:** Waka Kurikulum (jadwal ujian), Guru (soal).
- **Langkah:**
  1. Guru menyusun bank soal.
  2. Ujian dibuat (`draft → published`).
  3. Pengawas membuka token.
  4. Siswa mengerjakan di Android dengan anti-curang.
  5. Penilaian berjalan otomatis untuk soal objektif dan manual untuk esai.
  6. Ujian selesai (`completed`).
- **Status percobaan:** `in_progress → submitted → graded` (atau `blocked_violation`, `timeout`, `force_closed`).

#### D3. Tugas E-learning · ✅
- **Status:** `submitted → graded` (`locked_cheated` bila melanggar).

#### D4. Projek P5 (Kurikulum Merdeka) · 🟡
- **Pemilik:** Koordinator P5 (di bawah Waka Kurikulum).
- **Langkah:**
  1. Tetapkan tema dan dimensi.
  2. Fasilitator menilai per dimensi.
  3. Nilai masuk ke rapor P5.
- **Kini:** tabel karakter/P5 ada. Alur ujung ke ujung sampai rapor perlu diverifikasi.

#### D5. Rapor Semester · 🟡 → FASE 121
- **Pemilik:** Wali Kelas. **Mengesahkan:** Kepala Sekolah.
- **Langkah:**
  1. Guru mapel mengunci nilai akhir dan deskripsi capaian.
  2. Wali kelas melengkapi kehadiran (otomatis dari C2/C3), catatan, ekskul, dan P5.
  3. Wali kelas mengajukan pengesahan.
  4. Kepala sekolah mengesahkan (lewat mesin persetujuan, bisa per rombel sekaligus).
  5. Rapor **terbit**: orang tua dan siswa bisa membuka PDF di Android.
  6. Rapor diarsipkan ke Buku Induk (K2).
- **Status:** `draf → nilai_dikunci → diajukan → disahkan → terbit`. Revisi setelah terbit harus lewat pembukaan kunci oleh waka kurikulum dan tercatat.
- **Kini:** nilai, sinkron, catatan wali, dan cetak PDF sudah ada. Status pengesahan dan penerbitan belum ada.

### E. Kesiswaan, BK & Layanan Siswa

#### E1. Poin Pelanggaran & Surat Peringatan · ✅/🟡
- **Pemilik:** Waka Kesiswaan. **Pencatat:** guru dan BK.
- **Langkah:**
  1. Guru atau BK mencatat pelanggaran beserta kategori dan poin.
  2. Bila akumulasi poin melewati ambang, surat peringatan diterbitkan (`warning_letters`), sesi konseling wajib dibuat (E2), dan orang tua diberi tahu.
- **Perlu dicek:** ambang dan tahapan SP1/SP2/SP3 ditetapkan di pengaturan, bukan di kode.

#### E2. Konseling · ✅
- **Pemilik:** Guru BK.
- **Status:** `requested → scheduled → in_progress → completed / cancelled`. Catatan konseling dienkripsi.

#### E3. Laporan Perundungan (TPPK, Permendikbudristek 46/2023) · ✅
- **Pemilik:** Guru BK / TPPK. **Pelapor:** semua warga sekolah, bisa anonim.
- **Status:** `reported → under_review → investigating → action_taken → closed / dismissed`.

#### E4. Pendidikan Inklusi (ABK) · ✅
- **Pemilik:** Guru BK / koordinator inklusi.
- **Langkah:**
  1. Skrining.
  2. Program Pembelajaran Individual (IEP).
  3. Akomodasi per mapel atau ujian.
  4. Evaluasi berkala.
  5. Orang tua dilibatkan.

#### E5. UKS · ✅
- **Pemilik:** Petugas UKS.
- **Langkah:**
  1. Catat kunjungan, keluhan, dan tindakan.
  2. Stok obat berkurang otomatis.
  3. Bila keluhan berat atau dirujuk, orang tua langsung diberi tahu lewat WA.
  4. Riwayat kesehatan bisa dilihat siswa dan orang tua.

#### E6. Prestasi Siswa · ✅
- **Langkah:** siswa atau guru mengunggah prestasi, lalu diverifikasi kesiswaan.
- **Status:** `PENDING_REVIEW → VALIDATED / REJECTED`. Prestasi yang tervalidasi masuk rapor dan buku induk.

#### E7. Ekstrakurikuler · ✅
- **Pemilik:** Pembina ekskul.
- **Langkah:** pendaftaran → keanggotaan (`active/inactive`) → presensi kegiatan → nilai ekskul ke rapor.

#### E8. OSIS / MPK & Pemilihan Ketua · ✅
- **Status pemilihan:** `upcoming → active → closed → announced`.

### F. Keislaman (ciri khas sekolah)

#### F1. Tahfidz · ✅
- **Pemilik:** Guru tahfidz.
- **Langkah:** tetapkan target per siswa per TA → catat setoran (bisa massal) → pantau progres (dilihat siswa dan orang tua).

#### F2. Mutabaah Yaumiyah · ✅
- **Langkah:** siswa mengisi amalan harian di Android → wali kelas atau guru memantau → rekap ke orang tua.

#### F3. Tahsin · ✅
- **Langkah:** siswa merekam bacaan di Android (`pending`) → guru meninjau dan menilai (`reviewed`) → penilaian guru dicatat (`TahsinRecordController`).

#### F4. Jadwal Sholat (informasi) · ❌ → FASE 119.12 / Android 79.4
- **Sumber:** server menghitung jadwal harian untuk koordinat sekolah dengan parameter Kemenag RI. Satu sumber untuk web, beranda Android, dan widget.
- Bila koordinat belum diatur, jadwal tampil "belum tersedia", tanpa jadwal contoh.

### G. Keuangan

#### G1. Tagihan & Pembayaran Siswa · ✅
- **Pemilik:** Staf TU / Bendahara.
- **Langkah:**
  1. Tetapkan jenis biaya per TA.
  2. **Generate tagihan massal** per rombel atau angkatan.
  3. Orang tua melihat tagihan di Android.
  4. Orang tua membayar lewat VA (Midtrans webhook) atau tunai di TU (dicatat manual).
  5. Kuitansi.
  6. Pengingat otomatis tanggal 1 setiap bulan.
- **Status tagihan:** `unpaid → partial → paid`.

#### G2. Beasiswa / Keringanan · ✅
- **Status:** `pending → shortlisted → approved / rejected → disbursed`. Beasiswa mengurangi tagihan G1.

#### G3. Kas Kecil · ✅
- **Langkah:** pengajuan → disetujui atau ditolak (`approval_status`) → realisasi masuk buku kas.

#### G4. Pembayaran Vendor · ✅
- **Status:** `scheduled → paid / overdue / cancelled`.

#### G5. RKAS / RAPBS · ✅
- **Pemilik:** TU / Bendahara. **Menyetujui:** Kepala Sekolah. Penyusun tidak bisa menyetujui RKAS buatannya sendiri.
- **Status:** `draft → submitted → approved / revised`. Realisasi dibaca dari buku besar.

#### G6. Infaq / Donasi · ✅
- **Status donasi:** `pending → verified / cancelled`. Potongan tabungan hanya dari anak di keluarga donatur sendiri.

#### G7. Rekonsiliasi Bank · ✅
- **Langkah:** impor rekening koran → cocokkan transaksi → tindak lanjut selisih.

### H. Kepegawaian

#### H1. Presensi Pegawai (mesin sidik jari) · ✅
- **Pemilik:** Staf TU.
- **Langkah:** unggah file ekspor mesin → petakan ID mesin ke pegawai → rekap bulanan. Impor idempoten: file yang sama tidak menggandakan data.

#### H2. Cuti / Izin Pegawai · ✅ (memakai mesin persetujuan)
- **Langkah:** pegawai mengajukan → Waka Kurikulum → Kepala Sekolah (`leave_approval`). Cuti yang disetujui masuk ke rekap presensi.

#### H3. Penggajian · ✅
- **Pemilik:** Bendahara. **Menyetujui:** Kepala Sekolah.
- **Langkah:** rekap presensi H1 + cuti H2 → slip gaji dengan potongan sesuai tarif di pengaturan.
- **Status:** `draft → approved → paid`, per slip.

#### H4. Evaluasi Kinerja Guru · ✅
- **Langkah:** siklus evaluasi dibuka → siswa menilai guru yang mengajar di kelasnya, rekan dan pimpinan menilai (360) → hasil hanya untuk pimpinan dan guru yang dinilai.

### I. Sarana Prasarana

#### I1. Inventaris Aset · ✅
- **Status aset:** `available · borrowed · repairing · lost`. Stiker QR per aset.

#### I2. Peminjaman Aset · ✅
- **Pemilik:** Waka Sarpras.
- **Status:** `pending → approved → borrowed → returned` (atau `overdue`, `lost`, `rejected`).

#### I3. Laporan Kerusakan & Perawatan · 🟡 (ada 3 controller dobel → FASE 119.12)
- **Langkah:**
  1. Siapa pun memindai QR aset dan melaporkan kerusakan.
  2. Sarpras menangani.
  3. Selesai atau aset dihapus dari daftar.
- **Status:** `pending → investigating → repairing → resolved / scrapped`.

#### I4. Booking Ruang / Alat · ✅
- **Status:** `pending → approved / rejected / cancelled`.

### J. Administrasi & Komunikasi

#### J1. Surat Keluar · 🟡
- **Pemilik:** Staf TU. **Menandatangani:** Kepala Sekolah.
- **Langkah:** pilih template → isi → persetujuan kepala sekolah → nomor surat otomatis → terbit (PDF dengan QR verifikasi).
- **Status:** `draft → issued / revoked`.
- **Lubang:** definisi `letter_approval` sudah ada di seeder tetapi **belum dipakai**, sehingga surat bisa terbit tanpa persetujuan kepala sekolah. Lihat FASE 121.

#### J2. Surat Masuk & Disposisi · ❌ → FASE 121
- **Pemilik:** Staf TU.
- **Langkah:**
  1. TU mencatat dan memindai surat masuk.
  2. Kepala sekolah memberi **disposisi** ke waka atau guru, dengan instruksi dan tenggat.
  3. Penerima menindaklanjuti dan melaporkan.
  4. Surat diarsipkan.
- **Status:** `diterima → didisposisikan → ditindaklanjuti → selesai`.

#### J3. Helpdesk Internal · ✅
- **Status:** `open → in_progress → waiting → resolved → closed`, dengan SLA per prioritas.

#### J4. Pengumuman & WA Broadcast · ✅
- **Langkah:** buat pengumuman dengan sasaran peran atau kelas → tampil di Android dan web → WA broadcast opsional ke orang tua (antrean setiap menit).

#### J5. Kalender Sekolah · ✅
- Satu kalender (`AcademicEvent::visibleTo`) dengan pengingat harian pukul 07.00.

#### J6. Chat Orang Tua ↔ Wali Kelas · ✅

#### J7. Pertemuan Orang Tua–Guru · ✅
- **Status slot:** `available → booked → completed / cancelled / no_show`. Orang tua hanya memesan untuk anaknya sendiri.

#### J8. Notulen Rapat · ✅
- **Status:** `draft → finalized`. Rapat kenaikan kelas (A4) dan kelulusan (A5) merujuk ke notulen ini.

#### J9. Survei Sekolah · ✅
- **Status:** `draft → active → closed → archived`. Survei peminatan (A3) adalah alur khusus, **bukan** survei umum, karena hasilnya menjadi data alokasi.

### K. Data & Pelaporan

#### K1. Ekspor Dapodik / EMIS · ✅
- Kolom yang kosong dilaporkan sebagai "data belum lengkap", tidak diisi karangan.

#### K2. Buku Induk Siswa · ❌ → FASE 122
- **Isi:** identitas, riwayat rombel per TA (FASE 120), mutasi, nilai rapor per semester, prestasi, kelulusan. Dicetak per siswa sesuai format dinas.

#### K3. Alumni & Tracer Study · ✅

---

## 5. Lubang Alur yang Harus Ditutup (urut prioritas)

| # | Alur | Kenapa mendesak | Fase |
|---|---|---|---|
| 1 | A1–A6 Rombel, survei peminatan & plotting XI, kenaikan, kelulusan, mutasi | Fondasi semua data per tahun. Wajib ada sebelum Juli 2027 | **120** |
| 2 | C3 Izin/sakit siswa dari orang tua | Dipakai setiap hari, dan rekap presensi rapor salah tanpanya | 121 |
| 3 | D5 Pengesahan & penerbitan rapor | Rapor Genap Juni 2027 harus resmi | 121 |
| 4 | J1 Persetujuan surat keluar + J2 surat masuk & disposisi | Alur inti TU dan kepala sekolah | 121 |
| 5 | Satu kotak masuk "Menunggu Persetujuan Saya" untuk semua alur berjenjang | Kepala sekolah tidak perlu membuka 8 menu | 121 |
| 6 | K2 Buku Induk | Butuh riwayat dari FASE 120 | 122 |
| 7 | I3 satukan 3 controller perawatan aset | Menghapus kebingungan menu | 119.12 |

## 6. Cara Memakai Dokumen Ini

- **Sebelum membangun atau mengubah fitur:** cari alurnya di sini. Bila belum ada, tulis dulu alurnya (pemilik, langkah, status, persetujuan) dan minta persetujuan pemilik produk.
- **Setelah PR selesai:** perbarui baris "Status" alur yang tersentuh.
- **Test mengikuti alur:** setiap perpindahan status punya test, termasuk peran yang **tidak** boleh memindahkannya.
