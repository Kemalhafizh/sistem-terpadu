# Rencana Implementasi SISTA: SMA Islam Sultan Agung 1 Semarang

> **Baca bagian "Status Saat Ini" dulu.** Sejarah FASE 1–117 ada di [`docs/archive/implementation_plan_fase_1-117.md`](docs/archive/implementation_plan_fase_1-117.md). Arsip itu hanya untuk referensi; banyak modulnya sudah dihapus.
> **Alur kerja per fitur** (siapa, kapan, disetujui siapa) ada di [`docs/ALUR_KERJA.md`](docs/ALUR_KERJA.md). Kode mengikuti alur itu.

---

## 📍 STATUS SAAT INI (diperbarui 11 Oktober 2026)

### Produk dalam satu kalimat
ERP untuk **satu SMA Islam di Indonesia**. **Web** dipakai untuk pekerjaan meja (TU, waka, kepala sekolah). **Android** dipakai di kelas dan di rumah (guru, siswa, orang tua). Sistem **belum live** dan belum ada data asli.

### Angka setelah debloat (FASE 119)
| Controller | Route | Tabel | Model | View | Layar Android |
|---:|---:|---:|---:|---:|---:|
| 165 | ±1.000 | 225 | 212 | 536 | 75 |

### Modul aktif
Daftar lengkap beserta alurnya ada di `docs/ALUR_KERJA.md`:
- **Akademik:** jadwal, sesi kelas + presensi QR per mapel, jurnal, perangkat ajar, penilaian, remedial, CBT, e-learning, P5, rapor.
- **Kesiswaan:** poin & surat peringatan, BK, laporan perundungan (TPPK), inklusi, UKS, prestasi, ekskul, OSIS/MPK.
- **Keislaman:** Tahfidz, Mutabaah, Tahsin.
- **Keuangan:** tagihan & pembayaran VA, beasiswa, kas kecil, vendor, RKAS, infaq, rekonsiliasi.
- **Kepegawaian:** impor presensi sidik jari, cuti (mesin persetujuan), penggajian, evaluasi guru.
- **Sarpras:** aset, peminjaman, kerusakan/perawatan, booking ruang.
- **Administrasi:** SPMB, surat keluar, helpdesk, pengumuman + WA, kalender, chat orang tua–wali kelas, pertemuan orang tua–guru, notulen, survei.
- **Data:** ekspor Dapodik/EMIS, alumni & tracer study.

### Modul yang sudah DIHAPUS (jangan dihidupkan lagi)
Blockchain, IoT, digital twin, tools developer & monitoring di dalam aplikasi, HR/manajemen korporat (succession, SWOT, process mining), marketing & media (drip, NPS, podcast), gamifikasi, AI (tutor, esai, copilot, chatbot, prediksi), green school, kantin, mini banking, magang, SCORM, virtual classroom, forum, to-do guru, portofolio, EDS/akreditasi/compliance, tryout UTBK + rekomendasi PTN, presensi GPS / QR gerbang / wajah. Rinciannya ada di FASE 119.1 dan 119.11. Test arsitektur menolak prefix route-nya.

### Keputusan pemilik produk yang berlaku
| Tanggal | Keputusan |
|---|---|
| 5 Okt 2026 | Belum ada data asli, jadi modul yang dipotong dihapus permanen |
| 5 Okt 2026 | Presensi siswa **hanya QR per mapel**. Pegawai memakai **mesin sidik jari** (diimpor) |
| 5 Okt 2026 | Inklusi dipertahankan. Penggajian diurus sekolah. Tidak ada Adiwiyata/kantin. Gamifikasi dihapus |
| 11 Okt 2026 | **Full Kurikulum Merdeka.** Tidak ada jurusan IPA/IPS. **Survei peminatan** di kelas X hanya memetakan minat. Waka Kurikulum merancang susunan mapel pilihan tiap rombel XI dan **memplot siswa secara manual** berdasarkan statistiknya. Sistem tidak mengalokasikan otomatis |
| 11 Okt 2026 | **Peran dua lapis:** 4 jenis akun statis (Siswa, Orang Tua, Guru, Karyawan) + **jabatan dinamis yang katalognya diatur Admin** di aplikasi. Jabatan bawaan: Kepala Sekolah, 4 Waka (Kurikulum, Kesiswaan, Sarpras, Humas), Kepala Perpustakaan, Kepala TU. Wali kelas, pembina, guru mapel, dan pengurus OSIS dibaca dari data modul, bukan jabatan |
| 11 Okt 2026 | **Kenaikan kelas ditetapkan Waka Kurikulum** dengan sepengetahuan Kepala Sekolah |
| 12 Okt 2026 | **e-Rapor resmi Kemendikdasmen tetap dipakai.** SISTA tidak membuat rapor resmi kedua dan tidak boleh ada input nilai dua kali. SISTA mengekspor nilai ke template impor e-Rapor, lalu PDF rapor resmi diunggah balik dan diterbitkan ke orang tua |
| 12 Okt 2026 | **Dapodik sumber resmi identitas.** Impor awal dari file ekspor Dapodik, sehingga siswa yang sudah bersekolah tidak diketik ulang. Siswa baru dari konversi SPMB. Perubahan data lewat **pengajuan perubahan** yang diverifikasi TU, lalu masuk daftar "perlu diperbarui di Dapodik" |
| 12 Okt 2026 | **SISTA dijual ke sekolah sebagai jasa pembuatan, lalu dikelola IT sekolah.** Pemilik produk tidak merawatnya setelah serah terima. Akibatnya: semua akun pihak ketiga, server, domain, dan kunci atas nama sekolah; dokumentasi operasional untuk IT sekolah; dan serah terima resmi (BAST) masuk FASE 123 |
| 12 Okt 2026 | **Tidak ada Google Workspace for Education.** Login memakai akun SISTA. Akun dibuat massal, password awal dibagikan wali kelas, wajib diganti saat login pertama |
| 11 Okt 2026 | **Anti-macet disetujui penuh:** ketujuh aturan di `ALUR_KERJA.md` 1b, dan tabel pelonggaran beban Kepala Sekolah menjadi **pengaturan bawaan**. Sekolah tetap bisa mengubah langkah dan batasnya di pengaturan |
| 11 Okt 2026 | **Tanggal kegiatan tidak tetap.** Setiap tahun sekolah menyusun Kalender Akademik sendiri. Sistem tidak boleh menanam bulan atau tanggal kegiatan; semua alur membaca tanggalnya dari kegiatan kunci di kalender akademik TA itu |
| 11 Okt 2026 | Widget jadwal sholat Android **dipertahankan** |

### Cara mengambil keputusan (12 Okt 2026)
Pemilik produk **mendelegasikan keputusan detail ke PM** (sesi perencana). Pemilik produk hanya menjawab pertanyaan yang memang butuh dia: fakta sekolah, akses ke orang atau dokumen, dan restu.
- Keputusan PM ditulis sebagai **bawaan PM**. Sifatnya aman, bisa dibalik, dan sebisa mungkin berupa pengaturan yang bisa diubah sekolah tanpa developer.
- Agent **tidak** mengambil keputusan produk sendiri. Bila ragu, tanyakan, dan pertanyaannya diteruskan PM ke pemilik produk dalam bentuk singkat.

### Keputusan bawaan PM (12 Okt 2026, bisa diubah sekolah)
| # | Topik | Bawaan |
|---|---|---|
| T2 | Rapor pelengkap di luar e-Rapor | **Tidak ada rapor kedua.** `ERaporPdfService` dialihkan menjadi pratinjau nilai untuk wali kelas. Perkembangan Tahfidz/Tahsin/Mutabaah tampil sebagai ringkasan di aplikasi, bukan sebagai rapor |
| T3 | Login orang tua | **Satu akun per orang tua/wali** (ayah, ibu, atau wali bisa masing-masing punya akun), semua tertaut ke anak yang sama. Username = nomor HP. Password awal lewat lembar akun anak. OTP WA menyusul bila T5 aktif |
| T4 | Pembayaran | Tetap **Midtrans VA** yang sudah ada. Siapa yang menanggung biaya transaksi diatur di pengaturan (bawaan: ditampilkan ke orang tua sebelum bayar). Pembayaran tunai di TU tetap tercatat manual. Bisa diganti VA bank mitra kemudian tanpa mengubah alur G1 |
| T5 | WhatsApp | Aplikasi **berjalan penuh tanpa WA**: notifikasi lewat push Android dan web. WA (Fonnte) adalah tambahan yang aktif bila sekolah mengisi nomor dan kuncinya di pengaturan |

### Pertanyaan untuk pemilik produk (hanya ini)
| # | Pertanyaan | Kapan dibutuhkan |
|---|---|---|
| T1 | Contoh **file template impor e-Rapor** dari operator e-Rapor sekolah | Sebelum FASE 121 D5 |
| T6 | ~~Siapa yang merawat SISTA setelah live~~ → **IT sekolah**, setelah serah terima (12 Okt 2026) | — |
| T8 | Kemampuan IT sekolah: hanya **operasional** (server, backup, akun, pengaturan) atau juga bisa **memperbaiki kode** (Laravel/Kotlin)? | Kedalaman dokumen serah terima FASE 123 |
| T9 | Bentuk penjualan: **jual putus** (kode sumber + hak penuh ke sekolah) atau **dengan masa garansi/kontrak perawatan**? | Isi BAST dan pasal garansi |
| T7 | **Restu** Kepala Sekolah/yayasan dan siapa **PIC** di sekolah | Sebelum pilot |

### Aturan main untuk semua agent
1. **CLAUDE.md** berlaku penuh: tidak ada angka karangan, aksi harus tersimpan, satu aturan akses, setiap PR punya test.
2. **Alur dulu, kode kemudian.** Fitur yang alurnya belum ada di `docs/ALUR_KERJA.md` tidak dibangun. Setelah PR selesai, perbarui baris "Status" alurnya.
3. **Persetujuan berjenjang memakai `WorkflowService`**, agar ada satu kotak masuk persetujuan untuk pimpinan.
3b. **Anti-macet** (`docs/ALUR_KERJA.md` bagian 1b): tidak ada pekerjaan yang tertahan di satu orang. Aturannya:
   - "mengetahui" tidak menahan alur;
   - setiap langkah menunggu punya tenggat, pengingat, dan eskalasi;
   - setiap penyetuju punya cadangan;
   - hal berulang disetujui massal;
   - batas nominal dan jenis diatur sekolah.
4. **Tidak ada fitur baru di luar roadmap sampai go-live.** Fitur berikutnya ditentukan dari keluhan pengguna asli setelah pilot.
5. **PR kecil, satu kelompok per PR.** Cantumkan angka sebelum/sesudah bila menghapus.
6. **Perubahan API yang dibaca Android** wajib punya PR pasangan di `sista-android`, dan kedua PR saling menautkan.

### Roadmap
| Fase | Isi | Status | Target |
|---|---|---|---|
| 118 | Tutup akses bocor | ✅ Selesai | — |
| 119 | Debloat ke ERP sekolah | ✅ Selesai (sisa di 119.12) | — |
| 119.12 | Sisa bersih-bersih | ⏳ Berikutnya | 1–2 hari |
| **120** | **Jenis akun & jabatan, kalender akademik, rombel, tahun ajaran, survei peminatan & plotting rombel XI, kenaikan & kelulusan** | ⏳ Berikutnya | Nov 2026 |
| 121 | **Anti-macet** (tenggat, eskalasi, persetujuan massal, langkah & batas yang diatur sekolah, dasbor alur tertahan) + alur harian yang hilang: izin siswa dari orang tua, pengesahan & penerbitan rapor, surat keluar & masuk, satu kotak masuk persetujuan | 📝 Garis besar | Des 2026 |
| 122 | Buku Induk Siswa | 📝 Garis besar | Jan 2027 |
| 123 | Go-live & **serah terima ke IT sekolah**: server atas nama sekolah, deploy, backup, impor data, akun massal, pilot, dokumentasi operasional, pelatihan, BAST (menyerap FASE 116) | 📝 Garis besar | Pilot Jan 2027, penuh **Juli 2027** (TA 2027/2028) |

Pasangan Android: FASE 79 (siap rilis) berjalan paralel dengan 119.12/120, lalu FASE 80 (sisi Android FASE 120). Lihat `sista-android/android_implementation.md`.

**Mengapa go-live penuh di Juli 2027:** pergantian tahun ajaran adalah titik potong paling bersih. Rombel, wali kelas, dan tagihan semuanya dibuat baru, sehingga tidak ada data setengah tahun yang harus dipindahkan dari cara lama. Pilot semester Genap (Jan–Jun 2027) dengan 1–2 rombel, TU, dan BK menguji alur sebelum itu, termasuk satu putaran **rapor Genap dan kenaikan kelas** yang nyata.

---

## FASE 118: PERBAIKAN BROKEN ACCESS CONTROL PADA MODUL FASE 45–62 `[SELESAI]`

> **Tanggal Rancangan:** 4 Oktober 2026
> **Pelaksana:** Agent Backend (repo `sistem-terpadu`). Tidak ada perubahan di `sista-android`, karena semua route yang bocor adalah route **web**, bukan `/api/v1`.
> **Prioritas:** P0 (Kritis). Kerjakan sebelum fitur baru apa pun.
> **Prerequisite:** `RoleMiddleware` (hanya superadmin yang tembus semua), `RouteAccess`/`@canVisit`, dan `App\Support\Access\StudentDataAccess` sudah ada (commit `84611b7`).

### 118.0: Temuan Audit

Ada 17 file route yang di-`require` dari `routes/web.php` dan hanya dijaga `auth` (atau `auth` + `verified`), tanpa `role:` ataupun `can:`. Akibatnya **setiap akun yang login, termasuk siswa dan orang tua, bisa membuka sekaligus mengubah data** di modul-modul ini. Controller di belakangnya juga tidak memanggil `authorize()`/`Gate` (14 dari 16 folder controller tidak punya pengecekan sama sekali).

| File route | Route | Route tulis | Guard peran | Sensitivitas |
|---|---:|---:|:---:|---|
| `treasury.php` | 22 | 11 | ❌ | **Sangat tinggi**: rekening bank, kas kecil, rekonsiliasi, pembayaran vendor |
| `crisis.php` | 21 | 7 | ❌ | **Sangat tinggi**: laporan bullying (identitas korban), deklarasi krisis, broadcast darurat |
| `recruitment.php` | 5 | 0 | ❌ | **Tinggi**: kompensasi & kesejahteraan pegawai |
| `inclusive_education.php` | 24 | 9 | ❌ | **Tinggi**: data siswa berkebutuhan khusus |
| `family_engagement.php` | 17 | 7 | ❌ | Tinggi: data keluarga |
| `campus_ops.php` | 19 | 8 | ❌ | Sedang: tamu & operasional |
| `data_engineering.php` | 13 | 4 | ❌ | Sedang: laporan & kualitas data seluruh sekolah |
| `school_safety.php` | 4 | 0 | ❌ | Sedang: CCTV/keamanan fisik |
| `extended_diamond.php` | 48 | 22 | ❌ | Campuran, perlu dipilah per prefix |
| `knowledge.php` | 12 | 11 | ❌ | Sedang: ruang kolaborasi guru |
| `adaptive.php`, `microlearning.php`, `career_readiness.php` | 47 | 11 | ❌ | Rendah–sedang: sebagian memang untuk siswa |
| `alumni_relations.php`, `omnichannel.php`, `islamic_tech.php`, `green_school.php` | 38 | 2 | ❌ | Rendah–sedang |

Masalah turunan yang ikut ditemukan:
- **Menu ikut bocor.** `RouteAccess::allows()` mengembalikan `true` untuk route tanpa aturan, sehingga `@canVisit('recruitment.kanban')` dan `@canVisit('data_engineering.streams')` di `resources/views/components/sidebar.blade.php` **menampilkan menu itu ke siswa dan orang tua.**
- **Sukses palsu (melanggar CLAUDE.md aturan 2).** `Green/WasteManagementController::storeLog()` menjawab "Log sampah tersimpan." tanpa menyimpan apa pun, dan `updateLog()` kosong.
- **Status dokumen tidak akurat.** FASE 45–62 di "Status Tracker" ditandai `Selesai ✅`, padahal modul-modulnya terbuka untuk semua peran dan sebagian masih berupa stub.

### 118.1: Pasang `role:` di Setiap Group Route

Pakai `RoleMiddleware` yang sudah ada. Untuk peran berejaan ganda, rujuk konstanta di `StudentDataAccess`, jangan literal. Pisahkan aturan **lihat** dari **ubah** bila memang berbeda.

Pemetaan awal (usulan PM). Keputusan bisnis finalnya ada di pemilik produk, dan setiap penyimpangan dicatat beserta alasannya di 118.6:

| Modul | Lihat | Ubah |
|---|---|---|
| Treasury (semua) | `staf_tu`, `kepala_sekolah`, `admin` | `staf_tu`, `admin` |
| Crisis: command center, declare, protocols, broadcast | `kepala_sekolah`, `waka_kesiswaan`, `waka_sarpras`, `admin` | sama |
| Crisis: headcount insiden | guru (`TEACHER_ROLES`) + pimpinan | guru + pimpinan |
| Anti-bullying: **form lapor** (`bullying/report` GET/POST) | semua user login, **tetap terbuka** | semua user login |
| Anti-bullying: daftar, detail, statistik | `bk`, `waka_kesiswaan`, `kepala_sekolah`, `admin` | sama |
| Recruitment / compensation / wellness | samakan dengan group `hrd.` di `web.php:1205` (`admin`, `superadmin`, `kepala_sekolah`). **Tidak ada peran `hrd`** di seeder, jadi jangan membuatnya diam-diam | sama |
| Inclusive education | `bk`, `guru`, `waka_kesiswaan`, `waka_kurikulum`, `kepala_sekolah`, `admin` + scoping per kelas/siswa | `bk`, `waka_kesiswaan`, `admin` |
| Data engineering | `admin`, `kepala_sekolah` | `admin` |
| School safety / campus ops | `staf_tu`, `waka_sarpras`, `kepala_sekolah`, `admin` | `staf_tu`, `waka_sarpras`, `admin` |
| Knowledge hub | guru + pimpinan | guru + pimpinan |
| Family engagement | dipilah per route: bagian ortu pakai `PARENT_ROLES` + scoping anak sendiri, bagian manajemen untuk staf | — |
| Adaptive, microlearning, career readiness | dipilah per route: bagian belajar untuk siswa (`STUDENT_ROLES`, hanya data dirinya sendiri), bagian manajemen/analitik untuk guru/pimpinan | — |
| `extended_diamond.php` | pilah per prefix (`mobile.`, `interactive.`, `dashboards.`, `publishing.`, …). Dasbor eksekutif hanya untuk pimpinan | — |
| Alumni, omnichannel, islamic tech, green school | tentukan per route, defaultnya staf/pimpinan | staf/pimpinan |

### 118.2: Kepemilikan Data (Anti-IDOR)

Route dengan `{id}` atau model binding (mis. `crisis/incident/{id}`, `crisis/bullying/{id}`, `treasury/reconciliation/{id}/review`, resource `accounts`) wajib punya pengecekan kepemilikan atau cakupan lewat Policy + `can:`/`authorize()`, mengikuti CLAUDE.md aturan 3: siswa hanya dirinya, ortu hanya anaknya, guru hanya kelas yang diajar tahun ajaran ini.

### 118.3: Hapus Sukses Palsu

- Untuk `Green/WasteManagementController::storeLog()` dan `updateLog()`, pilih salah satu: implementasikan simpan sungguhan (dengan validasi + test), **atau** cabut route-nya dan sembunyikan menunya. Dilarang mengembalikan pesan sukses tanpa menyimpan.
- Sapu semua controller modul FASE 45–62 dengan pola `// Implementation`, method kosong, atau redirect sukses tanpa operasi DB. Daftarkan temuannya di 118.6.

### 118.4: `RouteAccess` Gagal Tertutup untuk Menu Internal

Setelah 118.1 selesai, nyalakan *deny-by-default* untuk route web ber-`auth` tanpa aturan, **kecuali whitelist eksplisit** (dashboard, profil, notifikasi, logout, dll.). Dengan begitu menu yang lupa diberi peran tidak lagi muncul untuk semua orang. Whitelist disimpan di satu tempat dan dipakai juga oleh test 118.5.

### 118.5: Test Regresi (Wajib, CLAUDE.md aturan 4)

1. **Test arsitektur.** Iterasi semua route web yang memakai middleware `auth`. Setiap route WAJIB punya `role:` atau `can:`, kecuali yang ada di whitelist. Test gagal bila ada route baru tanpa guard.
2. **Test per modul.** Akun `siswa` dan `orang_tua`:
   - mendapat **403** di GET index dan POST store pada treasury, crisis (daftar/detail bullying), recruitment, dan data engineering;
   - mendapat **200** di form lapor bullying.

   Peran yang berhak mendapat 200.
3. **Test menu.** Render sidebar sebagai siswa dan pastikan tautan `recruitment.kanban`, `data_engineering.streams`, dan `treasury.*` tidak muncul.
4. **Test sukses palsu.** POST `green-school/waste/store` harus benar-benar menambah baris, atau route-nya sudah tidak ada.

### 118.6: Kriteria Selesai

- [x] Tidak ada route web ber-`auth` tanpa `role:`/`can:` selain whitelist yang ditegakkan test 118.5.1.
- [x] Siswa & orang tua mendapat 403 di semua modul internal di tabel 118.0.
- [x] Sidebar siswa/ortu tidak lagi menampilkan menu internal (test 118.5.3 merender `/dashboard` sebagai siswa/ortu; cek manual di browser belum dilakukan).
- [x] Tidak ada lagi controller modul FASE 45–62 yang menjawab sukses tanpa menyimpan (daftar di 118.7).
- [x] `vendor/bin/pint`, `php artisan test`, dan `vendor/bin/phpstan analyse` hijau (lokal: 1940 lolos, 1 gagal yang memang khusus produksi, `Fase85DebloatPragmatismTest > single vps`).
- [x] Bagian ini diperbarui menjadi `[SELESAI]` dan dilengkapi tabel peran final per modul, termasuk alasan bila menyimpang dari usulan 118.1.
- [x] Status FASE 45–62 di "Status Tracker" dikoreksi bila ada modul yang ternyata masih stub.

### 118.7: Hasil Pelaksanaan

> **Tanggal Selesai:** 5 Oktober 2026. Tidak ada perubahan di `sista-android`: route `mobile/*` yang disentuh di sini adalah halaman **web** `extended_diamond.php`, bukan `/api/v1`.

**Cara kerja akhirnya**
- Peran ditulis lewat `App\Support\Access\Roles::only(...)`, yang menerima konstanta `StudentDataAccess` (`STUDENT_ROLES`, `PARENT_ROLES`, `TEACHER_ROLES`, `STAFF_ROLES`) plus kelompok `Roles::PRINCIPAL` (`kepala_sekolah`, `kepsek`), `Roles::LEADERSHIP` (kepala sekolah + semua waka), dan `Roles::MEMBERS` (semua warga sekolah: siswa, ortu, guru, staf, pimpinan, admin; tanpa `merchant`/`alumni`). Tidak ada literal `'parent'`/`'siswa'` baru di route.
- Lihat dan ubah dipisah dengan middleware kedua pada route tulis (`$treasuryChange`, `$inclusiveChange`, `$campusChange`, `$greenChange`, `$dataChange`, …). Keduanya harus lolos.
- Hanya `superadmin` yang tembus. `admin` lolos hanya bila tercantum (atau bila route mencantumkan pimpinan, sesuai `RoleMiddleware`).
- **Whitelist satu tempat:** `config/route_access.php`.
  - `open_to_all` (33 route) memuat akun sendiri, kotak masuk sendiri, pengumuman yang dibatasi `Announcement::canBeSeenBy`, dan form lapor bullying.
  - `unguarded_debt` (396 route) memuat route lama di luar FASE 45–62 yang sudah tanpa peran sebelum FASE 118, misalnya `financial/*`, `cbt/*`, `curriculum/*`, `tahfidz/*`, `hc/*`. Sesuai keputusan pemilik produk, menunya tetap tampil.
  - Daftar utang hanya boleh menyusut: test menolak entri yang sudah berperan atau sudah hilang.
- **`RouteAccess` fail-closed:** route web ber-`auth` tanpa `role:`/`can:` yang tidak terdaftar di `config/route_access.php` tertutup untuk semua kecuali superadmin. Dengan begitu menu yang lupa diberi peran tidak lagi muncul untuk semua orang.

#### Tabel peran final

| Modul / route | Lihat | Ubah |
|---|---|---|
| Treasury: rekening, rekonsiliasi, posisi kas, kas kecil, vendor | `staf_tu`, kepala sekolah, `admin` | `staf_tu`, `admin` |
| Treasury: setujui/tolak kas kecil | `staf_tu`, kepala sekolah, `admin` | sama (**menyimpang**, lihat bawah) |
| Crisis: command center, declare, insiden, protokol, broadcast | kepala sekolah, `waka_kesiswaan`, `waka_sarpras`, `admin` | sama |
| Crisis: headcount | `TEACHER_ROLES` + pimpinan + `admin` | sama |
| Anti-bullying: form lapor (GET/POST) | semua user login (`open_to_all`) | semua user login |
| Anti-bullying: daftar, detail, statistik | `bk`, `waka_kesiswaan`, kepala sekolah, `admin` | sama |
| Recruitment, compensation, wellness, turnover | kepala sekolah, `admin` (sama dengan group `hrd.`) | sama (semua halaman baca) |
| Inclusive education (IEP, screening, diferensiasi, akomodasi) | `bk`, `TEACHER_ROLES`, `waka_kesiswaan`, `waka_kurikulum`, kepala sekolah, `admin`; data dibatasi `Student::visibleTo` | `bk`, `waka_kesiswaan`, `admin` |
| Data engineering (streams, NLQ, report scheduler, data quality) | `admin`, kepala sekolah (eksekusi NLQ dihitung "lihat") | `admin` |
| School safety (CCTV, alert, drill, geofence) | `staf_tu`, `waka_sarpras`, kepala sekolah, `admin` | — (baca saja) |
| Campus ops (tamu, toko, event) | `staf_tu`, `waka_sarpras`, kepala sekolah, `admin` | `staf_tu`, `waka_sarpras`, `admin` |
| Campus ops: kantin | **dihapus** | — |
| Knowledge hub | `TEACHER_ROLES`, `bk`, pimpinan, `admin` | sama |
| Family: homework copilot (ortu), acknowledge feed, family plan | `PARENT_ROLES` + data anak sendiri | `PARENT_ROLES` |
| Family: teacher view, buat posting feed | `TEACHER_ROLES` + pimpinan + `admin` | sama |
| Family: timeline feed, term review | `PARENT_ROLES` + `TEACHER_ROLES` + pimpinan + `admin` | — |
| Family: parent skills | `PARENT_ROLES`, `waka_humas`, kepala sekolah, `admin` | sama |
| Adaptive: kalibrasi IRT | `TEACHER_ROLES`, `waka_kurikulum`, kepala sekolah, `admin` | sama |
| Adaptive: ujian CAT | `STUDENT_ROLES`, hanya sesi miliknya | sama |
| Adaptive: daftar sesi CAT, generate learning path, heatmap/kelas mastery | `TEACHER_ROLES` + pimpinan + `admin`; kelas harus terlihat (`Classroom::isVisibleTo`) | sama |
| Adaptive: learning path | `STUDENT_ROLES` + `TEACHER_ROLES` + pimpinan + `admin`; per siswa dibatasi | — |
| Adaptive: mastery per siswa | staf di atas + `STUDENT_ROLES` + `PARENT_ROLES`; dibatasi `Student::visibleTo` | — |
| Microlearning: micro lesson | `STUDENT_ROLES` + `TEACHER_ROLES` + pimpinan + `admin` | `TEACHER_ROLES` + pimpinan + `admin` |
| Microlearning: quiz arena | host/hasil: guru + pimpinan + `admin`; join/live: pembelajar | guru + pimpinan + `admin` |
| Microlearning: spaced repetition, galeri badge | `STUDENT_ROLES` | `STUDENT_ROLES` |
| Microlearning: transkrip | daftar: `TEACHER_ROLES`, `bk`, `staf_tu`, pimpinan, `admin`; per siswa: + `STUDENT_ROLES`/`PARENT_ROLES`, dibatasi | — (`transcripts/verify/{hash}` tetap publik) |
| Career: portal magang, college counseling | `STUDENT_ROLES` (counseling + `bk`) | sama |
| Career: monitoring magang | `TEACHER_ROLES`, `bk`, pimpinan, `admin` | — |
| Career: showcase | galeri `Roles::MEMBERS`; karya sendiri `STUDENT_ROLES` + `StudentShowcasePolicy` | `STUDENT_ROLES` |
| Career: mini bank | dompet `STUDENT_ROLES`; teller `staf_tu`, kepala sekolah, `admin` | — |
| Alumni relations, omnichannel | `waka_humas`, `staf_tu`, kepala sekolah, `admin` | sama |
| Islamic: tahsin, portofolio spiritual | `TEACHER_ROLES` + pimpinan + `admin` | sama |
| Islamic: kalender acara, hub dakwah | `Roles::MEMBERS` | — |
| Green: karbon, sampah, eco project | `staf_tu`, `waka_sarpras`, kepala sekolah, `admin` | `staf_tu`, `waka_sarpras`, `admin` |
| Green: eco challenge | `Roles::MEMBERS` | — |
| Diamond `mobile.*` (web) | guru/siswa/ortu masing-masing; lapor izin hanya untuk anak sendiri | sama |
| Diamond `interactive.*` | jadwal: `waka_kurikulum`, kepala sekolah, `admin`; denah & whiteboard: guru + pimpinan + `admin` (kelas sendiri, host sendiri); kanban: guru + `STAFF_ROLES` + pimpinan + `admin` | sama |
| Diamond `dashboards.*` | eksekutif: pimpinan + `admin`; guru 360: kepala sekolah, `waka_kurikulum`, `admin`; cockpit siswa: `TEACHER_ROLES`, `bk`, pimpinan, `admin` (dibatasi); skor keuangan: kepala sekolah, `staf_tu`, `admin` | — |
| Diamond `publishing.*` | majalah & podcast: `Roles::MEMBERS`; video, data stories: `waka_humas`, kepala sekolah, `admin` | podcast: `TEACHER_ROLES` + `waka_humas` + kepala sekolah + `admin`; lainnya humas + kepala sekolah + `admin` |
| Diamond `workflow.*` | designer, process mining, SLA: kepala sekolah, `admin`; approval: guru + staf + pimpinan + `admin`, rantai milik sendiri kecuali pimpinan/admin | keputusan hanya oleh approver yang ditunjuk |
| Diamond `accessibility.*` | WCAG & terjemahan: `admin`; voice & tema: `Roles::MEMBERS` | sama |

#### Penyimpangan dari usulan 118.1 dan alasannya
1. **Kantin dikeluarkan dari ERP.** Pedagang kantin adalah pihak luar yang menyewa tempat, bukan karyawan sekolah. Route `canteen.*`, `NutritionController`, `NutritionService`, dan dua view-nya dihapus. Model dan tabel `MealPlan`/`CanteenMenu` serta POS merchant (`Finance/CanteenController`) tidak disentuh.
2. **Kepala sekolah boleh menyetujui/menolak kas kecil** (keputusan pemilik produk). Membuat transaksi dan dana tetap hanya `staf_tu`/`admin`.
3. **Islamic tech dan green school dipilah.** Tahsin dan portofolio spiritual hanya untuk guru dan pimpinan. Kalender acara, dakwah, dan eco challenge terbuka untuk semua warga sekolah.
4. **Alumni hanya untuk staf** (humas, TU, pimpinan, admin), bukan untuk alumni sendiri.
5. **Knowledge hub menyertakan `bk`.** BK juga ikut PLC dan berbagi praktik baik.
6. **Publishing dipilah baca/tulis.** Majalah dan podcast dibaca semua warga sekolah, guru boleh merekam podcast, dan sisanya tetap humas/pimpinan. Test lama memperlihatkan guru membuat podcast dan warga sekolah berlangganan majalah.
7. **Guru 360 tidak untuk guru biasa.** Halaman itu menilai seluruh guru.
8. **Route di luar FASE 45–62 masuk daftar utang, bukan langsung diberi peran.** Cakupan PR ini dijaga, dan daftar utang hanya bisa menyusut.
9. **`users.show` dihapus dari resource.** Method-nya kosong (halaman 200 tanpa isi) dan tidak dirujuk di mana pun.

#### Temuan sapuan 118.3
- **Disimpan sungguhan:** `waste.store`. Log sampah tervalidasi dan ditandatangani data pegawai pencatat. `updateLog()` kosong dihapus, dan tracker menampilkan log asli.
- **Dijawab 501 "Fitur ini belum tersedia. Perubahan belum bisa disimpan."** (dulu redirect seolah sukses):
  - `CatController::startExam` (dulu mengarah ke sesi palsu nomor 1) dan `processResponse`; `LearningPathController::generate`.
  - `ConditionalReportController::store`.
  - `ClassFeedController::storePost`, `acknowledgePost`; `FamilyLearningPlanController::storePlan`, `updateProgress`.
  - `CarbonMetricController::storeDailyData` (dulu "Data tersimpan.").
  - `KnowledgeArticleController::store`, `toggleLike`, `storeComment`; `LessonStudyController::store`, `submitObservation`, `advancePhase`; `PlcDiscussionController::store`, `reply`; `SharedResourceController::store`, `rate`.
  - `GamifiedQuizController::store`; `MicroLessonController::store`, `update`, `destroy`, `publish`; `SpacedRepetitionController::submitReview`.
- **Kode mati dihapus** (stub tanpa route, sebagian berisi flash sukses palsu):
  - Alumni: `AlumniAchievement`, `AlumniProfile`, `CrowdfundCampaign`, `Endowment` (12 method).
  - Green: `EcoChallenge::submitEvidence`, `verifyParticipation`; `EcoProject` CRUD dan `show`; `CarbonMetric::calculateMonthly`.
  - HR: `Compensation`, `Recruitment`, `Turnover`, `Wellness` (12 method).
  - Islamic: `DawahContent`, `IslamicEvent`, `SpiritualPortfolio`, `TahsinRecord` (13 method).
  - Safety: `CctvIncident`, `EmergencyAlert`, `GeoFencing`, `SafetyDrill` (7 method).
  - Student: `CollegeCounseling::storeApplication`, `Internship::apply`/`logJournal`, `MiniBanking::processDeposit`/`processTransfer`.
- **Fallback `auth()->id() ?? 1` dihapus** dari `CrisisController` (3 tempat), `EmergencyBroadcastController`, `EventTicketingController`, dan `MerchandiseController`. Pencatat sekarang selalu user yang login.
- **Laporan bullying tidak anonim** sekarang menyimpan `reporter_id`. Sebelumnya pelapor yang memilih menyebut nama tetap hilang.
- **Belum diperbaiki (di luar cakupan, dicatat):**
  - `MerchandiseController::checkout` memercayai `total_amount` dari klien dan mengisi `student_id` dengan id user.
  - `ParentSkillController::searchDirectory` mengabaikan kata kunci.
  - `DailyAssessmentMobileApiController` masih memakai `academic_year_id ?? 1`, dan ini route mobile yang perlu koordinasi.
  - Dashboard akun ber-role `student` tanpa data `students` error 500 (`total_xp` on null).

#### Kepemilikan data (118.2)
- `Student::visibleTo($user)` / `isVisibleTo()` (scope di model):
  - Peran sekolah-luas (`superadmin`, `admin`, `bk`, `staf_tu`, pimpinan) melihat semua siswa.
  - Guru melihat siswa di kelas yang diajarnya (jadwal) atau diwalikannya **tahun ajaran ini**.
  - Siswa hanya melihat dirinya, ortu hanya anaknya, dan peran lain tidak melihat siapa pun.
- `Classroom::isVisibleTo()` dan `StudentDataAccess::authorizeStudentRecord()` (404 bila bukan miliknya).
- Dipakai oleh IEP, screening, akomodasi, homework copilot, cockpit siswa, transkrip, mastery, learning path, sesi CAT, denah kelas, dan lapor izin ortu.
- Keputusan approval hanya oleh approver yang ditunjuk; kanvas whiteboard hanya oleh host.

#### Test
- `tests/Feature/Architecture/RouteGuardTest.php` (118.5.1):
  - Semua route web ber-`auth` berperan atau terdaftar.
  - Daftar hanya berisi route yang masih ada dan masih tanpa aturan.
  - Ke-297 route dari 17 file modul dimuat ke router tersendiri, dan hanya form lapor bullying yang tanpa peran.
  - `RouteAccess` menolak route tak terdaftar.
- `tests/Feature/Security/Fase118AccessControlTest.php` (118.5.2–4 dan 118.2):
  - 403 untuk `siswa`/`orang_tua`/`student`/`parent` di treasury, crisis, recruitment, dan data engineering.
  - 200 untuk form lapor bullying, dan 200 untuk peran yang berhak.
  - Sidebar siswa/ortu tidak memuat menu internal.
  - Log sampah benar-benar bertambah.
  - Stub menjawab 501.
  - IDOR per siswa/kelas/sesi/approval/whiteboard.
- Test lama yang mengodekan perilaku salah (user tanpa peran membuka modul internal, heatmap kelas yang tidak ada = 200, guru menata denah kelas orang lain) ditulis ulang ke peran yang benar, ditambah kasus 403/404-nya. `SmartCampusSuiteTest` kini memastikan route kantin sudah tidak ada.
- Sidebar: blok "Enterprise Platinum" dulu bergantung pada `@if` daftar peran literal dan blok `surveys` yang tidak ditutup, sehingga seluruh menu FASE 51–62 hanya muncul bila user boleh membuka survei. Sekarang tiap menu memakai `@canVisit`, dan judul blok tampil bila minimal satu menunya boleh dibuka.

---

## FASE 119: DEBLOAT, KEMBALI KE ERP SEKOLAH YANG JELAS `[SELESAI]`

> **Tanggal Rancangan:** 5 Oktober 2026
> **Pelaksana:** Agent Backend (repo `sistem-terpadu`, backend + web). Pasangannya di Android: **FASE 78** di `sista-android/android_implementation.md`.
> **Prioritas:** P0. Kerjakan sebelum fitur baru apa pun.
> **Prinsip:** Aplikasi ini adalah ERP untuk **satu SMA Islam di Indonesia**, bukan platform korporat. Setiap modul harus menjawab "siapa di sekolah yang memakai ini setiap minggu?" Kalau jawabannya tidak ada, modulnya dihapus.

### 119.0: Latar Belakang & Keputusan Pemilik Produk

Audit 5 Oktober 2026 menemukan sekitar 180 dari ±300 controller web (±59%) berada di folder modul tambahan FASE 45–68: teknologi pamer, tools developer, HR/manajemen korporat, marketing, media, dan gimmick. Mayoritas cuma 15–40 baris yang menampilkan view tanpa menyimpan data. Database mencapai 504 tabel. Sidebar web bahkan punya seksi "Enterprise Platinum" dan "Enterprise Diamond". Di sisi lain, fitur dasar ERP sekolah (naik kelas massal, impor presensi sidik jari) **belum ada**.

Keputusan pemilik produk (5 Okt 2026):

| Topik | Keputusan | Akibat |
|---|---|---|
| Status produksi | **Belum dipakai, belum ada data asli** | Modul **dihapus permanen** (kode, view, model, migrasi, test), bukan dibekukan |
| Presensi siswa | Siswa boleh memakai HP; presensi lewat **QR per mapel** (Sesi Kelas, FASE 117) | Hapus presensi GPS/geofence, QR identitas/gerbang dinamis, dan face recognition |
| Presensi pegawai | Guru & karyawan memakai **mesin sidik jari** | Aplikasi tidak menyediakan check-in pegawai; buat **impor log mesin** (119.6) |
| Pendidikan inklusi | **Dipertahankan** (sekolah punya siswa ABK) | Modul Inclusive tetap, disederhanakan di bawah BK |
| Penggajian | **Diurus sekolah** | HRD + Payroll tetap |
| Adiwiyata / kantin cashless | **Tidak ada** | Hapus Green School dan Canteen |
| Gamifikasi | **Gimmick, tidak jelas tujuannya** | Hapus seluruhnya (web, API, Android) |

### 119.1: Daftar POTONG (hapus permanen)

**A. File route yang dihapus seluruhnya** (beserta `require` di `routes/web.php`):
`adaptive.php`, `alumni_relations.php`, `campus_ops.php`, `career_readiness.php`, `data_engineering.php`, `extended_diamond.php`, `family_engagement.php`, `foundation.php`, `green_school.php`, `islamic_tech.php`, `knowledge.php`, `microlearning.php`, `omnichannel.php`, `recruitment.php`, `school_safety.php`, `strategic.php`.
`crisis.php` **tidak** dihapus utuh: bagian anti-bullying dipindah ke BK (119.2), sisanya dihapus.

**B. Folder controller yang dihapus seluruhnya** (`app/Http/Controllers/<Folder>`), beserta route di `web.php`/`api.php`, view, service, model, dan test miliknya:

| Kelompok | Folder |
|---|---|
| Teknologi pamer | `Blockchain`, `Iot`, `Platform` (Digital Twin, AI Recommendation, Dashboard Builder, Developer API), `Infrastructure` |
| Tools developer yang nyasar ke aplikasi | `Deployment`, `Performance`, `Integration`, `Debloat`, `UX` (Design System, Print Engine, Translation console) |
| Manajemen korporat | `Grc` (kecuali `AccreditationController`, lihat 119.9), `Strategic`, `Workflow` (Process Mining, Workflow Designer, SLA), `HumanCapital`, `HR` (recruitment, compensation, wellness) |
| Marketing & media | `Communication` (lihat pengecualian di 119.2), `Publishing`, `Community` |
| Gimmick & modul tanpa pemakai | `Adaptive`, `Microlearning`, `Green`, `Campus`, `Safety`, `Knowledge`, `Family`, `Alumni` (endowment, crowdfund), `Islamic` (lihat pengecualian Tahsin), `Ai`, `Accessibility` (voice navigation, WCAG console), `InteractiveUi`, `Mobile` & `Dashboard` (versi `extended_diamond`), `DataEngineering`, `Foundation` (konsolidasi yayasan YBWSA bukan urusan aplikasi satu sekolah) |

**C. Controller satuan yang dihapus:**
- Folder `Admin`: `ApmObservability`, `Observability`, `CacheMonitor`, `QueueMonitor`, `ApiGatewayMonitor`, `EdwOlap`, `DeveloperPortal`, `WebhookHub`, `BpaRuleEngine`, `UatFeedback`, `VendorSla`, `ReportBuilder`, dan UI `DisasterRecovery`. Backup harian **tetap jalan** lewat scheduler `spatie/laravel-backup`; yang dihapus hanya halaman konsolnya.
- Folder `Analytics`: `AtRiskStudentPrediction`, `PredictivePerformance`, `FinancialForecasting`, `TreasuryForecast`, `AcademicBenchmark`, `ParentEngagement`. Label "probabilitas putus sekolah" pada anak di bawah umur tidak punya dasar data dan berisiko.
- Folder `Academic`: `AcademicPathway`, `AiCopilot`, `Scorm`, `VirtualClassroom`. `CompetencyMapping` hanya dihapus bila tidak dipakai rapor (periksa dulu).
- Folder `Student`: `Internship` (SMA tidak punya PKL), `MiniBanking`, `Showcase`, `CollegeCounseling` (match-engine), `CourseEvaluation`, `DigitalID`.
- Folder `Finance`: `Canteen`, `Wadiah`, `FinancialAudit` (fraud detector).
- Folder `Financial`: `CostCenter`, `Procurement`.
- Folder `Growth`: `StudentLifecycle`, `Wellbeing`.
- Folder `Content`: `OcrDocument`, `MediaAsset`.
- Root: `GamificationController`, `ChatbotController`, `CareerOpportunityController`, `SmartAlertController`.
- Presensi: endpoint `mobile/attendance/gps-checkin`, `mobile/attendance/dynamic-qr`, `mobile/attendance/verify-qr`, `iot/face/*`, beserta `GeofenceService` dan logika TOTP-QR yang hanya dipakai di sana.
- API: endpoint `ai/*`, `gamification/*`, `documents/ocr/*`, `documents/signature/*`, `blockchain/*`, `iot/*`, dan endpoint `Debloat`. Hapus juga entri terkait di `config/mobile_features.php` (`student.ai_tutor`, `student.ai_essay`, `student.gamification`, `attendance.gps`, `attendance.id_qr`, `attendance.face_enroll`, `documents.scan`, `documents.signature`, `admin.executive`).

### 119.2: GABUNG & SEDERHANAKAN

| Area | Jadi apa | Asal |
|---|---|---|
| **Keuangan** | Satu menu "Keuangan": tagihan siswa (SPP/uang gedung) + pembayaran VA/Midtrans, beasiswa/potongan, pengeluaran (kas kecil, pembayaran vendor), rekening bank & rekonsiliasi, laporan | `FinanceController`, `StudentFinanceController`, `FinancialReportController`, `Treasury/*`, `Financial/ScholarshipController` |
| **BK** | Satu menu "Bimbingan Konseling": konseling, poin pelanggaran, **laporan perundungan** (TPPK, Permendikbudristek 46/2023), dan **pendidikan inklusi** (IEP, akomodasi) | `CounselingController`, `PointController`, `Crisis/AntiBullyingController`, `Inclusive/*` |
| **Komunikasi** | Pengumuman + notifikasi + WA (Fonnte) + chat ortu ↔ wali kelas. **Satu** pusat notifikasi dan **satu** kalender | `Announcement`, `Whatsapp`, `Notification`, `AcademicEvent`; pertahankan dari `Communication/*` hanya yang dipakai chat & notifikasi Android, sisanya hapus |
| **Dashboard** | **Satu dashboard per peran** (siswa, ortu, guru, BK, TU, pimpinan) | `DashboardController` + isi yang berguna dari `Dashboard/*`, `advanced-dashboards`, `Analytics/*` |
| **Tahfidz & Tahsin** | Satu menu keislaman: Tahfidz, Mutabaah, Tahsin | `TahfidzController`, `MutabaahController`, `Islamic/TahsinRecordController` (gabung bila bukan duplikat `TahsinSubmissionApi`) |
| **Kurikulum Merdeka** | P5 tetap, masuk menu Kurikulum/Rapor | `Growth/P5AssessmentController` |
| **OSIS** | OSIS + MPK satu menu | `OsisController`, `MpkController` |
| **Pengaturan** | Branding + tampilan jadi satu | `Admin/SchoolBranding`, `Admin/Appearance` |

### 119.3: INTI ERP yang Dipertahankan & Diperkuat

- **Data induk:** tahun ajaran & semester, kelas/rombel, mapel, siswa, pegawai, mutasi, alumni + tracer study ringan.
- **Akademik:** jadwal, sesi kelas + presensi QR per mapel, jurnal mengajar, penilaian harian, remedial, rapor (Kurikulum Merdeka + P5), CBT + bank soal, e-learning (materi & tugas).
- **Kesiswaan:** BK (termasuk inklusi & perundungan), UKS, ekstrakurikuler, OSIS/MPK, perpustakaan.
- **Keislaman:** Tahfidz, Mutabaah, Tahsin. Ini pembeda sekolah, jangan dihapus.
- **Keuangan:** sesuai 119.2.
- **Kepegawaian:** data pegawai, presensi (impor sidik jari), penggajian.
- **Administrasi:** SPMB, surat-menyurat, sarpras/inventaris + peminjaman + perawatan, helpdesk, notulen rapat, ekspor Dapodik, survei.
- **Komunikasi:** sesuai 119.2, ditambah portal orang tua dan situs publik/CMS.
- **Sistem:** auth + 2FA, peran & akses, audit log, PDP/privasi, sinkronisasi mobile, feature flag, laporan terjadwal.

### 119.4: Frontend Web

- Hapus seksi sidebar `header_enterprise_platinum` dan `header_enterprise_diamond` beserta semua menu di bawahnya.
- Hapus folder view yang modulnya dipotong, termasuk `fase57`–`fase62`, `developer`, `digital-publishing`, `interactive-ui`, `gamification`, `gamified-quizzes`, `spaced-repetition`, `micro-lessons`, `mini-bank`, `finance/canteen`, `internships`, `scorm`, `virtual-classroom`, `succession`, `evaluation-360`, `workload`, `turnover`, `drip-campaigns`, `nps`, `omnichannel`, `pr`, `releases`, `e2e`, `environment`, `performance`, `design-system`, `data-engineering`, `data-quality`, `nlq`, `risk`, `sop`, `quality`, `strategic`, `foundation`, `mentorship`, `volunteers`, `partnerships`, `wellbeing`, `wellness`, `lifecycle`, `pathway`, `showcase`, `college-counseling`, `career`, `store`, `visitors`, `facility`, `cost-center`, `procurement`, `workflow-automation`, `advanced-dashboards`. Lakukan pengecekan referensi sebelum menghapus.
- Sidebar disusun ulang per area 119.3. Satu menu per area, tanpa duplikat.
- Hapus kunci `navigation.*` dan teks lain yang tidak terpakai dari `lang/{id,en,ar}`.

### 119.5: Bersih-bersih Data & Repo

- Hapus model, factory, seeder, migrasi (*create* dan *alter*), job, event, listener, command, dan policy yang hanya dipakai modul terpotong. Karena belum ada data produksi, **file migrasinya dihapus**, tidak perlu migrasi `drop`. Database lokal cukup `migrate:fresh --seed`.
- Hapus generator scaffolding di `scripts/` (`create_*.php`, `generate_fase*.{php,py,cjs}`, `fix.php`, `gen.php`) dan folder `scratch/`.
- Bersihkan entri usang di `phpstan-baseline.neon` (`reportUnmatchedIgnoredErrors` aktif) dan daftar `DEBT` di `NoFabricatedRandomnessTest` yang filenya ikut terhapus.
- Perbarui `README.md` dan `docs/` agar tidak lagi mengiklankan modul yang sudah dihapus.
- Ganti `sistem-terpadu/android_implementation.md` (salinan basi, 1.761 baris) dengan satu paragraf yang menunjuk ke `sista-android/android_implementation.md` sebagai satu-satunya sumber.
- Status Tracker FASE 45–68 diperbarui: modul yang dihapus ditandai `DIHAPUS (FASE 119)`.

### 119.6: Fitur Inti yang Belum Ada

1. **Impor presensi pegawai dari mesin sidik jari** *(dikerjakan di FASE 119)*: unggah file ekspor mesin (CSV/Excel; format kolom dikonfigurasi agar tidak terikat satu merek), pemetaan ID mesin ↔ pegawai, simpan ke `employee_attendances` (idempoten: impor ulang file yang sama tidak menggandakan), rekap bulanan per pegawai (hadir, telat, tidak hadir) yang bisa dipakai Payroll. Test: impor ganda, ID tak dikenal dilaporkan bukan diabaikan diam-diam, dan rekap kosong bernilai `null`, bukan 0.
2. **Kenaikan kelas, kelulusan massal & riwayat rombel per tahun ajaran** *(dirancang di FASE 120)*: saat ini `students.classroom_id` hanya satu kolom dan `classrooms.academic_year` berupa teks, sehingga riwayat kelas siswa hilang setiap ganti tahun ajaran.
3. **Buku Induk Siswa** *(dirancang setelah FASE 120)*: dokumen administrasi wajib, belum ada di kode.

### 119.7: Urutan Kerja (PR kecil, satu kelompok per PR)

| PR | Isi | Ketergantungan Android |
|---|---|---|
| 1 | Hapus tools developer & teknologi pamer (119.1 B baris 1–2, C bagian `Admin`) | Tidak ada |
| 2 | Hapus modul korporat, marketing & media | Tidak ada |
| 3 | Hapus gimmick + endpoint mobile (AI, gamifikasi, presensi GPS/QR gerbang/wajah, OCR, tanda tangan) | **Setelah** PR FASE 78 Android yang menghapus layar pemakainya di-merge |
| 4 | Gabung & sederhanakan (119.2) + rapikan sidebar (119.4). Tambah endpoint API **lapor perundungan** untuk Android | Android FASE 78 memakai endpoint lapor perundungan |
| 5 | Bersih-bersih data & repo (119.5) | Tidak ada |
| 6 | Impor presensi sidik jari (119.6.1) | Tidak ada |

Setiap PR mencantumkan angka sebelum/sesudah: jumlah controller, route, tabel, dan view.

### 119.8: Kriteria Selesai

- [x] Tidak ada lagi namespace/folder 119.1 B di `app/`. `phpstan` dan `pint` hijau, `php artisan test` hijau (kecuali `Fase85DebloatPragmatismTest > single vps` yang khusus produksi, atau test itu sekalian disesuaikan).
- [x] `php artisan migrate:fresh --seed` sukses dari nol.
- [x] Test arsitektur baru: daftar prefix route terlarang (`blockchain`, `iot`, `gamification`, `ai/`, `green-school`, `treasury-forecast`, dst.) tidak muncul di `route:list`, agar modul yang sudah dipotong tidak hidup lagi.
- [x] Sidebar tidak punya seksi Enterprise. Setiap peran melihat satu dashboard.
- [x] Endpoint lapor perundungan untuk mobile tersedia, dengan test akses (semua user login boleh lapor, hanya BK/pimpinan yang boleh lihat).
- [x] Impor sidik jari berjalan dengan test 119.6.1.
- [x] Bagian ini diperbarui menjadi `[SELESAI]` beserta angka sebelum/sesudah dan daftar modul yang ternyata dipertahankan (beserta alasannya).

### 119.9: Menunggu Keputusan Pemilik Produk (jangan disentuh di FASE 119)

> Sudah diputuskan sesudah FASE 119, lihat 119.11.

Modul berikut abu-abu. Biarkan apa adanya sampai pemilik produk memutuskan:
- Tryout UTBK + rekomendasi PTN (`UtbkMobileApi`).
- Evaluasi guru oleh siswa (`EvaluationMobileApi`).
- EDS / Rapor Mutu & dashboard kesiapan akreditasi (`EdsController`, `ComplianceController`, `Grc/AccreditationController`). Sudah ada platform resmi pemerintah (Rapor Pendidikan).
- Forum diskusi kelas (`ForumController`).
- Kampanye donasi/infaq (`Finance/CharityController`).
- Perencanaan anggaran/RKAS (`Financial/BudgetPlanningController`).
- Portofolio siswa, To-do guru, penjadwal pertemuan ortu–guru (`PortfolioController`, `TeacherTodoController`, `Growth/PtcSchedulerController`).

### 119.10: Hasil `[SELESAI]`

Dikerjakan dalam enam PR sesuai 119.7: #41 (tools developer & teknologi pamer), #42 (korporat, marketing & media), #43 (gimmick & endpoint mobile), #44 (gabung & sederhanakan, sidebar, lapor perundungan), #45 (bersih-bersih data & repo), dan #46 (impor sidik jari).

| | Sebelum FASE 119 | Sesudah |
|---|---:|---:|
| Controller | 351 | 177 |
| Route | 1734 | 1113 |
| View | 946 | 564 |
| Tabel (`Schema::create`) | 504 | 252 |
| Model | 490 | 238 |
| Service | 262 | 111 |

**Yang ditambahkan atau diperbaiki di sepanjang jalan:**
- **Endpoint lapor perundungan** untuk Android (`api/v1/counseling/bullying-reports`).
- **Impor presensi sidik jari** dengan rekap bulanan yang jujur.
- **Satu kalender** (`AcademicEvent::visibleTo`) untuk web dan aplikasi.
- **Satu dashboard per peran** (`User::dashboardRole`) tanpa angka karangan.
- **Menu sidebar per area** yang mengikuti aturan route dan policy.
- **Celah akses yang ditutup:**
  - Route beasiswa dan kalender kedua yang terbuka untuk semua akun.
  - Gateway pembayaran tiruan yang bisa menandai tagihan lunas tanpa uang masuk.
  - Akun `siswa` yang terkunci dari tagihannya sendiri.

**Modul yang dipertahankan walau berada di folder 119.1 B, beserta alasannya:**
- **`Integration/` (Dapodik, EMIS):** ekspor Dapodik termasuk inti 119.3. Yang dihapus hanya tools integrasi developer.
- **`Grc/AccreditationController`, `HumanCapital/Evaluation360Controller`:**
  - Accreditation termasuk 119.9.
  - Evaluasi 360 adalah satu-satunya tempat siklus evaluasi dibuat untuk `EvaluationMobileApi` (119.9).
- **`Communication/`:**
  - Messaging dan pusat notifikasi dipakai web dan Android (119.2).
  - Booking ruang/alat dipertahankan atas keputusan pemilik produk (6 Okt 2026).
- **`Islamic/TahsinRecordController`:** Tahsin termasuk inti keislaman (119.3).
- **`Campus/VisitorService`, `EventTicketingService`:** masih dipakai `ScannerMobileApiController`.
- **`Mobile/*`:** API, sync, dan push untuk aplikasi Android, bukan halaman mobile web `extended_diamond` yang dihapus.
- **`Dashboard/FinancialScoreboardService`:** kini tampil sebagai "Kesehatan Keuangan" di dashboard pimpinan.
- **Model wadiah dan kantin:** dipakai `Finance/CharityController` (119.9) dan portal orang tua. UI-nya sudah dihapus.
- **`AdaptiveRiskEngine`:** mesin risiko login (keamanan), bukan adaptive learning.

**Belum diputuskan atau ditindaklanjuti di luar FASE 119:**
- **Modul 119.9** tetap menunggu keputusan pemilik produk.
- **EMIS:** `EmisService` masih mengirim `'Sudah Sertifikasi'` tetap untuk profil sekolah, dan dokumentasi menyebut fallback `"Ibu Kandung Siswa"` untuk Dapodik. Bila kode masih mengirimnya, itu teks karangan (CLAUDE.md aturan 1).
- **Payroll** masih menghitung hari kerja sendiri (Senin–Jumat). `EmployeeAttendanceRecap::forEmployee()` siap dipakai bila payroll ingin memakai hari yang tercakup log mesin.
- **Tahsin:** `TahsinRecordController` (penilaian guru) dan `TahsinSubmissionApi` (setoran audio siswa) bukan duplikat. Penggabungan alurnya perlu keputusan produk.

### 119.11: Keputusan Modul 119.9 dan Tindak Lanjut

Pemilik produk menyerahkan keputusan modul 119.9 ke tim backend (11 Okt 2026). Ukurannya satu: apakah modul ini dipakai sehari-hari oleh TU, guru, siswa, atau orang tua SMA Islam Sultan Agung 1 Semarang dan punya pemilik yang jelas. Kalau dipertahankan, alurnya harus tersimpan, aksesnya ikut aturan peran, dan tidak ada angka karangan.

**Dipertahankan dan diperbaiki (satu PR per kelompok):**
- **RKAS / RAPBS:** TU atau bendahara menyusun, kepala sekolah menyetujui, penyusun tidak bisa menyetujui RKAS buatannya sendiri. Realisasi dibaca dari buku besar. Pos tanpa akun dicatat manual, dan "belum ada data" tampil "–", bukan 0%. Halaman proyeksi yang mengarang pertumbuhan 5% dihapus.
- **Infaq / donasi:** potongan tabungan hanya dari anak di keluarga donatur sendiri. Sebelumnya `Student::first()` bisa terdebit untuk donatur yang tidak punya anak.
- **Penjadwal pertemuan ortu–guru:** orang tua hanya memesan untuk anaknya sendiri, dan satu slot tidak bisa dipesan dua kali. Pemesanan bisa dibatalkan.
- **Evaluasi guru oleh siswa dan 360°:**
  - Siswa hanya menilai guru yang mengajar di kelasnya menurut jadwal tahun ajaran aktif.
  - Perspektif penilai ditentukan dari akun, bukan dipilih di form.
  - Ringkasan tidak lagi mengisi perspektif kosong dengan 4.2/4.1/4.5.
  - Hasil hanya terbuka untuk pimpinan dan guru yang dinilai.

**Dipotong:**
- **Forum diskusi kelas:** komunikasi kelas sudah lewat chat ortu ↔ wali kelas, pengumuman, dan e-learning. Forum terbuka untuk anak di bawah umur menuntut moderasi harian yang tidak punya pemilik.
- **To-do guru:** catatan pribadi, bukan alur kerja sekolah. Agenda sekolah ada di kalender (`AcademicEvent`).
- **Portofolio siswa** (termasuk tautan publik `portfolio/{uuid}` dan lencana): prestasi tercatat di `Achievement` dan prestasi ekstrakurikuler. Halaman publik tanpa verifikasi berisiko membuka data anak.
- **EDS / Rapor Mutu, dashboard akreditasi, konsol compliance** (retensi data, kontrak legal, forensik, log ekspor Dapodik): sudah ada platform resmi pemerintah (Rapor Pendidikan), dan skor kesiapannya tidak punya sumber data. Ekspor Dapodik dan EMIS tetap di modul integrasi.
- **Tryout UTBK + rekomendasi PTN:** daftar program dan passing grade PTN-nya hanya isian seeder, bukan data resmi SNPMB, jadi "rekomendasi" ke siswa tidak berdasar. Persiapan UTBK tetap bisa lewat CBT sekolah (jenis ujian `try_out`) dan acara kalender `tryout`. Layar Android dicabut lebih dulu, baru endpoint `api/v1/utbk/*`, `UtbkService`, model `Tryout*`/`PtnProgram`, tabelnya, dan kunci kapabilitas `student.utbk`.

**Yang ikut beres dari daftar "belum diputuskan" 119.10:**
- **Payroll** memakai `EmployeeAttendanceRecap`.
  - Hari kerja diambil dari `HRD_WORK_DAYS` yang tercakup log mesin, dan cuti yang disetujui masuk ke rekap.
  - Tarif potongan disimpan di pengaturan. Bila belum diisi, nilainya `null`, bukan Rp 25.000/Rp 100.000 karangan.
  - Persetujuan dan pembayaran berlaku per slip.
- **Cuti pegawai** lewat alur persetujuan `leave_approval` (waka kurikulum → kepala sekolah). Sebelumnya cuti tidak pernah bisa disetujui.
- **Dapodik/EMIS:**
  - Tidak ada lagi `"Ibu Kandung Siswa"` atau `'Sudah Sertifikasi'` karangan. Kolom yang kosong dilaporkan sebagai data belum lengkap.
  - Ditambahkan tempat lahir, nama ibu kandung, dan NUPTK.
  - Konversi SPMB membawa jenis kelamin, tanggal lahir, dan alamat pendaftar. Konversi tidak lagi mengarang NISN atau tagihan Uang Pangkal Rp 5.000.000.


### 119.12: Sisa Bersih-bersih `[RANCANGAN BARU]`

> Ditemukan saat verifikasi PM (11 Okt 2026). Butir 1–5 dalam **satu PR**, butir 6 dalam PR kecil terpisah. Keduanya sebelum FASE 120.

1. **Hapus sisa gimmick `Operations/`:** `IotFacilityController`, `UtilityTrackerController`, `BusRoutingController` (masing-masing ±16 baris), route `facility/iot/dashboard`, `facility/utility/dashboard`, dan `facility/transport/routes` (`routes/web.php` ±335–341), beserta view, model/tabel, dan test yang hanya dipakai ketiganya. Tambahkan prefix-nya ke test arsitektur route terlarang.
2. **Satukan perawatan aset (ALUR_KERJA I3):** saat ini ada `AssetMaintenanceController` (root), `Admin/AssetMaintenanceTicketController`, dan `Operations/AssetMaintenanceController` (jadwal). Pertahankan **satu** alur: lapor lewat QR → tiket → ditangani sarpras → selesai/dihapus dari daftar (`asset_maintenance_tickets`). Fungsi yang benar-benar dipakai dari dua lainnya dipindah ke sana, sisanya dihapus.
3. **Ganti nama folder view peninggalan fase:** `resources/views/fase57/tahsin` → `resources/views/tahsin`, `resources/views/fase58/*` → `resources/views/inclusive/*`, lalu sesuaikan semua `view('fase57.…')`/`view('fase58.…')`.
4. **Ganti nama file route** agar sesuai isinya: `routes/crisis.php` (kini hanya perundungan) → `routes/bullying.php`, `routes/islamic_tech.php` (kini hanya Tahsin) → `routes/tahsin.php`. Nama route (`crisis.bullying.*`) boleh tetap bila dipakai di banyak tempat; yang penting nama file jujur.
5. **Pindahkan dokumen basi** `fase_88_97.md` dan `security_patch.md` dari root ke `docs/archive/`.
6. **Endpoint jadwal sholat** untuk widget Android yang dipertahankan (pasangan `sista-android` FASE 79.4). Saat ini backend tidak punya sumber jadwal sholat, sehingga widget kosong.
   - `GET api/v1/prayer-times?date=YYYY-MM-DD` menghitung Subuh, Dzuhur, Ashar, Maghrib, dan Isya untuk **koordinat sekolah** yang disimpan di pengaturan.
   - Parameter perhitungan mengikuti **Kemenag RI** (sudut Subuh 20°, Isya 18°, ihtiyat), memakai pustaka yang teruji, bukan rumus buatan sendiri.
   - Bila koordinat belum diatur, kirim `null`, bukan jadwal contoh (CLAUDE.md aturan 1).
   - Waktu dalam ISO-8601 Asia/Jakarta. Kunci kapabilitas `prayer_times` untuk semua peran yang login.
   - Test: koordinat kosong mengembalikan `null`, dan satu tanggal acuan cocok dengan jadwal Kemenag untuk Semarang (toleransi ±2 menit).

   Butir ini adalah fitur kecil, bukan bersih-bersih. Dipasang di sini karena widget-nya sudah diputuskan dipertahankan.

**Selesai bila:** `pint`, `phpstan`, dan `php artisan test` hijau, tidak ada lagi `fase5x` di `resources/views`, dan baris I3 di `docs/ALUR_KERJA.md` menjadi ✅.

---

## FASE 120: JENIS AKUN & JABATAN, ROMBEL, TAHUN AJARAN, MAPEL PILIHAN, KENAIKAN & KELULUSAN `[RANCANGAN BARU]`

> **Tanggal rancangan:** 11 Oktober 2026
> **Pelaksana:** Agent Backend (backend + web). Pasangan Android: **FASE 80**.
> **Prioritas:** P0, fondasi sebelum go-live. Wajib selesai sebelum pilot Januari 2027.
> **Alur yang dibangun:** `docs/ALUR_KERJA.md` L1–L2 dan A0–A6.

### 120.0: Masalah

1. **Riwayat rombel tidak ada.** `students.classroom_id` hanya satu kolom. Setiap pergantian tahun ajaran, data "siswa ini tahun lalu di rombel mana" hilang, padahal rekap wali kelas, rapor lama, dan buku induk membutuhkannya.
2. **Rombel tidak terikat ke tahun ajaran.** `classrooms.academic_year` berupa teks ("2026/2027"), sedangkan `academic_years` adalah baris per **semester** (`year` + `semester`). Dua konsep ini tidak tersambung lewat kunci asing.
3. **Model masih kurikulum lama.** `classrooms.major` diisi IPA/IPS oleh seeder, dan `subjects.category` berisi `Nasional/Lokal/Kejuruan/Ciri Khas` (Kejuruan adalah SMK). Sekolah sudah **full Kurikulum Merdeka**: tanpa jurusan, dengan mapel pilihan di XI–XII.
4. **Tidak ada alur kenaikan kelas.** Kelulusan sudah ada (`AlumniController::bulkGraduate`), tetapi tidak terikat ke keputusan atau riwayat. Mutasi (`student_mutations`) tidak lewat persetujuan dan tidak mengubah rombel.
5. **106 tempat di kode** membaca `student->classroom_id` / `->classroom` langsung.
6. **Peran disimpan di satu kolom `users.role`.** Kepala sekolah dan waka adalah nilai kolom itu, padahal mereka **jabatan berperiode** yang dipegang guru. Akibatnya:
   - kepala sekolah yang mengajar tidak bisa sekaligus guru, dan waka yang juga wali kelas harus memilih salah satu;
   - ejaan peran bercabang (`student`/`siswa`, `parent`/`orang_tua`/`orangtua`/`wali_murid`, `guru`/`teacher`, `kepala_sekolah`/`kepsek`);
   - ada sekitar 385 pemeriksaan `->role` di `app/` dan 114 di `resources/views/`;
   - struktur jabatan tidak bisa diubah sekolah tanpa developer.
7. **Kalender akademik belum jadi sumber tanggal.** `academic_events` hanya daftar acara (`event_type` teks bebas). Tidak ada pengesahan dan tidak ada penanda kegiatan kunci, sehingga alur lain (survei peminatan, batas rapor, rapat kenaikan) tidak tahu tanggalnya dan cenderung ditanam di kode.

### 120.1: Keputusan Pemilik Produk (11 Okt 2026)

- **Full Kurikulum Merdeka.** Kelas X umum. Jumlah rombel XI mengikuti kebutuhan sekolah (saat ini 11 rombel X, X-1 s.d. X-11, menjadi 11 rombel XI). Setiap rombel XI–XII punya **susunan mapel pilihan sendiri** yang dirancang Waka Kurikulum. Siswa tidak berpindah kelas per mapel.
- **Survei peminatan hanya untuk memetakan minat**, bukan pendaftaran yang mengikat. Siswa kelas X memilih mapel pilihan yang diminati. Hasilnya berupa **statistik** yang dipakai Waka Kurikulum untuk tiga hal:
  1. merancang susunan mapel tiap rombel XI;
  2. memplot siswa ke rombel yang paling cocok;
  3. menyusun jadwal.
- **Alurnya dinamis dan dikendalikan pengguna.** Sistem **tidak** mengalokasikan siswa secara otomatis, tidak memakai kuota, dan tidak punya kriteria prioritas. Semua keputusan plotting ada di tangan Waka Kurikulum. Sistem hanya menyajikan statistik, menandai kecocokan, dan mencatat keputusan.
- **Kenaikan kelas ditetapkan Waka Kurikulum** dengan sepengetahuan Kepala Sekolah. Kepala sekolah menerima notifikasi dan dapat **menahan** keputusan sebelum dieksekusi.
- **Kelulusan disahkan Kepala Sekolah** (SK kelulusan), sesuai praktik umum.
- **Peran dua lapis + penugasan dari data** (lihat `ALUR_KERJA.md` bagian 2):
  - 4 jenis akun statis;
  - jabatan dinamis yang **bisa ditambah, diubah, dan dinonaktifkan oleh Admin**. Hak akses sensitif hanya oleh Admin Utama;
  - jabatan bawaan: Kepala Sekolah, Waka Kurikulum, Waka Kesiswaan, Waka Sarpras, Waka Humas, Kepala Perpustakaan, Kepala TU.
- **Kalender akademik disusun ulang setiap tahun** oleh Waka Kurikulum dan disahkan Kepala Sekolah. Urutan kegiatan tetap, tetapi tanggalnya berubah. Sistem membaca tanggal dari kalender itu.

### 120.2: Model Data (usulan, boleh disesuaikan dengan alasan tertulis)

| Tabel | Isi | Catatan |
|---|---|---|
| `users` *(ubah)* | `role` → `account_type` (`siswa/orang_tua/guru/karyawan`), + `status` (`aktif/nonaktif`) dan `deactivated_reason` (`lulus/mutasi/resign/pensiun/…`). `superadmin` dipisah sebagai penanda akun teknis | Semua ejaan lama dipetakan ke satu kode. Akun tidak pernah dihapus |
| `positions` *(baru)* | `code` (otomatis, stabil), `name`, `description`, `allowed_account_types` (json), `is_system`, `is_active` | Katalog jabatan, dikelola Admin. Seed 7 jabatan bawaan. `kepala_sekolah` ber-`is_system` |
| `position_permissions` *(baru)* | `position_id`, `permission` | `permission` adalah kode dari **daftar tetap di kode** (`config/permissions.php`), dikelompokkan per modul dan ditandai `sensitive` |
| `position_assignments` *(baru)* | `position_id`, `user_id`, `starts_on`, `ends_on`, `school_year_id` (nullable), `decree_number`, `is_acting` (Plt), `assigned_by` | Boleh banyak per orang. Aktif bila hari ini di dalam periode |
| `workflow_definitions.steps` *(ubah)* | langkah merujuk `position` (kode jabatan) atau `permission`, bukan string role | Kotak masuk persetujuan mengikuti siapa pemegang jabatan saat itu |
| `school_years` *(baru)* | `name` ("2027/2028"), `start_date`, `end_date`, `status` (`draf/aktif/ditutup`) | Satu baris per tahun ajaran. Hanya satu yang `aktif` |
| `academic_years` *(ubah)* | + `school_year_id` | Tetap baris per semester. Diisi dari kolom `year` yang ada |
| `academic_calendars` *(baru)* | `school_year_id`, `status` (`draf/disahkan/terbit`), `approved_by`, `approved_at`, `published_at` | Satu kalender akademik per TA |
| `academic_events` *(ubah)* | + `academic_calendar_id` (nullable untuk acara biasa), + `milestone` (kode kegiatan kunci, nullable) | Daftar kode tetap di satu enum/konstanta: `ta_mulai`, `ta_selesai`, `semester_mulai`, `semester_selesai`, `pts`, `penilaian_akhir_semester`, `penilaian_akhir_tahun`, `batas_input_nilai`, `pembagian_rapor`, `survei_peminatan`, `rapat_kenaikan`, `kelulusan`, `mpls`, `spmb`. Untuk kode per semester, satu per semester per kalender. Label tampilan tiga bahasa; kode tidak diterjemahkan |
| `classrooms` *(ubah)* | + `school_year_id`; kolom `major` dan teks `academic_year` dihapus setelah semua pemakai pindah | Nama rombel boleh sama tiap tahun; identitasnya `id` + `school_year_id` |
| `class_enrollments` *(baru)* | `student_id`, `classroom_id`, `school_year_id`, `status` (`aktif/naik/tinggal/lulus/mutasi_keluar/keluar/pindah_rombel`), `started_on`, `ended_on`, `ended_reason`, `decided_by`, `promotion_decision_id` | Paling banyak **satu** `aktif` per siswa (ditegakkan service + test). Pindah rombel di tengah tahun menutup baris lama dan membuka baris baru |
| `subjects` *(ubah)* | `category` → `umum / pilihan / muatan_lokal / ciri_khas`; + `display_group` (nullable, misalnya "Ilmu Pengetahuan Alam") untuk mapel yang di rapor kelas X ditulis sebagai sub-mapel | Petakan data lama. `Kejuruan` dihapus. Kelompok cetak rapor: A = umum + muatan lokal, B = pilihan, C = ciri khas (format rapor sekolah di `ALUR_KERJA.md` D5) |
| `grade_subject_templates` *(baru)* | `school_year_id`, `grade_level` (X/XI/XII), `subject_id` | **Struktur mapel umum, muatan lokal, dan ciri khas per tingkat per TA.** Contohnya Ciri Khusus kelas X berbeda dengan XI–XII. Rombel mendapat mapel tingkatnya + `classroom_subjects` (pilihan) |
| `classroom_subjects` *(baru)* | `classroom_id`, `subject_id` | Susunan mapel **pilihan** per rombel XI/XII, dirancang Waka Kurikulum. Satu sumber untuk jadwal (C1), penilaian, dan rapor rombel itu. Rombel X tidak memerlukannya |
| `elective_surveys` *(baru)* | `school_year_id` (TA target), `title`, `opens_at`, `closes_at`, `min_choices`, `max_choices`, `ranked` (bool, peringkat opsional), `status` (`draf/dibuka/ditutup/selesai`) | Satu survei per angkatan. Waka boleh membuka ulang sebelum `selesai` |
| `elective_survey_subjects` *(baru)* | `elective_survey_id`, `subject_id` | Mapel pilihan yang ditawarkan di survei itu |
| `elective_choices` *(baru)* | `elective_survey_id`, `student_id`, `subject_id`, `rank` (nullable) | Unik (survei, siswa, mapel). Bisa diubah selama `dibuka` |
| `class_placements` *(baru)* | `school_year_id` (TA target), `student_id`, `classroom_id`, `placed_by`, `note`, timestamps | Hasil **plotting manual** Waka Kurikulum. Satu penempatan per siswa per TA target. Setiap perubahan tercatat di audit log. Dipakai saat eksekusi kenaikan (C) |
| `promotion_batches` *(baru)* | `from_school_year_id`, `to_school_year_id`, `status` (`draf/ditinjau_wali/ditetapkan/dieksekusi/dibatalkan`), `meeting_note_id` (notulen rapat dewan guru), `held_by`/`held_at` (penahanan kepala sekolah) | Satu batch per pergantian tahun |
| `promotion_decisions` *(baru)* | `promotion_batch_id`, `student_id`, `from_classroom_id`, `decision` (`naik/tinggal/lulus`), `to_classroom_id`, `basis` (json ringkasan dasar), `homeroom_note`, `decided_by` | Satu per siswa per batch |

**`students.classroom_id` dipertahankan** sebagai *rombel aktif* yang ditulis **hanya** oleh `EnrollmentService`. Dengan begitu 106 pemakainya tidak rusak sekaligus. Test arsitektur menolak penulisan `classroom_id` siswa di luar service itu. Pembacaan untuk konteks lampau (rapor lama, rekap TA lalu, buku induk) wajib lewat `class_enrollments`.

### 120.3: Alur yang Dibangun

**R. Jenis akun & jabatan (ALUR L1–L2), dikerjakan paling awal**
- **Daftar hak akses tetap di kode** (`config/permissions.php`). Diturunkan dari kelompok route yang ada, misalnya:
  - `kurikulum.kelola`, `kurikulum.tetapkan_kenaikan`;
  - `kesiswaan.kelola`, `bk.catatan_rahasia`*, `sarpras.kelola`, `humas.kelola`, `perpustakaan.kelola`, `tu.kelola`, `uks.kelola`*;
  - `keuangan.kelola`*, `keuangan.setujui`*, `kepegawaian.gaji`*;
  - `pimpinan.dasbor`, `pimpinan.mengesahkan`;
  - `sistem.kelola_jabatan`, `sistem.kelola_admin`*.

  Tanda `*` berarti sensitif. Daftar final disusun pelaksana dari route yang ada, dan setiap kode punya label tiga bahasa.
- **Route memeriksa hak akses** (`can:`/`Gate`), bukan nama jabatan:
  - Jenis akun memberi hak dasar: menu siswa, orang tua, guru, atau karyawan.
  - Jabatan aktif menambah hak.
  - Penugasan dari data (wali kelas, pembina, guru mapel) tetap diperiksa lewat policy per objek.
  - `RouteAccess`, `MobileCapabilities`, dan sidebar membaca aturan yang sama, sehingga menu dan aplikasi Android otomatis mengikuti.
- **Layar Admin "Jabatan & Hak Akses" (L1):**
  - buat, ubah, dan nonaktifkan jabatan;
  - centang hak akses per modul, dengan pratinjau dampak sebelum disimpan.

  Hak akses sensitif hanya bisa diubah `superadmin`. `kepala_sekolah` tidak bisa dihapus dan tidak bisa kehilangan `pimpinan.mengesahkan`.
- **Layar "Pejabat" (L2):** tetapkan orang ke jabatan dengan periode dan nomor SK, termasuk Plt. Daftar pejabat bisa disalin ke TA baru sebagai draf.
- **Migrasi dari kolom `role` lama:**
  - Akun `kepala_sekolah`/`waka_*` menjadi Guru (atau Karyawan) + penugasan jabatan yang sesuai.
  - Akun `staf_tu` menjadi Karyawan + jabatan sesuai data seed.
  - Semua `->role ===`/`in_array(role…)` diganti `hasPermission()`/`can()`. Test arsitektur menolak pemeriksaan `->role` baru.
  - `StudentDataAccess::*_ROLES` diganti pemeriksaan `account_type`.

**A0. Kalender akademik (ALUR A0)**
- Waka Kurikulum menyusun kalender TA baru: libur, hari efektif, acara, dan **kegiatan kunci** (kolom `milestone`). Kalender bisa disalin dari TA sebelumnya sebagai draf, tetapi **semua tanggal wajib ditinjau ulang**. Draf salinan ditandai "belum ditinjau" sampai disimpan ulang.
- Kepala sekolah mengesahkan lewat `WorkflowService` (definisi `academic_calendar`). Setelah terbit, kalender tampil di kalender web dan Android (J5).
- Revisi setelah terbit tercatat. Bila tanggal kegiatan kunci berubah, peran yang terdampak diberi notifikasi.
- **Alur lain membaca tanggal dari sini:**
  - tanggal awal/akhir TA dan semester (A1);
  - usulan jendela survei peminatan (B, tetap bisa diubah waka);
  - pengingat batas input nilai dan pembagian rapor (D5, FASE 121);
  - tanggal rapat kenaikan (C).
- **Bila kegiatan kunci belum diisi**, alur yang membutuhkannya menampilkan "tanggal belum ditetapkan di kalender akademik" dan tidak memakai tanggal tebakan.

**A. Tahun ajaran & rombel (ALUR A1–A2)**
- Menu web **"Tahun Ajaran & Rombel"** (Waka Kurikulum + admin): buat TA (otomatis membuat dua semester, tanggalnya dari kalender akademik A0) → salin struktur rombel dari TA aktif sebagai draf → tetapkan wali kelas → aktifkan.
- Aktivasi TA baru adalah satu aksi yang menutup TA lama, menjalankan eksekusi kenaikan (C), dan mengaktifkan semester Ganjil. Aksi ini idempoten dan punya konfirmasi ringkasan ("312 naik, 4 tinggal, 298 lulus, 340 siswa baru").
- Pergantian semester Genap **tidak** mengubah rombel.

**B. Survei peminatan, perancangan rombel XI, dan plotting (ALUR A3)**
1. **Buka survei.** Waka kurikulum memilih mapel pilihan yang ditawarkan, jumlah mapel yang harus dipilih siswa (`min_choices`–`max_choices`), dan apakah memakai peringkat.
2. **Siswa mengisi.** Siswa kelas X mengisi di Android (web sebagai cadangan), dan orang tua bisa melihat pilihan anaknya. Pilihan bisa diubah sampai survei ditutup.
3. **Statistik (inti fitur ini).** Isinya apa adanya dari pilihan siswa, dan bisa diekspor ke Excel:
   - jumlah peminat per mapel;
   - **kombinasi mapel yang paling sering dipilih bersama**;
   - sebaran pilihan per rombel X;
   - daftar siswa yang belum mengisi.
4. **Rancang rombel XI** untuk TA berikutnya. Waka kurikulum menentukan berapa rombel (misalnya XI-1 s.d. XI-11), nama, wali kelas (bisa menyusul), dan **susunan mapel pilihan tiap rombel** (`classroom_subjects`). Statistik tampil di samping sebagai acuan.
5. **Plot siswa** ke rombel XI (`class_placements`).
   - Untuk tiap siswa, sistem menampilkan pilihannya dan **tingkat kecocokan** dengan setiap rombel, misalnya "4 dari 4 mapel cocok", serta mengurutkan rombel yang paling cocok **sebagai saran**.
   - Sistem **tidak menempatkan sendiri.** Plotting bisa massal: pilih banyak siswa lalu masukkan ke satu rombel.
   - Selalu terlihat: jumlah siswa per rombel, siswa yang belum diplot, dan siswa yang ditempatkan di rombel yang tidak cocok dengan pilihannya. Yang terakhir ini boleh, tetapi diberi catatan.
6. **Jadwal XI** disusun di modul jadwal (C1) dari susunan mapel tiap rombel.
7. **Umumkan.** Waka menandai survei `selesai` dan mengumumkan rombel XI ke siswa dan orang tua. Kepala sekolah diberi tahu. Plotting masih boleh direvisi sampai TA baru aktif, dan setiap perubahan tercatat.
8. **Eksekusi.** Saat kenaikan dijalankan (C), siswa X yang naik masuk ke rombel hasil plotting.

**C. Kenaikan kelas (ALUR A4)**
1. Setelah rapor Genap disahkan, waka kurikulum membuat batch. Sistem menyusun draf per siswa beserta `basis`: jumlah mapel di bawah KKTP, persentase kehadiran, dan catatan BK bila ada. **Ambang kenaikan diisi sekolah di pengaturan.** Bila kosong, draf hanya menampilkan data tanpa label "naik/tinggal" otomatis.
2. Wali kelas meninjau kelasnya (`ditinjau_wali`): ubah keputusan dengan catatan. Wali kelas tidak bisa mengubah kelas lain.
3. Rapat dewan guru dicatat di notulen dan ditautkan ke batch.
4. Waka kurikulum **menetapkan** lewat `WorkflowService` dengan definisi baru `promotion_decision`. Kepala sekolah mendapat notifikasi "mengetahui" dan dapat **menahan** sebelum eksekusi. Penahanan mengembalikan batch ke `ditinjau_wali` dengan catatan.
5. Tujuan siswa ditentukan sebelum eksekusi:
   - X → XI: rombel hasil plotting (B). Siswa X yang naik tetapi belum diplot **menghalangi eksekusi**, dan daftarnya ditampilkan.
   - XI → XII: rombel XII lanjutan, dengan susunan mapel disalin dari rombel XI-nya.
   - Siswa yang tinggal kelas masuk ke rombel tingkat yang sama di TA baru (dipilih waka).
6. **Eksekusi** saat aktivasi TA baru, dalam satu transaksi: enrollment lama ditutup (`naik/tinggal/lulus`), enrollment baru dibuat, dan `students.classroom_id` disinkronkan. Batch bisa **dibatalkan** selama TA baru belum diaktifkan.

**D. Kelulusan (ALUR A5)**
Siswa kelas XII dengan keputusan `lulus` memerlukan **pengesahan kepala sekolah** (langkah persetujuan terpisah dalam batch yang sama, karena kelulusan di-SK-kan kepala sekolah). Setelah disahkan: siswa menjadi alumni lewat service yang sudah ada (`AlumniEnhancedService::bulkGraduateStudents`, disesuaikan agar menulis enrollment `lulus`). Form "Luluskan massal" yang berdiri sendiri dihapus atau dialihkan ke alur ini.

**E. Mutasi (ALUR A6)**
`student_mutations` diberi status dan persetujuan `WorkflowService` dengan definisi baru `student_mutation` (TU mengajukan → Kepala Sekolah). Setelah disetujui, `EnrollmentService` menutup atau membuka enrollment. Mutasi keluar juga menandai tagihan berjalan untuk ditinjau TU (tidak dihapus otomatis) dan menonaktifkan akun siswa.

### 120.4: API untuk Android (FASE 80)

| Endpoint | Peran | Isi |
|---|---|---|
| `GET student/elective-survey` | siswa kelas X | Survei aktif, mapel pilihan yang ditawarkan, `min_choices`/`max_choices`, apakah berperingkat, pilihan saya, status, tenggat |
| `PUT student/elective-survey/choices` | siswa kelas X | Simpan pilihan mapel (dan peringkat bila `ranked`). Ditolak bila survei bukan `dibuka` atau jumlah pilihan di luar batas (`error_code`) |
| `GET student/next-classroom` | siswa | Rombel tahun depan dan susunan mapelnya. Hanya terisi setelah diumumkan, sebelum itu `null` |
| `GET parent/child/{uuid}/elective-survey` | orang tua | Pilihan dan rombel tahun depan anak sendiri |
| `GET student/enrollment-history` | siswa / orang tua (`parent/child/{uuid}/…`) | Riwayat rombel per TA (opsional untuk profil) |

Semua mengikuti CLAUDE.md bagian Mobile API: `ApiResponseTrait`, pesan `__()` tiga bahasa, `status_code` stabil di samping label, dan waktu ISO-8601. Kunci kapabilitas baru di `config/mobile_features.php`: `student.elective_survey`, `parent.elective_survey`.

### 120.5: Urutan PR

| PR | Isi |
|---|---|
| 0 | Jenis akun & jabatan: `account_type`, `positions`, `position_permissions`, `position_assignments`, daftar hak akses, `hasPermission()`, `RoleMiddleware`/`RouteAccess`/`MobileCapabilities` membaca hak akses, migrasi ejaan lama, layar Jabatan & Pejabat. Boleh dipecah 0a (model + middleware) dan 0b (ganti pemeriksaan `->role` + layar). Perbarui CLAUDE.md aturan 3 (ejaan peran dan `StudentDataAccess`) di PR yang sama |
| 1 | `school_years`, FK di `academic_years`/`classrooms`, `class_enrollments`, `EnrollmentService` + backfill dari data seed, test arsitektur penulisan `classroom_id` |
| 1b | Kalender akademik: `academic_calendars`, kolom `milestone` di `academic_events`, pengesahan kepala sekolah, salin dari TA lalu dengan tanda "belum ditinjau", notifikasi revisi |
| 2 | Kurikulum Merdeka: kategori mapel, `classroom_subjects` (susunan mapel per rombel, dipakai jadwal), hapus `major` IPA/IPS dari model, seeder, dan view |
| 3 | Survei peminatan + statistik + perancangan rombel XI + plotting manual (web waka) + API Android 120.4 |
| 4 | Batch kenaikan kelas: draf, tinjau wali, tetapkan (`promotion_decision`), tahan kepala sekolah, eksekusi/batal |
| 5 | Kelulusan terikat batch + pengesahan kepala sekolah, mutasi lewat persetujuan |
| 6 | Aktivasi TA baru (satu aksi, ringkasan, idempoten). Migrasikan pembacaan historis ke `class_enrollments`. Hapus `classrooms.academic_year` (teks) dan `major` |

### 120.6: Test Wajib

- Satu siswa tidak pernah punya dua enrollment `aktif`, termasuk saat dua proses berjalan bersamaan.
- Eksekusi kenaikan dijalankan dua kali tidak menggandakan data (idempoten). Pembatalan sebelum aktivasi mengembalikan keadaan semula.
- Tinggal kelas, lulus, mutasi keluar di tengah semester, dan pindah rombel: riwayat TA lalu tetap benar dan rapor lama tetap menunjuk rombel lamanya.
- Peran: wali kelas tidak bisa mengubah kelas lain. Siswa tidak bisa mengisi survei di luar status `dibuka` atau mengubah pilihan siswa lain. Orang tua hanya melihat anaknya. Waka menetapkan. Kepala sekolah bisa menahan. Penyusun draf tidak menyetujui sendiri bila alurnya mensyaratkan.
- **Statistik:** jumlah per mapel dan per kombinasi sama persis dengan isi `elective_choices`. Siswa yang belum mengisi dihitung terpisah, tidak dianggap memilih apa pun.
- **Plotting:** sistem tidak pernah membuat `class_placements` tanpa aksi Waka Kurikulum. Satu siswa hanya punya satu penempatan per TA target. Kecocokan dihitung benar, termasuk untuk siswa yang belum mengisi (`null`, bukan 0%).
- **Eksekusi kenaikan** ditolak bila ada siswa X yang naik tetapi belum diplot.
- Ambang kenaikan kosong: tidak ada label otomatis (`null`, bukan "tinggal").
- **Jabatan & hak akses:**
  - satu orang dengan Guru + Waka Kurikulum + wali kelas mendapat gabungan haknya;
  - jabatan yang periodenya lewat tidak memberi akses lagi (Plt berakhir otomatis);
  - Admin tidak bisa memberi hak sensitif (hanya `superadmin`);
  - `kepala_sekolah` tidak bisa dihapus atau kehilangan `pimpinan.mengesahkan`;
  - jabatan yang dipakai di langkah persetujuan tidak bisa dinonaktifkan;
  - penyusun ≠ penyetuju walau memegang dua jabatan;
  - test arsitektur menolak pemeriksaan `->role` baru di `app/` dan `resources/views/`.
- **Tanggal dari kalender:** test arsitektur menolak tanggal atau bulan kegiatan yang ditanam di kode alur akademik (misalnya `month === 7`). Kegiatan kunci yang kosong menghasilkan `null` dan pesan "belum ditetapkan", bukan tanggal bawaan. Satu kode `milestone` per semester per kalender.

### 120.7: Kriteria Selesai

- [ ] Semua PR 120.5 di-merge. `pint`, `phpstan`, `php artisan test`, dan `migrate:fresh --seed` hijau.
- [ ] Simulasi penuh di seeder demo: TA 2026/2027 → kalender akademik 2026/2027 disahkan → survei peminatan → statistik → rancang & plot rombel XI → kenaikan → kalender 2027/2028 → TA 2027/2028 aktif. Rombel, riwayat, dan alumni benar.
- [ ] Tidak ada lagi IPA/IPS atau `Kejuruan` di kode, seeder, dan view.
- [ ] `docs/ALUR_KERJA.md` A0–A6 menjadi ✅. Bagian ini diperbarui menjadi `[SELESAI]` beserta penyimpangan dari usulan 120.2 dan alasannya.

---

## FASE 121: ANTI-MACET & ALUR HARIAN YANG HILANG `[GARIS BESAR]`

> Dirinci setelah FASE 120 berjalan. Alurnya sudah tertulis di `docs/ALUR_KERJA.md`, termasuk bagian 1b (Anti-Macet). **Tujuan pemilik produk: workflow jelas dan mengalir, tidak terjadi bottleneck di user.**

**A. Mesin anti-macet di `WorkflowService` (dikerjakan dulu, dipakai semua alur di bawahnya)**
1. **Tenggat per langkah**, berupa durasi atau tanggal dari kegiatan kunci kalender. Ada pengingat otomatis sebelum tenggat dan **eskalasi** ke cadangan setelah lewat. Semuanya tercatat.
2. **Cadangan penyetuju.** Yang dipakai berurutan: Plt aktif, delegasi, lalu atasan yang ditetapkan. Jabatan penyetuju yang kosong tampil sebagai peringatan di layar Admin.
3. **Langkah bertipe "mengetahui"** hanya mengirim notifikasi dan tidak menahan alur. Langkah "menahan" hanya untuk persetujuan wajib.
4. **Persetujuan massal**, dengan tetap bisa membuka satu per satu: rapor per rombel/angkatan, slip gaji per bulan, izin siswa per hari, dan kelulusan satu angkatan.
5. **Langkah dan batas alur diatur Admin**, seperti katalog jabatan:
   - langkah per alur, merujuk jabatan;
   - batas nominal (kas kecil) dan batas hari (cuti panjang) di pengaturan;
   - jenis surat yang boleh ditandatangani Kepala TU.

   Bila batas belum diisi, langkahnya ke Kepala Sekolah.
6. **Dasbor "Alur Tertahan"** untuk pimpinan dan Admin: jumlah dokumen per langkah, umur tertua, dan siapa pemegangnya.
7. **Satu kotak masuk "Menunggu Persetujuan Saya"** di web dan Android, diurutkan menurut tenggat. Mencakup cuti, surat, kenaikan, mutasi, rapor, RKAS, kas kecil, dan izin siswa. Alur yang punya persetujuan sendiri (RKAS, kas kecil) dipindah ke `WorkflowService`.
8. **Pelonggaran beban Kepala Sekolah** sesuai tabel di `ALUR_KERJA.md` 1b, yang sudah disetujui pemilik produk (11 Okt 2026) sebagai **pengaturan bawaan**. Sekolah tetap bisa mengubahnya di pengaturan. Contohnya cuti guru ke Waka Kurikulum, cuti karyawan ke Kepala TU, dan surat rutin ke Kepala TU.

**B. Alur harian yang hilang**
1. **C3 Izin/sakit siswa dari orang tua** (Android) → wali kelas menyetujui (bisa massal) → presensi sesi hari itu otomatis `izin`/`sakit`.
   - Sakit 1 hari tercatat otomatis bila sekolah mengaktifkannya; wali tetap diberi tahu dan bisa membatalkan.
   - Izin yang lewat tenggat dieskalasi ke Waka Kesiswaan.
2. **D5 Rapor lewat e-Rapor resmi** (tidak ada rapor kedua):
   - dasbor kelengkapan nilai untuk wali kelas, dengan pengingat dari kegiatan kunci "batas input nilai";
   - **ekspor nilai akhir, deskripsi capaian, kehadiran, dan catatan wali ke template impor e-Rapor** per rombel/mapel. Formatnya mengikuti template asli dari operator (T1), jangan ditebak;
   - **Tujuan Pembelajaran (TP) per mapel, tingkat, dan semester.** Penilaian harian ditandai dengan TP-nya. Tabel `daily_assessments` saat ini belum punya kaitan TP, dan `report_card_entries.capaian_kompetensi` hanya teks bebas;
   - **draf kalimat capaian kompetensi otomatis** dari nilai per TP (TP terkuat dan terlemah, pola kalimat diatur sekolah), lalu ditinjau guru. Ini mengakhiri kalimat rusak seperti "sangat baik dalam FALSE" yang ada di rapor contoh. TP kosong menghasilkan kalimat kosong untuk diisi guru, bukan karangan;
   - **dua jenis rapor per semester**: tengah semester (ringkas) dan akhir semester (lengkap), keduanya lewat e-Rapor;
   - nilai yang belum ada diekspor **kosong**, bukan 0. Draf capaian **tidak mengulang** klausa bila TP terkuat sama dengan terlemah, dan kata awalnya mengikuti ambang yang diatur sekolah (`ALUR_KERJA.md` D5);
   - **ketidakhadiran rapor dihitung dalam hari** dengan aturan di `ALUR_KERJA.md` D5;
   - **keterangan kenaikan kelas** (semester genap) dari keputusan A4;
   - **nama Kepala Sekolah dan wali kelas menurut tanggal rapor** (pemegang jabatan saat itu);
   - unggah balik PDF rapor resmi massal per rombel, dicocokkan per siswa lewat NISN;
   - Waka Kurikulum menerbitkan massal ke orang tua dan siswa;
   - `ERaporPdfService` dialihkan menjadi pratinjau nilai atau dihapus, menunggu T2.
3. **J1 Surat keluar** memakai definisi `letter_approval` (yang sudah ada di seeder tetapi belum dipakai), dengan pembagian jenis surat Kepala TU vs Kepala Sekolah.
4. **J2 Surat masuk & disposisi** (baru), dengan delegasi disposisi saat kepala sekolah berhalangan.
5. **K4 Pengajuan perubahan data diri** di web dan Android:
   - data identitas memerlukan bukti dan verifikasi TU;
   - data kontak langsung tersimpan;
   - perubahan yang disetujui masuk daftar "Perlu diperbarui di Dapodik" (K1).

## FASE 122: BUKU INDUK SISWA `[GARIS BESAR]`

Identitas, riwayat rombel per TA (FASE 120), mutasi, nilai rapor per semester, prestasi, dan kelulusan. Dicetak per siswa sesuai format dinas. Hanya untuk TU, waka, dan kepala sekolah.

## FASE 123: GO-LIVE, PILOT & SERAH TERIMA KE IT SEKOLAH `[GARIS BESAR]`

> **Konteks (12 Okt 2026):** SISTA dijual ke sekolah sebagai jasa pembuatan, lalu **dikelola IT sekolah**. Pemilik produk tidak merawatnya setelah serah terima. Ukuran selesai fase ini bukan sekadar "live", tetapi **IT sekolah bisa menjalankan, memulihkan, dan mengatur SISTA tanpa pembuatnya**. Menyerap FASE 116 (arsip).

**A. Kepemilikan: semuanya atas nama sekolah sejak awal (bukan dipindah belakangan)**
- **Server dan infrastruktur:** VPS, domain `*.sch.id`, dan sertifikat HTTPS.
- **Akun layanan:** repositori kode (organisasi GitHub sekolah, atau salinan penuh saat serah terima), Firebase dan FCM, Google Play Console, Midtrans, Fonnte/WA, serta Google Drive backup.
- **Kunci dan rahasia:** keystore rilis Android beserta password dan cadangannya (**kehilangan keystore = aplikasi tidak bisa diperbarui di Play Store**), `APP_KEY`, kredensial database, dan kunci API. Semuanya diserahkan lewat pengelola rahasia, tidak lewat chat.
- **Data milik sekolah sebagai pengendali data (UU PDP).** Selama pengembangan dan pilot, pembuat berperan sebagai pemroses data, dengan perjanjian tertulis.

**B. Infrastruktur sederhana yang bisa dirawat IT sekolah**
- Satu VPS dengan Docker Compose (sudah ada `docker-compose.prod.yml`). Isinya `deploy.sh` (down → pull → migrate → up), queue worker, dan cron scheduler. Workflow deploy di CI saat ini hanya *skip* karena belum ada server.
- Backup harian ke Google Drive sekolah (`spatie/laravel-backup`, terenkripsi). **Uji pemulihan dilakukan bersama IT sekolah** sebelum BAST.
- Log error kritis dikirim ke grup Telegram/WA tim IT sekolah. Telescope hanya untuk admin.

**C. Data awal (K1, K5)**
1. Impor file ekspor Dapodik (peserta didik, PTK, rombel). Baris yang gagal dicocokkan dilaporkan.
2. Akun massal dibuat. Lembar akun dibagikan per rombel lewat wali kelas, dan password wajib diganti saat login pertama. Wali kelas bisa me-reset password siswanya.
3. **Kampanye "cek data dirimu"**: siswa dan orang tua memeriksa profilnya sendiri dan mengajukan koreksi (K4).

**D. Dokumentasi serah terima, dalam Bahasa Indonesia, untuk pembaca non-pembuat**
1. **Runbook operasional:**
   - menjalankan, menghentikan, dan memperbarui;
   - backup dan **pemulihan langkah demi langkah**;
   - memperpanjang domain dan sertifikat;
   - memantau error;
   - apa yang dilakukan bila server mati.
2. **Panduan Admin:**
   - membuka tahun ajaran dan kalender akademik;
   - mengatur jabatan dan hak akses (L1–L2);
   - impor data;
   - reset password;
   - langkah dan batas persetujuan (anti-macet);
   - template ekspor e-Rapor dan Dapodik.
3. **Panduan per peran** (satu halaman per peran): guru, wali kelas, TU/bendahara, waka, kepala sekolah, siswa, dan orang tua.
4. **Dokumen teknis** untuk pengembang berikutnya. Kedalamannya mengikuti T8: arsitektur, cara menjalankan lokal, cara rilis Android, dan `docs/ALUR_KERJA.md` sebagai peta fitur.

**E. Pilot, pelatihan, dan BAST**
- **Pilot semester Genap (Jan–Jun 2027):** 1–2 rombel, TU, BK, dan wali kelasnya. Keluhan pilot menjadi backlog.
- **Pelatihan:** Admin/IT sekolah (operasional penuh), TU, wali kelas, guru, dan pimpinan. Untuk siswa dan orang tua, cukup video pendek atau lembar panduan.
- **BAST** (Berita Acara Serah Terima) berisi daftar periksa:
  - semua akun dan kunci sudah atas nama sekolah;
  - uji pemulihan backup berhasil;
  - dokumentasi diserahkan;
  - IT sekolah sudah mencoba sendiri: deploy pembaruan, pulihkan backup, buat jabatan baru, buka TA baru.

  Masa garansi dan dukungan mengikuti T9.
- **Go-live penuh Juli 2027** bersamaan dengan TA 2027/2028.
