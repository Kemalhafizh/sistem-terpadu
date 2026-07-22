# 🚀 Rencana Pengembangan Localhost — Sistem Terpadu SMA Islam Sultan Agung 1 Semarang

> **Konteks Proyek:** Platform **Sistem Terpadu (SISTA)** ini dikembangkan **khusus dan eksklusif untuk SMA Islam Sultan Agung 1 Semarang** — sekolah Islam di bawah naungan **Yayasan Badan Wakaf Sultan Agung (YBWSA)** Semarang. Seluruh fitur, terminologi, alur kerja, dan integrasi keislaman dirancang spesifik untuk kebutuhan operasional SMA ISA 1.

> **Fokus Dokumen:** Seluruh pengembangan di bawah ini dikerjakan **full di localhost** — tanpa deployment, tanpa mobile app, tanpa UAT dengan user nyata. Tujuannya: **menambah fitur baru yang bermakna, memperbaiki fitur yang sudah ada, memperluas data demo, dan memoles UI/UX** agar platform benar-benar lengkap dan siap dipamerkan sebagai portofolio.

---

## 📊 Statistik Codebase Saat Ini (Baseline — Updated Juli 2026)

| Metrik | Jumlah |
| :--- | :---: |
| **Model Eloquent** | 119 |
| **Controller** | 72 (root) + 5 subdirectories = **~77** |
| **Service Class** | 37 + 2 (Integration/) = **39** |
| **Migration** | 99 |
| **View Directory** | 61 |
| **Blade Templates** | ~350+ |
| **Test Files** | ~90+ |
| **Seeder Files** | 30 |
| **Factory Files** | 29 |
| **Policies** | 23 |
| **API Resources** | 13 |
| **Middleware** | 9 |
| **Events (Broadcast)** | 5 |
| **Bahasa (i18n)** | 3 (ID, EN, AR) — masing-masing 6 file |
| **Dokumentasi** | 27 file di `docs/` |
| **Registered Routes** | ~350+ |
| **Demo Records (Seeder)** | ~3000+ records across 30+ tables |
| **User Roles** | 12 (superadmin, admin, kepala_sekolah, guru, bk, waka_kurikulum, waka_kesiswaan, waka_humas, waka_sarpras, staf_tu, student, parent) |

---

## 🗺️ Peta Jalan Pengembangan (Semua di Localhost)

### Filosofi

Platform ini sudah sangat besar (~100 controller, 107 model, 300+ routes). Pengembangan selanjutnya berfokus pada:

1. **🆕 Fitur Baru yang Bermakna** → Menambah modul-modul baru yang benar-benar dibutuhkan SMA ISA 1 tapi belum ada.
2. **🔧 Perbaikan & Penguatan Fitur Existing** → Menambah depth pada fitur yang sudah ada tapi masih dangkal.
3. **🎨 UI/UX Polish & Konsistensi** → Memastikan seluruh halaman konsisten, responsif, dan cantik.
4. **📦 Data Demo Realistis** → Memperluas seeder agar demo terasa seperti sekolah nyata (bukan data placeholder).
5. **🧪 Testing & Kualitas Kode** → Menambah test coverage dan memperbaiki kode yang kurang solid.

---

## 📦 FASE 11: Fitur Baru — Modul Keislaman & Akademik Lanjutan

**Fokus:** Menambah modul-modul baru yang sangat relevan untuk SMA Islam Sultan Agung 1 — terutama yang berkaitan dengan identitas keislaman sekolah d### 🕌 Modul 11.1: Sistem Tahfidz Al-Qur'an Komprehensif (Selesai ✅)

**Status Saat Ini:** Sudah ada model `TahfidzLog`, controller `TahfidzController`, view `tahfidz/show.blade.php`, dan seeder dasar. Namun, fiturnya masih sangat dasar (hanya input & daftar setoran hafalan).

#### User Review Required

> [!IMPORTANT]
> **Otomatisasi Status Capaian Hafalan (on_track/behind/exceeded):**
> Kami mengusulkan status pencapaian target hafalan siswa diperbarui secara otomatis setiap kali guru menyimpan log setoran `ziyadah` baru:
> - **exceeded**: Jika `achieved_juz` > `target_juz`
> - **completed**: Jika `achieved_juz` == `target_juz` dan `achieved_pages` >= `target_pages`
> - **on_track**: Jika pencapaian proporsional terhadap waktu berjalannya semester (misal: target 5 juz, saat ini minggu ke-8 dari 16 minggu, siswa sudah mencapai 2.5 juz atau lebih).
> - **behind**: Jika pencapaian di bawah batas proporsional waktu semester.
> Apakah Anda menyetujui model kalkulasi status otomatis ini atau lebih menyukai status yang sepenuhnya ditentukan manual oleh guru?

#### Open Questions

> [!NOTE]
> **Penentuan Juz untuk Setiap Surah/Ayat:**
> Al-Qur'an terdiri dari 30 Juz dengan pembagian surah/ayat yang spesifik. Untuk menghitung `achieved_juz` secara dinamis, apakah sistem perlu menyimpan tabel pemetaan ayat Al-Qur'an ke Juz (Juz 1-30) untuk menghitung capaian juz secara presisi, atau cukup dengan memasukkan input field `juz` secara eksplisit saat guru mencatat setoran hafalan?
> **Usulan Kami:** Kami mengusulkan agar pada model `TahfidzLog` ditambahkan kolom `juz` (1-30) dan `pages_count` (jumlah halaman yang disetor) sebagai input opsional saat guru mencatat setoran baru. Dengan cara ini, guru dapat langsung menentukan juz & jumlah halaman yang disetor tanpa perlu sistem melakukan parsing teks ayat yang rumit. Apakah ini disetujui?

#### Rencana Implementasi

##### [NEW] Migration: `create_tahfidz_targets_table`
```php
Schema::create('tahfidz_targets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
    $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete(); // Represents semester
    $table->integer('target_juz');                          // Target hafalan juz per semester (misal: 2)
    $table->integer('target_pages')->default(0);            // Target halaman tambahan
    $table->integer('achieved_juz')->default(0);            // Pencapaian juz riil
    $table->integer('achieved_pages')->default(0);          // Pencapaian halaman riil
    $table->enum('status', ['on_track', 'behind', 'exceeded', 'completed'])->default('on_track');
    $table->text('notes')->nullable();                      // Catatan guru tahfidz
    $table->timestamps();

    $table->unique(['student_id', 'academic_year_id']);
});
```

##### [MODIFY] `app/Http/Controllers/TahfidzController.php`
```php
// Method baru:
public function dashboard(Request $r);          // Dashboard guru tahfidz: melihat progress per kelas, target vs aktual
public function bulkInputForm(Request $r);      // Form input setoran massal per kelas
public function storeBulk(Request $r);          // Simpan setoran massal per kelas
public function targetSettingForm($classroom); // Halaman set target semester siswa di kelas
public function storeTargets(Request $r);       // Simpan target semester massal
public function leaderboard();                 // Ranking/leaderboard siswa tahfidz teraktif & hafalan terbanyak
public function exportPdf($classroom);          // Export PDF laporan progress kelas
```

##### [NEW] `app/Services/TahfidzService.php`
```php
class TahfidzService
{
    public function getStudentProgress(Student $student, int $academicYearId): ?TahfidzTarget;
    public function submitSetoran(Student $student, array $data): TahfidzLog;
    public function updateTargetAchievement(int $studentId, int $academicYearId): void;
    public function getClassProgressSummary(int $classroomId, int $academicYearId): Collection;
    public function getLeaderboard(int $limit = 10): Collection;
}
```

##### [NEW] Views di `resources/views/tahfidz/`
* `dashboard.blade.php`: Ringkasan pencapaian kelas, widget persentase target, ringkasan status target (on_track/behind/exceeded).
* `bulk-input.blade.php`: Grid input tabel berisi nama siswa, input nama surah, ayat, tipe (ziyadah/murajaah), nilai (grade), juz, pages, dan catatan.
* `target-setting.blade.php`: Formulir input target juz & halaman untuk seluruh siswa kelas tahfidz.
* `leaderboard.blade.php`: Papan peringkat tahfidz siswa se-sekolah.
* `report.blade.php`: Halaman laporan per kelas (preview sebelum PDF).
* `pdf_export.blade.php`: Kerangka cetak PDF laporan progress.

##### [MODIFY] `resources/views/tahfidz/show.blade.php`
* Menambahkan visualisasi progress bar pencapaian target semester (Target vs Aktual) dan widget informasi status pencapaian.

##### Integrasi Gamifikasi (XP Awards)
* Setoran Ziyadah Baru: `Grade A` (+15 XP), `Grade B` (+10 XP), `Grade C` (+5 XP).
* Setoran Muraja'ah: `Grade A` (+8 XP), `Grade B` (+5 XP).
* Pencapaian Target Semester (exceeded/completed): Bonus +50 XP.

##### Demo Data (Seeder)
* Menambahkan target tahfidz semester aktif dan log setoran 30 hari untuk 10 siswa di kelas tahfidz, disimulasikan 4 siswa on_track, 2 siswa behind, 2 siswa exceeded, dan 2 siswa completed.

#### Verification Plan

##### Automated Tests
* Membuat file unit/feature test `tests/Feature/Islamic/TahfidzTest.php` untuk memvalidasi:
  * Guru dapat menetapkan target semester.
  * Guru dapat menginput setoran ziyadah/muraja'ah secara massal.
  * System menghitung achieved_juz/achieved_pages secara otomatis dan memperbarui status target.
  * XP gamifikasi berhasil diberikan secara proporsional.
  * Siswa & orang tua dapat mengakses data progress tahfidz.
  * PDF report dapat diunduh.

##### Manual Verification
* Akses menu Tahfidz sebagai Guru, verifikasi form bulk-input dan target setting.
* Akses sebagai Siswa, verifikasi progress bar di halaman show.
* Ekspor laporan kelas ke PDF dan periksa tata letaknya.

---

### 🌙 Modul 11.2: Sistem Mutaba'ah Ibadah Lengkap (Selesai ✅)

**Status Saat Ini:** Implementasi sistem pencatatan ibadah harian terperinci (5 sholat fardhu, sholat sunnah dhuha & tahajud, tadarus al-qur'an, puasa sunnah, sedekah, dzikir pagi & sore, catatan harian) lengkap dengan perhitungan skor otomatis (0-100), rekapitulasi mingguan berpredikat (Grade A-D), notifikasi/pembinaan wali kelas, portal orang tua untuk verifikasi dan tanda tangan laporan, statistik interaktif Chart.js, serta ekspor rekapitulasi PDF kelas.

#### User Review Required

> [!IMPORTANT]
> **Arsitektur Mutaba'ah Logs:**
> Kami merencanakan untuk menambahkan kolom ibadah harian terperinci ke tabel `mutabaah_logs` agar satu baris merepresentasikan satu hari penuh aktivitas siswa. Untuk menghindari kerusakan/eror pada endpoint API mobile (`MobileDataController`) dan tes bawaan yang merujuk pada `mutabaah_item_id`, kami akan mengatur kolom `mutabaah_item_id` menjadi **nullable**. Data harian baru akan di-save dengan `mutabaah_item_id = null`, sementara sistem lama tetap kompatibel.

#### Open Questions

> [!NOTE]
> **Formula Perhitungan Skor Mutaba'ah Yaumiyah (0-100):**
> Kami mengusulkan rumus perhitungan skor harian sebagai berikut:
> - **Shalat 5 Waktu Wajib (Subuh, Dzuhur, Ashar, Maghrib, Isya):** 0 = Tidak shalat (0 poin), 1 = Sendiri/Munfarid (10 poin), 2 = Berjamaah (15 poin). Maksimal = 75 poin.
> - **Shalat Sunnah (Dhuha & Tahajud):** 0 = Tidak (0 poin), 1 = Ya (5 poin). Maksimal = 10 poin.
> - **Tadarus Al-Quran:** 0 = Tidak (0 poin), &ge; 1 halaman (10 poin). Maksimal = 10 poin.
> - **Puasa Sunnah & Sedekah Harian:** Masing-masing bernilai 5 poin jika dikerjakan. Maksimal = 10 poin.
> - **Dzikir Pagi & Sore:** Masing-masing bernilai 2.5 poin jika dikerjakan. Maksimal = 5 poin.
> - **Total Poin Maksimal:** 110 poin (dibatasi/di-cap maksimal **100 poin**).
> Apakah formula pembobotan skor ibadah harian di atas sudah sesuai untuk kebutuhan kurikulum keislaman SMA Islam Sultan Agung 1 Semarang?

**Apa yang Perlu Ditambah:**

#### Mengapa Penting untuk SMA ISA 1?
Mutaba'ah ibadah adalah **program harian wajib** di SMA ISA 1. Setiap siswa harus melaporkan ibadah harian: sholat 5 waktu, sholat dhuha, tadarus, puasa sunnah, sedekah, dll. Saat ini tracking-nya terlalu sederhana. Dibutuhkan: laporan mingguan/bulanan, statistik kelas, ranking istiqomah, dan notifikasi ke orang tua.

#### Rencana Implementasi

##### [NEW] Migration: `add_mutabaah_enhancements`
```php
// Tambah kolom ke mutabaah_logs:
$table->tinyInteger('sholat_subuh')->default(0);       // 0=tidak, 1=ya, 2=berjamaah
$table->tinyInteger('sholat_dzuhur')->default(0);
$table->tinyInteger('sholat_ashar')->default(0);
$table->tinyInteger('sholat_maghrib')->default(0);
$table->tinyInteger('sholat_isya')->default(0);
$table->tinyInteger('sholat_dhuha')->default(0);
$table->tinyInteger('sholat_tahajud')->default(0);
$table->tinyInteger('tadarus_pages')->default(0);      // Jumlah halaman Al-Qur'an yang dibaca
$table->boolean('puasa_sunnah')->default(false);
$table->boolean('sedekah')->default(false);
$table->boolean('dzikir_pagi')->default(false);
$table->boolean('dzikir_sore')->default(false);
$table->text('catatan_harian')->nullable();             // Refleksi hari ini
$table->integer('score')->default(0);                   // Auto-calculated score (0-100)

// Tabel baru untuk rangkuman mingguan
Schema::create('mutabaah_weekly_summaries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('students');
    $table->date('week_start');
    $table->date('week_end');
    $table->integer('total_score');
    $table->integer('sholat_5_waktu_count');             // Berapa kali sholat 5 waktu lengkap
    $table->integer('sholat_berjamaah_count');
    $table->integer('tadarus_total_pages');
    $table->enum('grade', ['A', 'B', 'C', 'D'])->nullable(); // A=istimewa, D=perlu pembinaan
    $table->text('wali_kelas_comment')->nullable();
    $table->boolean('parent_acknowledged')->default(false); // Ortu sudah melihat/konfirmasi
    $table->timestamps();

    $table->unique(['student_id', 'week_start']);
});
```

##### [MODIFY] `app/Http/Controllers/MutabaahController.php`
```php
// Tambahkan method baru:
public function inputHarian()               // Form input mutaba'ah harian (lebih detail dari toggle)
public function rekapMingguan()             // Rekap mingguan per siswa
public function rekapKelas($classroomId)    // Rekap mutaba'ah per kelas (untuk wali kelas)
public function statistik()                 // Statistik: rata-rata kelas, tren, distribusi grade
public function exportPdf($classroomId)     // Export rekap mutaba'ah kelas ke PDF
public function parentView($studentId)      // View khusus orang tua: rekap anak + acknowledge
```

##### [NEW] `resources/views/mutabaah/`
```
resources/views/mutabaah/
├── index.blade.php                 # [EXISTING] → Revamp: calendar view + daily input
├── input-harian.blade.php          # [NEW] Form detail: sholat 5 waktu (checkboxes), dhuha, tadarus, dll
├── rekap-mingguan.blade.php        # [NEW] Tabel rekap per minggu + grade (A/B/C/D) + chart
├── rekap-kelas.blade.php           # [NEW] Wali kelas: ranking siswa, rata-rata kelas, alert siswa grade D
├── statistik.blade.php             # [NEW] Statistik: chart tren score, distribusi grade, top performers
└── parent-view.blade.php           # [NEW] Portal ortu: lihat rekap anak + tombol "Sudah Dilihat"
```

##### Integrasi Gamifikasi
```php
// Trigger XP di GamificationService:
// - Sholat 5 waktu lengkap 1 hari → +5 XP
// - Sholat berjamaah (nilai 2) → +2 XP per sholat
// - Sholat dhuha → +3 XP
// - Tadarus ≥ 2 halaman → +5 XP
// - Puasa sunnah → +10 XP
// - Mutaba'ah score 100 (sempurna) → +20 XP
// - Streak 7 hari berturut-turut score ≥ 80 → Achievement "Istiqomah Mingguan" (+30 XP)
// - Streak 30 hari → Achievement "Istiqomah Bulanan" (+120 XP, Epic)
```

---

### 📝 Modul 11.3: Sistem Penilaian Harian & Remedial (Selesai ✅)

**Status Saat Ini:** Ada model `Grade` dan `GradeController` untuk input/view nilai. Tapi hanya mendukung nilai akhir semester (Tugas, UTS, UAS). Belum ada penilaian harian (ulangan harian, kuis), dan belum ada sistem remedial.

**Apa yang Perlu Ditambah:**

#### Mengapa Penting untuk SMA ISA 1?
Kurikulum Merdeka mengharuskan **penilaian formatif berkelanjutan** — bukan hanya sumatif (UTS/UAS). Guru SMA ISA 1 perlu input nilai ulangan harian, kuis, praktikum, project, dan presentasi. Siswa yang di bawah KKM harus menjalani remedial, dan sistem harus tracking ini otomatis.

#### Rencana Implementasi

##### [NEW] Migration: `create_daily_assessments_table` (Selesai ✅)
```php
Schema::create('daily_assessments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('subject_id')->constrained('subjects');
    $table->foreignId('classroom_id')->constrained('classrooms');
    $table->foreignId('teacher_id')->constrained('users');
    $table->foreignId('academic_year_id')->constrained('academic_years');
    $table->string('title');                                // "Ulangan Harian BAB 3", "Kuis Trigonometri"
    $table->enum('type', ['ulangan_harian', 'kuis', 'praktikum', 'project', 'presentasi', 'tugas']);
    $table->date('assessment_date');
    $table->decimal('max_score', 5, 2)->default(100);
    $table->decimal('kkm', 5, 2)->nullable();               // Override KKM per assessment
    $table->text('description')->nullable();
    $table->timestamps();
});

Schema::create('daily_assessment_scores', function (Blueprint $table) {
    $table->id();
    $table->foreignId('daily_assessment_id')->constrained('daily_assessments');
    $table->foreignId('student_id')->constrained('students');
    $table->decimal('score', 5, 2)->nullable();
    $table->boolean('needs_remedial')->default(false);       // Auto-set jika score < KKM
    $table->decimal('remedial_score', 5, 2)->nullable();     // Nilai setelah remedial
    $table->enum('remedial_status', ['not_needed', 'pending', 'completed'])->default('not_needed');
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->unique(['daily_assessment_id', 'student_id']);
});
```

##### [NEW] `app/Http/Controllers/DailyAssessmentController.php` (Selesai ✅)
```php
class DailyAssessmentController extends Controller
{
    public function index()                    // List semua penilaian harian guru (per mapel per kelas)
    public function create()                   // Form buat penilaian baru (pilih kelas, mapel, tipe, tanggal)
    public function store(Request $request)     // Simpan penilaian + input nilai per siswa (batch)
    public function show($id)                  // Detail: list siswa + score + status remedial
    public function inputScores($id)           // Form input nilai per siswa (DataTable editable)
    public function storeScores(Request $r)    // Simpan nilai batch + auto-detect remedial
    public function remedialList()             // List semua siswa yang butuh remedial (cross-mapel)
    public function inputRemedialScore($id)    // Form input nilai remedial
    public function storeRemedialScore(Request $r)  // Simpan nilai remedial
    public function analytics($classroomId)    // Analisis: distribusi nilai, rata-rata, trend
    public function exportPdf($id)             // Export daftar nilai per assessment ke PDF
}
```

##### [NEW] `resources/views/daily-assessments/` (Selesai ✅)
```
resources/views/daily-assessments/
├── index.blade.php                 # DataTable: list penilaian, filter per mapel/kelas/tipe
├── create.blade.php                # Form wizard: pilih kelas → mapel → tipe → tanggal → simpan
├── show.blade.php                  # Detail: tabel siswa + score + status remedial + chart distribusi
├── input-scores.blade.php          # Input nilai massal: DataTable editable, auto-highlight < KKM (merah)
├── remedial-list.blade.php         # Cross-mapel: semua siswa yang perlu remedial + status
├── input-remedial.blade.php        # Input nilai remedial
└── analytics.blade.php             # Chart: distribusi nilai (histogram), rata-rata per assessment, tren
```

##### [NEW] `app/Services/DailyAssessmentService.php` (Selesai ✅)
```php
class DailyAssessmentService
{
    public function createAssessment(array $data): DailyAssessment;
    public function batchStoreScores(int $assessmentId, array $scores): void;
    public function autoDetectRemedial(int $assessmentId): int;      // Return jumlah siswa perlu remedial
    public function getClassAnalytics(int $classroomId, int $subjectId): array;
    public function getStudentTranscript(int $studentId): Collection; // Semua nilai harian siswa per mapel
    public function calculateFinalGrade(int $studentId, int $subjectId): float; // Hitung rata-rata akhir dari penilaian harian + UTS + UAS
}
```

---

### 📊 Modul 11.4: Analisis Butir Soal (Item Analysis) untuk CBT (Selesai ✅)

**Status Saat Ini:** Selesai diimplementasikan secara komprehensif. Menambahkan perhitungan tingkat kesukaran, daya pembeda kelompok atas/bawah 27%, efektivitas distractor pengecoh, nilai reliabilitas KR-20, ekspor PDF landscape, serta visualisasi analitik interaktif.

**Apa yang Perlu Ditambah:**

#### Mengapa Penting untuk SMA ISA 1?
Guru SMA ISA 1 perlu tahu apakah soal yang mereka buat berkualitas. Analisis butir soal (item analysis) memberikan data:
- **Tingkat Kesukaran (P)**: Soal terlalu mudah? Terlalu sulit?
- **Daya Pembeda (D)**: Apakah soal bisa membedakan siswa pandai dan kurang?
- **Efektifitas Pengecoh**: Apakah opsi jawaban salah (distraktor) berfungsi?
- **Validitas & Reliabilitas**: Apakah ujian secara keseluruhan valid?

Ini standar kebutuhan guru profesional dan akan membuat portofolio sangat kuat.

#### Rencana Implementasi

##### [NEW] `app/Services/ItemAnalysisService.php`
```php
class ItemAnalysisService
{
    /**
     * Hitung tingkat kesukaran (P) per soal.
     * P = jumlah yang benar / jumlah peserta.
     * P < 0.3 = sulit, P 0.3-0.7 = sedang, P > 0.7 = mudah.
     */
    public function calculateDifficultyIndex(int $examId): Collection;

    /**
     * Hitung daya pembeda (D) per soal.
     * D = (Ba/Ja) - (Bb/Jb) — kelompok atas vs bawah (27%).
     * D < 0.2 = jelek, D 0.2-0.4 = cukup, D > 0.4 = baik.
     */
    public function calculateDiscriminationIndex(int $examId): Collection;

    /**
     * Analisis efektifitas pengecoh per soal.
     * Setiap opsi salah harus dipilih oleh minimal 5% peserta.
     */
    public function analyzeDistractors(int $examId): Collection;

    /**
     * Hitung reliabilitas ujian menggunakan KR-20 (Kuder-Richardson).
     */
    public function calculateReliability(int $examId): float;

    /**
     * Rekomendasi otomatis: soal mana yang harus direvisi/dibuang/dipertahankan.
     */
    public function generateRecommendations(int $examId): Collection;

    /**
     * Rangkuman lengkap analisis per ujian.
     */
    public function getFullAnalysis(int $examId): array;
}
```

##### [MODIFY] `app/Http/Controllers/ExamController.php`
```php
// Tambahkan method baru:
public function itemAnalysis($exam)           // Halaman analisis butir soal per ujian
public function exportAnalysisPdf($exam)      // Export analisis ke PDF
```

##### [NEW] `resources/views/cbt/item-analysis.blade.php`
```
Halaman analisis butir soal:
├── Ringkasan Ujian
│   ├── Rata-rata nilai, median, standar deviasi
│   ├── Reliabilitas (KR-20): 0.xx — interpretasi (rendah/sedang/tinggi)
│   └── Jumlah soal: total, mudah, sedang, sulit
├── Tabel Analisis Per Soal
│   ├── No. Soal | Pertanyaan | P (Kesukaran) | D (Daya Pembeda) | Rekomendasi
│   ├── Warna baris: hijau (baik), kuning (revisi), merah (buang)
│   └── Expand per soal: distribusi jawaban (bar chart: A, B, C, D, E)
└── Chart Visualisasi
    ├── Scatter plot: P vs D (quadrant analysis)
    ├── Histogram distribusi nilai siswa
    └── Box plot per kelas
```

---

### 🎒 Modul 11.5: Presensi Kegiatan & Acara Sekolah (Selesai ✅)

**Status Saat Ini:** Selesai diimplementasikan secara komprehensif. Menambahkan sistem presensi kegiatan non-KBM (Upacara, Sholat Jamaah, PHBI, OSIS, dll) lengkap dengan dynamic targeting, batch input, charts, rekap dashboard, dan export PDF.

**Apa yang Perlu Ditambah:**

#### Mengapa Penting untuk SMA ISA 1?
SMA ISA 1 memiliki banyak kegiatan di luar KBM yang butuh presensi terpisah:
- **Upacara bendera** (Senin)
- **Sholat Dhuha berjamaah**
- **Sholat Dzuhur berjamaah**
- **Kultum / Ceramah Agama**
- **Kegiatan PHBI** (Peringatan Hari Besar Islam): Maulid, Isra Miraj, dll.
- **Rapat guru**
- **Kegiatan OSIS & MPK**
- **Class meeting**, dll.

#### Rencana Implementasi

##### [NEW] Migration: `create_event_attendances_table`
```php
Schema::create('event_attendance_events', function (Blueprint $table) {
    $table->id();
    $table->string('name');                                 // "Upacara Bendera 2 Juni 2026"
    $table->enum('type', [
        'upacara', 'sholat_dhuha', 'sholat_dzuhur', 'kultum',
        'phbi', 'rapat_guru', 'osis', 'class_meeting', 'lainnya'
    ]);
    $table->date('event_date');
    $table->time('start_time')->nullable();
    $table->time('end_time')->nullable();
    $table->enum('target', ['all_students', 'all_teachers', 'specific_class', 'specific_group']);
    $table->json('target_ids')->nullable();                 // classroom_ids atau user_ids
    $table->foreignId('created_by')->constrained('users');
    $table->text('notes')->nullable();
    $table->timestamps();
});

Schema::create('event_attendance_records', function (Blueprint $table) {
    $table->id();
    $table->foreignId('event_id')->constrained('event_attendance_events')->onDelete('cascade');
    $table->foreignId('user_id')->constrained('users');
    $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa', 'terlambat'])->default('alpa');
    $table->time('check_in_time')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->unique(['event_id', 'user_id']);
});
```

##### [NEW] `app/Http/Controllers/EventAttendanceController.php`
```php
class EventAttendanceController extends Controller
{
    public function index()                     // List semua kegiatan + rekapitulasi
    public function create()                    // Form buat kegiatan baru
    public function store(Request $request)      // Simpan kegiatan
    public function inputAttendance($event)      // Form input presensi per kegiatan (DataTable checkbox)
    public function storeAttendance(Request $r)  // Simpan presensi batch
    public function show($event)                // Detail kegiatan + rekap hadir/izin/alpa
    public function rekap()                     // Rekap kehadiran kegiatan per siswa (cross-event)
    public function exportPdf($event)           // Export rekap presensi kegiatan ke PDF
}
```

##### [NEW] `resources/views/event-attendance/`
```
resources/views/event-attendance/
├── index.blade.php                 # DataTable: daftar kegiatan, filter per tipe/bulan
├── create.blade.php                # Form: nama, tipe, tanggal, target (semua/kelas tertentu)
├── input.blade.php                 # Input presensi: DataTable checkbox (hadir/izin/sakit/alpa)
├── show.blade.php                  # Detail: ringkasan kehadiran, chart pie, list siswa
├── rekap.blade.php                 # Rekap lintas kegiatan: berapa kali hadir upacara, sholat dhuha, dll
```

---

## 📦 FASE 12: Penguatan Fitur Existing & Kelengkapan Data

**Fokus:** Memperkuat fitur-fitur yang sudah ada tapi masih dangkal, menambah view/flow yang belum lengkap, dan menambah depth pada modul yang sudah terbangun.

---

### 🔧 Modul 12.1: Penguatan Dashboard per Role (Selesai ✅)

**Status Saat Ini:** Dashboard sudah ada (68KB `dashboard.blade.php`!) dengan widget drag-drop. Tapi data yang ditampilkan per role masih basic.

**Apa yang Perlu Ditambah per Role:**

#### Dashboard Kepala Sekolah & Admin
```
[EXISTING ✅] Total siswa, guru, kelas, mapel
[EXISTING ✅] SPMB stats, payment trend, recent payments

[BARU - PERLU DITAMBAH]
├── 📊 Widget: Tingkat Kehadiran Hari Ini (%)
│   └── Gauge chart: "93% siswa hadir hari ini" — merah jika < 85%
├── 💰 Widget: Status Keuangan Bulan Ini
│   └── Card: Total tagihan, sudah dibayar, belum dibayar, % collection rate
├── 📝 Widget: Jurnal Mengajar Hari Ini
│   └── Progress bar: "45/60 guru sudah mengisi jurnal" + list yang belum
├── 🎓 Widget: Rata-rata Nilai Semester
│   └── Bar chart per mapel: rata-rata kelas X, XI, XII
├── 🔔 Widget: Smart Alerts Terbaru
│   └── List 5 alert terbaru yang perlu perhatian
├── 📋 Widget: Approval Pending
│   └── Count workflow requests yang menunggu approval kepsek
├── 🏆 Widget: Top 5 Siswa XP (Gamifikasi)
│   └── Mini leaderboard
└── 🕌 Widget: Rekap Mutaba'ah Minggu Ini
    └── Rata-rata score kelas, persentase yang grade A
```

#### Dashboard Guru
```
[EXISTING ✅] Info wali kelas, my classrooms

[BARU - PERLU DITAMBAH]
├── 📅 Widget: Jadwal Hari Ini
│   └── Timeline jadwal mengajar hari ini (jam, mapel, kelas, ruangan)
├── ✅ Widget: Absensi yang Belum Diinput
│   └── Alert: "Kelas XI-IPA-2 hari ini belum diabsensi" → link langsung
├── 📝 Widget: Jurnal Hari Ini
│   └── Status: sudah diisi / belum. Link ke form input jurnal
├── 📊 Widget: Tugas Belum Dinilai
│   └── Count submission e-learning yang pending grading
├── 📖 Widget: Progress Tahfidz (jika guru tahfidz)
│   └── Mini chart progress kelas tahfidz
└── 🔔 Widget: Notifikasi Terbaru
    └── 5 notifikasi terbaru (tiket, approval, dll)
```

#### Dashboard Siswa
```
[EXISTING ✅] Total poin, recent point logs, unpaid billings

[BARU - PERLU DITAMBAH]
├── 📅 Widget: Jadwal Hari Ini
│   └── List pelajaran hari ini (jam, mapel, guru, ruangan)
├── 📝 Widget: Tugas Pending
│   └── List tugas yang deadline-nya mendekat + countdown
├── 🏆 Widget: Gamifikasi Quick View
│   └── XP bar, level, streak 🔥, achievement terbaru
├── 📊 Widget: Nilai Terbaru
│   └── 5 nilai terakhir (penilaian harian + UTS/UAS)
├── 🕌 Widget: Mutaba'ah Hari Ini
│   └── Quick toggle sholat + tadarus (tanpa pindah halaman)
├── 📚 Widget: Buku yang Dipinjam
│   └── List buku + tanggal harus kembali + alert jika lewat
└── 💬 Widget: Thread Forum Terbaru
    └── 3 thread terbaru di forum kelas/mapel
```

#### Dashboard Orang Tua
```
[EXISTING ✅] Children data, points, billing info

[BARU - PERLU DITAMBAH]
├── 📋 Widget: Kehadiran Anak Hari Ini
│   └── Status per anak: "Hadir ✅" atau "Alpa ⚠️" — real-time feel
├── 📝 Widget: Nilai Terbaru Anak
│   └── 5 nilai terakhir per anak
├── 🕌 Widget: Mutaba'ah Anak Minggu Ini
│   └── Ringkasan grade mutaba'ah + tombol "Sudah Dilihat"
├── 📅 Widget: Agenda Sekolah Mendatang
│   └── 3 event terdekat dari kalender akademik
├── 📊 Widget: Rapor Progress
│   └── Mini chart: tren nilai anak semester ini vs semester lalu
└── 💬 Widget: Pesan dari Wali Kelas
    └── Pesan terbaru + notif belum dibaca
```

##### Implementasi
```php
// [MODIFY] app/Http/Controllers/DashboardController.php
// - getAdminStats() → tambah data baru (kehadiran hari ini, jurnal, collection rate, smart alerts)
// - getTeacherData() → tambah jadwal hari ini, absensi pending, tugas belum dinilai
// - getStudentData() → tambah jadwal hari ini, tugas pending, gamifikasi, mutaba'ah
// - getParentData() → tambah kehadiran hari ini, nilai terbaru, mutaba'ah

// [MODIFY] resources/views/dashboard.blade.php
// - Tambah widget baru per role (pakai sistem drag-drop yang sudah ada)
// - Pastikan widget baru responsive dan memiliki animasi skeleton loading
```

---

### 🔧 Modul 12.2: Penguatan Jadwal Pelajaran (Selesai ✅)

**Status Saat Ini:** Selesai diimplementasikan secara komprehensif. Menambahkan validasi bentrok tiga arah (Guru, Kelas, Ruangan), visualisasi grid per guru & per ruangan, dasbor audit bentrok, serta cetak PDF landscape untuk jadwal kelas, guru, dan master.

#### User Review Required

> [!IMPORTANT]
> **Skema Room-ID & Deteksi Bentrok Tiga Arah (Teacher, Classroom, Room):**
> Kami mengusulkan untuk menambahkan kolom `room_id` (foreign key ke tabel `rooms`, nullable) pada tabel `schedules`. Hal ini penting karena jadwal tidak hanya mengikat guru dan kelas, tetapi juga ruangan fisik tempat KBM berlangsung.
> Dengan adanya `room_id`, sistem akan memvalidasi **3 jenis bentrok** secara otomatis saat menyimpan jadwal baru:
> 1. **Bentrok Guru:** Guru tidak boleh mengajar di dua kelas/ruang berbeda pada jam sesi yang sama.
> 2. **Bentrok Kelas:** Suatu kelas tidak boleh menerima mata pelajaran berbeda pada jam sesi yang sama.
> 3. **Bentrok Ruangan:** Dua kelas berbeda tidak boleh menggunakan ruangan fisik yang sama pada jam sesi yang sama (misal: Lab Komputer terisi ganda).
> Apakah Anda setuju dengan skema database dan validasi tiga arah ini?

#### Rencana Implementasi

##### [NEW] Migration: `add_room_id_to_schedules_table`
```php
Schema::table('schedules', function (Blueprint $table) {
    $table->foreignId('room_id')->nullable()->after('teacher_id')->constrained('rooms')->nullOnDelete();
});
```

##### [MODIFY] `app/Models/Schedule.php`
* Menambahkan `room_id` ke array `$fillable`.
* Menambahkan relasi `room()`:
```php
public function room() {
    return $this->belongsTo(Room::class);
}
```

##### [MODIFY] `app/Http/Controllers/ScheduleController.php`
Menambahkan/memodifikasi method berikut:
* `showGrid(Request $r)`: Diperbarui agar memuat daftar ruangan (`Room::all()`) dan mengirimkannya ke view.
* `store(Request $r)`: Menambahkan validasi `room_id` (opsional) dan melakukan 3 validasi bentrok (guru, kelas, ruangan).
* `teacherSchedule(Request $r)`: Menampilkan grid jadwal per guru dengan dropdown pilihan guru.
* `roomSchedule(Request $r)`: Menampilkan grid jadwal per ruangan dengan dropdown pilihan ruangan.
* `detectConflicts()`: Memvalidasi seluruh data jadwal aktif di database secara cross-check dan menampilkan semua bentrok yang ada.
* `printClassSchedule(Classroom $classroom)`: Ekspor/Print jadwal per kelas format landscape PDF.
* `printTeacherSchedule(User $teacher)`: Ekspor/Print jadwal per guru format landscape PDF.
* `printMasterSchedule()`: Ekspor/Print master jadwal seluruh sekolah.

##### [MODIFY] `routes/web.php`
Mendaftarkan rute-rute baru di dalam grup kurikulum:
```php
Route::get('schedules/teacher', [ScheduleController::class, 'teacherSchedule'])->name('schedules.teacher');
Route::get('schedules/room', [ScheduleController::class, 'roomSchedule'])->name('schedules.room');
Route::get('schedules/conflicts', [ScheduleController::class, 'detectConflicts'])->name('schedules.conflicts');
Route::get('schedules/print/class/{classroom}', [ScheduleController::class, 'printClassSchedule'])->name('schedules.print.class');
Route::get('schedules/print/teacher/{teacher}', [ScheduleController::class, 'printTeacherSchedule'])->name('schedules.print.teacher');
Route::get('schedules/print/master', [ScheduleController::class, 'printMasterSchedule'])->name('schedules.print.master');
```

##### [NEW] Views di `resources/views/schedules/`
* `teacher-view.blade.php`: Halaman grid visual jadwal dari sudut pandang guru.
* `room-view.blade.php`: Halaman grid visual jadwal dari sudut pandang ruangan.
* `conflicts.blade.php`: Dasbor audit bentrok jadwal yang menampilkan semua bentrok guru/ruang/kelas dengan badge merah menyala dan tombol aksi cepat untuk menghapus/mengedit jadwal bentrok.
* `print-class.blade.php`, `print-teacher.blade.php`, `print-master.blade.php`: Template cetak (landscape) dengan style minimalis bersih dioptimalkan untuk PDF.

##### [MODIFY] `resources/views/schedules/grid.blade.php`
* Menambahkan pilihan Ruangan (`room_id`) pada modal plot mata pelajaran.
* Menampilkan nama ruangan pada kartu jadwal jika ruangan dipilih.
* Menambahkan tombol pintasan navigasi ke: *Jadwal Guru, Jadwal Ruangan, Deteksi Bentrok, dan Cetak PDF*.

##### [MODIFY] `resources/views/components/sidebar.blade.php`
* Menambahkan menu navigasi baru di bawah Fitur Penjadwalan:
  * "Jadwal Guru" (link ke `schedules.teacher`)
  * "Jadwal Ruangan" (link ke `schedules.room`)
  * "Audit Bentrok" (link ke `schedules.conflicts`)

#### Verification Plan

##### Automated Tests
Membuat file test baru `tests/Feature/Academic/ScheduleTest.php` untuk memvalidasi:
* Otorisasi pengakses rute (hanya kurikulum/admin).
* Validasi input & konflik (deteksi bentrok guru, kelas, dan ruangan bekerja dengan benar).
* Halaman grid per guru dan ruangan memuat data yang benar.
* Pencetakan PDF (Class, Teacher, Master) terproses dengan status 200.

##### Manual Verification
* Menambahkan plotting jadwal baru melalui modal, verifikasi penginputan ruangan.
* Memaksa terjadinya bentrok (membuat jadwal guru di tempat berbeda pada jam sama), pastikan muncul pesan eror.
* Membuka halaman Audit Bentrok, verifikasi tampilan list bentrok.
* Membuka halaman cetak PDF, pastikan tata letak cetak rapi.


---

### 🔧 Modul 12.3: Penguatan E-Learning & Materi Pembelajaran (Selesai ✅)

**Status Saat Ini:** E-Learning sudah cukup lengkap (kelas, materi, assignment, submission, grading, secure mode). Tapi belum ada: diskusi per materi, announcement per kelas, progress tracking siswa.

#### User Review Required

> [!IMPORTANT]
> **Skema Pengarsipan & Duplikasi Kelas Virtual:**
> - **Pengarsipan:** Ketika kelas diarsipkan (`is_archived = true`), kelas tersebut tidak akan muncul di dashboard utama siswa/guru secara default. Hal ini mencegah dashboard dipenuhi oleh kelas semester lalu, namun riwayat belajar tetap tersimpan. Akses ke kelas arsip dapat dibuka kembali melalui tab "Arsip Kelas".
> - **Duplikasi:** Menduplikasi kelas virtual akan menyalin data dasar kelas (nama, deskripsi, subjek, kelas fisik) serta menduplikasi seluruh materi dan penugasan (tanpa menyalin data interaksi siswa seperti pengumpulan tugas, nilai, komentar diskusi, dan riwayat baca materi). Batas waktu tugas yang diduplikasi akan diset secara otomatis ke 7 hari ke depan (dapat diubah kembali oleh guru).
> Apakah alur pengarsipan dan duplikasi kelas virtual di atas disetujui?

#### Open Questions

> [!NOTE]
> **Logika Deteksi Progress Membaca Materi:**
> Kami mengusulkan pembuatan halaman detail materi baru (`/elearning/material/{material}`) yang akan dikunjungi oleh siswa untuk membaca deskripsi lengkap, mendownload lampiran file, menonton video, dan berdiskusi.
> - Setiap kunjungan pertama siswa ke halaman detail materi ini akan mencatat log di tabel `elearning_material_views`.
> - Kunjungan ini dianggap sebagai indikasi bahwa siswa telah membaca/mengakses materi tersebut.
> - Progress persentase siswa di kelas virtual dihitung dari rasio jumlah materi yang sudah dibaca ditambah tugas yang sudah dikerjakan dibagi dengan total materi dan tugas yang ada di kelas tersebut.
> Apakah mekanisme penghitungan progress ini sudah sesuai dengan kebutuhan guru?

#### Rencana Implementasi

##### [NEW] Migration: `create_elearning_enhancements_tables`
```php
Schema::table('elearning_classes', function (Blueprint $table) {
    $table->boolean('is_archived')->default(false)->after('description');
});

Schema::create('elearning_announcements', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('elearning_class_id')->constrained('elearning_classes')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Creator (teacher)
    $table->text('content');
    $table->timestamps();
});

Schema::create('elearning_material_views', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
    $table->foreignId('elearning_material_id')->constrained('elearning_materials')->cascadeOnDelete();
    $table->timestamp('viewed_at');
    $table->timestamps();

    $table->unique(['student_id', 'elearning_material_id']);
});

Schema::create('elearning_material_comments', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('elearning_material_id')->constrained('elearning_materials')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Commenter (student/teacher)
    $table->text('content');
    $table->timestamps();
});
```

##### [NEW] Models
* `ElearningAnnouncement.php`: Relasi ke `ElearningClass` dan `User` (creator).
* `ElearningMaterialView.php`: Relasi ke `Student` dan `ElearningMaterial`.
* `ElearningMaterialComment.php`: Relasi ke `User` (author) dan `ElearningMaterial`.
* Update `ElearningClass.php` dan `ElearningMaterial.php` untuk mendefinisikan relasi timbal balik.

##### [NEW] `app/Services/ElearningService.php`
```php
class ElearningService
{
    public function duplicateClass(int $classId, int $teacherId): ElearningClass;
    public function toggleArchive(int $classId): void;
    public function logMaterialView(int $studentId, int $materialId): void;
    public function getStudentProgress(int $classId, int $studentId): array;
    public function getClassProgressSummary(int $classId): array;
}
```

##### [MODIFY] `app/Http/Controllers/ElearningController.php`
Menambahkan/memodifikasi method berikut:
* `showClass(ElearningClass $elearningClass)`: Memuat pengumuman kelas (`announcements`) dan menghitung progress bar untuk siswa yang masuk.
* `storeAnnouncement(Request $r, ElearningClass $elearningClass)`: Guru memposting pengumuman.
* `deleteAnnouncement(ElearningClass $elearningClass, ElearningAnnouncement $announcement)`: Guru menghapus pengumuman.
* `showMaterial(ElearningClass $elearningClass, ElearningMaterial $material)`: Halaman detail materi, mencatat riwayat baca siswa, menampilkan daftar komentar, dan form input komentar.
* `storeComment(Request $r, ElearningMaterial $material)`: Siswa/Guru mengirim komentar ke materi.
* `deleteComment(ElearningMaterial $material, ElearningMaterialComment $comment)`: Menghapus komentar jika pemilik atau pengajar kelas.
* `classProgress(ElearningClass $elearningClass)`: Dashboard progres kelas (Wali kelas/Guru dapat melihat status ketuntasan siswa).
* `studentProgress(ElearningClass $elearningClass, Student $student)`: Halaman detail progress individu siswa (materi dibaca & tugas selesai).
* `duplicateClass(ElearningClass $elearningClass)`: Guru menduplikasi kelas.
* `archiveClass(ElearningClass $elearningClass)`: Guru mengarsipkan kelas.
* `unarchiveClass(ElearningClass $elearningClass)`: Guru memulihkan kelas dari arsip.

##### [MODIFY] `routes/web.php`
Mendaftarkan rute baru:
```php
// Virtual Class Management
Route::post('elearning/class/{elearningClass}/archive', [ElearningController::class, 'archiveClass'])->name('elearning.class.archive');
Route::post('elearning/class/{elearningClass}/unarchive', [ElearningController::class, 'unarchiveClass'])->name('elearning.class.unarchive');
Route::post('elearning/class/{elearningClass}/duplicate', [ElearningController::class, 'duplicateClass'])->name('elearning.class.duplicate');

// Announcements
Route::post('elearning/class/{elearningClass}/announcement', [ElearningController::class, 'storeAnnouncement'])->name('elearning.announcement.store');
Route::delete('elearning/class/{elearningClass}/announcement/{announcement}', [ElearningController::class, 'deleteAnnouncement'])->name('elearning.announcement.destroy');

// Materials & Discussions
Route::get('elearning/class/{elearningClass}/material/{material}', [ElearningController::class, 'showMaterial'])->name('elearning.material.show');
Route::post('elearning/material/{material}/comment', [ElearningController::class, 'storeComment'])->name('elearning.comment.store');
Route::delete('elearning/material/{material}/comment/{comment}', [ElearningController::class, 'deleteComment'])->name('elearning.comment.destroy');

// Progress Tracking
Route::get('elearning/class/{elearningClass}/progress', [ElearningController::class, 'classProgress'])->name('elearning.class.progress');
Route::get('elearning/class/{elearningClass}/progress/student/{student}', [ElearningController::class, 'studentProgress'])->name('elearning.class.student-progress');
```

##### [NEW & MODIFY] Views di `resources/views/elearning/`
* `show_class.blade.php` [MODIFY]:
  * Menampilkan widget pengumuman kelas di atas timeline.
  * Menambahkan section/modal posting pengumuman untuk Guru.
  * Menambahkan tombol pintasan "Progress Kelas", "Duplikat Kelas", "Arsipkan Kelas" untuk Guru.
  * Menambahkan progress bar keterbacaan materi & penugasan untuk Siswa.
* `show_material.blade.php` [NEW]:
  * Halaman detail materi untuk membaca penjelasan lengkap, unduh file, tonton video.
  * Form & daftar komentar diskusi di bawah materi (bergaya chat / thread modern).
* `class_progress.blade.php` [NEW]:
  * Halaman progres kelas bagi guru: chart rata-rata tuntas, daftar siswa dengan persentase keterbacaan materi, pengumpulan tugas, dan nilai rata-rata.
* `student_progress.blade.php` [NEW]:
  * Halaman checklist detail tuntas siswa (materi apa saja yang sudah dibaca ✅, tugas apa yang sudah dikumpulkan ✅ beserta nilainya).

##### Demo Data (Seeder)
* Menambahkan status `is_archived = true` pada 1 kelas demo lama.
* Menambahkan 5 pengumuman pada kelas aktif.
* Menambahkan log riwayat baca materi dan komentar diskusi pada materi untuk menyimulasikan kelas interaktif yang aktif.

#### Verification Plan

##### Automated Tests
* Membuat file unit/feature test `tests/Feature/Academic/ElearningEnhancementTest.php` untuk memvalidasi:
  * Pengumuman kelas dapat dibuat, dibaca, dan dihapus dengan otorisasi yang benar.
  * Membuka detail materi secara otomatis mencatat riwayat baca siswa (`elearning_material_views`).
  * Diskusi komentar materi berjalan (post komentar dan hapus komentar terotorisasi).
  * Guru dapat mengakses data progres kelas dan detail progress per siswa.
  * Guru dapat menduplikasi kelas virtual (materi & tugas tersalin dengan benar).
  * Guru dapat mengarsipkan & memulihkan kelas virtual.

##### Manual Verification
* Login sebagai Guru, posting pengumuman baru, buka halaman diskusi materi dan kirim komentar.
* Login sebagai Siswa, verifikasi progress bar di halaman kelas virtual, klik materi, pastikan riwayat baca tercatat, dan balas diskusi.
* Login kembali sebagai Guru, buka halaman "Progress Kelas" dan verifikasi grafik persentase serta checklist siswa.
* Lakukan duplikasi dan pengarsipan kelas virtual, verifikasi status kelas di dashboard.

---

---

### 🔧 Modul 12.4: Penguatan Modul Perpustakaan (Selesai ✅)

**Status Saat Ini:** Library cukup lengkap (katalog, CRUD, sirkulasi, peminjaman, pengembalian, denda). Tapi belum ada: rekomendasi buku, statistik baca, kartu anggota digital.

#### Rencana Implementasi

##### [MODIFY] `app/Http/Controllers/LibraryController.php`
```php
// Tambahkan method baru:
public function statistics()                // Statistik: buku paling sering dipinjam, jumlah peminjaman per bulan
public function studentReadingHistory($s)   // Riwayat baca per siswa
public function recommendations($studentId) // Rekomendasi buku berdasarkan riwayat peminjaman
public function overdueReport()             // Laporan buku terlambat dikembalikan
public function memberCard($studentId)      // Kartu anggota perpustakaan digital (PDF/gambar)
```

##### [NEW] Views
```
resources/views/library/
├── statistics.blade.php            # [NEW] Dashboard perpustakaan: chart peminjaman, top books, trend
├── reading-history.blade.php       # [NEW] Riwayat baca per siswa + buku favorit
├── recommendations.blade.php       # [NEW] Rekomendasi: "Karena kamu suka Fisika, coba buku ini..."
├── overdue-report.blade.php        # [NEW] DataTable: buku terlambat, siswa, berapa hari, denda
└── member-card.blade.php           # [NEW] Template kartu anggota digital (bisa di-print)
```

---

### 🔧 Modul 12.5: Penguatan Modul Keuangan (SPP & Billing)

**Status Saat Ini:** Selesai ✅ (Laporan tunggakan per kelas, proyeksi pendapatan, surat tagihan otomatis, rekapitulasi bulanan, ekspor laporan keuangan excel/pdf, dan automated tests telah diimplementasikan dengan lengkap).

#### Rencana Implementasi

##### [MODIFY] `app/Http/Controllers/FinanceController.php`
```php
// Tambahkan method baru:
public function tunggakanPerKelas()          // Laporan tunggakan per kelas: DataTable, total per kelas
public function proyeksiPendapatan()         // Proyeksi pendapatan bulan depan berdasarkan data existing
public function suratTagihan($billingId)     // Generate surat tagihan SPP otomatis (PDF)
public function rekapBulanan($month, $year)  // Rekap keuangan bulanan (income, expense, profit)
public function exportLaporanKeuangan()      // Export laporan keuangan lengkap (PDF/Excel)
```

##### [NEW] Views
```
resources/views/finance/
├── tunggakan-kelas.blade.php       # [NEW] Tabel tunggakan per kelas: kelas, jumlah siswa belum bayar, total nominal
├── proyeksi.blade.php              # [NEW] Proyeksi: chart line prediksi pendapatan
├── surat-tagihan.blade.php         # [NEW] Template surat tagihan: kop sekolah, data siswa, rincian
├── rekap-bulanan.blade.php         # [NEW] Rekap: income vs expense, chart, tabel detail
```

---

## 📦 FASE 13: Data Demo Realistis & Seeder Komprehensif

**Fokus:** Memperluas seeder agar demo terasa seperti sekolah nyata, bukan data placeholder generik. Saat ini hanya ~860 records — target: **3000+ records** dengan data yang contextually rich dan spesifik SMA ISA 1.

---

### 📦 Modul 13.1: Mega Seeder — Data Realistis SMA ISA 1 (Selesai ✅)

#### [MODIFY] `database/seeders/DatabaseSeeder.php`
```php
// Target data demo setelah seeding:
$this->call([
    // EXISTING (diperluas):
    UserSeeder::class,                  // 20 → 80 users (lebih banyak guru + orang tua)
    AcademicSeeder::class,              // 36 → 120 siswa (20 per kelas, 6 kelas per angkatan)
    FinanceSeeder::class,               // 220 → 600+ billings (semua siswa punya SPP 6 bulan)
    ActivitySeeder::class,              // 180 → 2000+ attendances (1 bulan penuh, setiap hari)
    ExtrasSeeder::class,                // Diperluas

    // NEW:
    DailyAssessmentSeeder::class,       // 50+ penilaian harian, 600+ scores, beberapa dengan remedial
    TahfidzDetailSeeder::class,         // Progress tahfidz per siswa kelas Tahfidz (target + logs)
    MutabaahDetailSeeder::class,        // 30 hari mutaba'ah per siswa, weekly summaries
    EventAttendanceSeeder::class,       // 10 kegiatan + presensi (upacara, sholat dhuha, PHBI)
    ForumSeeder::class,                 // 20 threads, 100 replies, reactions
    ElearningSeeder::class,             // 5 kelas, 20 materi, 10 assignments, 50 submissions
    CbtSeeder::class,                   // 3 ujian CBT, 30 soal per ujian, 100+ attempts
    CounselingSeeder::class,            // 10 sesi konseling (berbagai status)
    LetterSeeder::class,                // 15 surat (izin, keterangan, tugas, undangan)
    TicketSeeder::class,                // 10 tiket helpdesk (berbagai status + komentar)
    SurveySeeder::class,               // 2 survei + 50 responses + questions
    LibraryLoanSeeder::class,           // 30 peminjaman buku (aktif + selesai + overdue)
]);
```

#### Detail Seed Data Baru

##### [NEW] `database/seeders/DailyAssessmentSeeder.php`
```php
// Data penilaian harian yang realistis:
// Matematika:
//   - "Ulangan Harian BAB 1: Eksponen & Logaritma" (10 Juni, kelas X-IPA-1)
//   - "Kuis Trigonometri" (15 Juni, kelas XI-IPA-2)
//   - "Ulangan Harian BAB 3: Statistika" (20 Juni, kelas XII-IPA-1)
// Bahasa Indonesia:
//   - "Tugas Menulis Teks Eksposisi" (12 Juni)
//   - "Presentasi Resensi Novel" (18 Juni)
// Tahfidz:
//   - "Setoran Juz 30" (setiap hari, per siswa)
// Distribusi nilai: normal distribution sekitar 70-85, beberapa di bawah KKM (perlu remedial)
```

##### [NEW] `database/seeders/ForumSeeder.php`
```php
// Thread diskusi realistis SMA ISA 1:
// - "Tips persiapan UTBK 2026 — share pengalaman kakak kelas" (by siswa XII)
// - "Adakah yang punya rangkuman BAB 3 Kimia?" (by siswa XI)
// - "Pengumuman: Jadwal class meeting semester genap" (by Waka Kesiswaan, pinned)
// - "Tanya: Cara bayar SPP lewat QRIS?" (by orang tua)
// - "Diskusi: Persiapan Maulid Nabi 2026" (by guru PAI)
// Masing-masing thread punya 3-10 replies + reactions
```

##### [NEW] `database/seeders/CbtSeeder.php`
```php
// 3 ujian CBT dengan soal realistis:
// 1. "Ulangan Tengah Semester Matematika Kelas X" — 30 soal PG, 90 menit
//    → 20 siswa sudah mengerjakan, 10 belum
// 2. "Kuis Biologi BAB 5: Ekosistem" — 15 soal, 30 menit
//    → Semua sudah mengerjakan
// 3. "Ujian Akhir PAI Kelas XII" — 40 soal, 120 menit
//    → Belum dimulai (status: draft)
```

---

## 📦 FASE 14: UI/UX Polish, Konsistensi & Kualitas Kode

**Fokus:** Memoles tampilan, memastikan konsistensi di seluruh halaman, menambah animasi, dan meningkatkan kualitas kode.

---

### 🎨 Modul 14.1: UI/UX Consistency Audit & Fix

**Status:** Selesai ✅ (Implementasi 7 komponen UI premium: page-header, data-table, empty-state, stat-card, confirm-modal, form-group, status-badge; refaktor penuh modul user management, announcements, meeting notes, dan productivity todos dengan 100% test passing)

#### Checklist UI yang Harus Konsisten di Seluruh Halaman

```
□ Header halaman: semua halaman punya breadcrumb + judul + deskripsi
□ DataTable: semua tabel pakai style yang sama (pagination, search, export button)
□ Form: semua form pakai layout yang sama (label, input, helper text, error message)
□ Button: semua tombol pakai warna/ukuran yang konsisten per aksi
  ├── Primary (biru): Simpan, Submit
  ├── Success (hijau): Approve, Terima
  ├── Danger (merah): Hapus, Tolak
  ├── Warning (kuning): Edit, Revisi
  └── Secondary (abu): Batal, Kembali
□ Modal: semua modal pakai template yang sama (header, body, footer, close)
□ Alert/Flash Message: semua pakai komponen yang sama (success, error, warning, info)
□ Empty State: semua halaman list yang kosong punya empty state illustration + CTA
□ Loading State: semua halaman punya skeleton loading saat data belum ready
□ Responsive: semua halaman berfungsi di mobile (375px) — sidebar collapse, table scroll
□ Dark Mode: semua halaman support dark mode dengan benar
□ Print: halaman-halaman yang perlu di-print punya @media print styling
□ Favicon: favicon sekolah terpasang
□ Page Title: setiap halaman punya <title> yang descriptive
```

#### Implementasi

##### [MODIFY] `resources/views/components/`
```
resources/views/components/
├── page-header.blade.php           # [NEW/IMPROVE] Komponen header halaman reusable
├── data-table.blade.php            # [NEW] Wrapper DataTable dengan style konsisten
├── empty-state.blade.php           # [NEW] Empty state component: icon + message + CTA
├── stat-card.blade.php             # [NEW] Stat card dashboard: icon, angka, label, trend arrow
├── confirm-modal.blade.php         # [NEW] Modal konfirmasi reusable (hapus, approve, dll)
├── form-group.blade.php            # [NEW] Form group: label + input + error + helper text
├── status-badge.blade.php          # [NEW] Badge status: aktif/nonaktif, lunas/belum, hadir/alpa
└── breadcrumb.blade.php            # [NEW] Breadcrumb reusable
```

---

### 🎨 Modul 14.2: Animasi & Micro-Interactions

**Status:** Selesai ✅ (Implementasi keyframes shimmer, shake, modalIn, pageFadeIn, utility classes card-hover, btn-click, sidebar-item, dan counter up statistik interaktif 60fps berbasis Alpine.js)

#### Rencana Implementasi

##### [MODIFY] `resources/css/app.css` (atau Tailwind config)
```css
/* Animasi yang akan ditambahkan: */

/* 1. Page transition: fade-in saat pindah halaman */
.page-enter { animation: fadeIn 0.3s ease-in-out; }

/* 2. Card hover: sedikit naik + shadow lebih besar */
.card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }

/* 3. Button click: scale down sedikit */
.btn-click:active { transform: scale(0.97); }

/* 4. Skeleton loading: shimmer effect */
.skeleton { animation: shimmer 1.5s infinite linear; }

/* 5. Counter animation: angka naik perlahan saat pertama load */
.counter-animate { animation: countUp 1s ease-out; }

/* 6. Notification bell: shake saat ada notif baru */
.notif-shake { animation: shake 0.5s ease-in-out; }

/* 7. Success feedback: checkmark muncul setelah form submit */
.success-check { animation: scaleIn 0.3s ease-in-out; }

/* 8. Toast notification: slide in dari kanan */
.toast-enter { animation: slideInRight 0.4s ease-out; }

/* 9. Modal: scale + fade in */
.modal-enter { animation: modalIn 0.25s ease-out; }

/* 10. Sidebar menu item: indent animation saat hover */
.sidebar-item:hover { padding-left: 1.25rem; transition: padding-left 0.2s; }
```

---

### 🎨 Modul 14.3: Halaman Error & Empty State yang Cantik

**Status:** Selesai ✅ (Refaktorisasi penuh halaman error 404, 403, 401, 402, 419, 429, 500, 503 untuk memperluas layout premium dengan style inline fallback agar kokoh, penentuan lebar/tinggi SVG secara inline untuk mencegah luapan gambar, serta interaksi mikro pada empty state)

##### [MODIFY] `resources/views/errors/`
```
resources/views/errors/
├── 403.blade.php    # "Akses Ditolak" — ilustrasi gembok + pesan ramah + tombol kembali
├── 404.blade.php    # "Halaman Tidak Ditemukan" — ilustrasi + search suggestion + tombol ke dashboard
├── 419.blade.php    # "Sesi Kadaluarsa" — pesan + tombol refresh
├── 429.blade.php    # "Terlalu Banyak Request" — pesan + countdown timer
├── 500.blade.php    # "Server Error" — pesan + tombol lapor ke helpdesk
└── 503.blade.php    # "Maintenance Mode" — ilustrasi + estimasi waktu kembali
```

---

### 🧪 Modul 14.4: Penambahan Factory & Test untuk Fitur Baru

#### [NEW] Factories untuk Model Baru
```
database/factories/
├── DailyAssessmentFactory.php
├── DailyAssessmentScoreFactory.php
├── EventAttendanceEventFactory.php
├── EventAttendanceRecordFactory.php
├── TahfidzTargetFactory.php
├── MutabaahWeeklySummaryFactory.php
├── ForumThreadFactory.php
├── ForumReplyFactory.php
├── CounselingSessionFactory.php
├── LetterFactory.php
├── TicketFactory.php
├── LibraryLoanFactory.php
└── ExamFactory.php
```

#### [NEW] Test Files
```
tests/Feature/
├── DailyAssessmentTest.php          # CRUD penilaian harian, input score batch, auto-detect remedial
├── TahfidzComprehensiveTest.php     # Target setting, progress tracking, bulk input, export
├── MutabaahComprehensiveTest.php    # Input harian detail, rekap mingguan, statistik, gamifikasi trigger
├── EventAttendanceTest.php          # CRUD kegiatan, input presensi, rekap, export
├── ItemAnalysisTest.php             # Perhitungan P, D, distractor analysis, reliability
├── ScheduleConflictTest.php         # Deteksi bentrok guru/ruangan
├── DashboardWidgetTest.php          # Data widget baru per role load correctly
├── FinanceReportTest.php            # Tunggakan per kelas, proyeksi, rekap bulanan
└── LibraryStatisticsTest.php        # Statistik perpustakaan, recommendations, overdue report
```

---

## 📦 FASE 15: Fitur Tambahan — Productivity & Communication

**Fokus:** Fitur-fitur tambahan yang meningkatkan produktivitas dan komunikasi civitas SMA ISA 1.

---

### 📋 Modul 15.1: Memo & Notulensi Rapat Digital

**Status:** Selesai ✅ (Sistem notulensi rapat, daftar hadir, action items tracker interaktif, cetak PDF resmi kop sekolah, dan pengujian otomatis telah diselesaikan secara penuh).

**Tujuan:** Sistem untuk mendokumentasikan rapat guru, rapat OSIS, rapat yayasan. Bisa diakses kembali, di-share, dan di-print.

#### Rencana Implementasi

##### [NEW] Migration: `create_meeting_notes_table`
```php
Schema::create('meeting_notes', function (Blueprint $table) {
    $table->id();
    $table->string('title');                                // "Rapat Koordinasi Semester Genap 2026/2027"
    $table->enum('type', ['rapat_guru', 'rapat_osis', 'rapat_wali_murid', 'rapat_yayasan', 'rapat_panitia', 'lainnya']);
    $table->date('meeting_date');
    $table->time('start_time');
    $table->time('end_time')->nullable();
    $table->string('location')->nullable();                 // "Aula Utama" / "Ruang Guru"
    $table->foreignId('created_by')->constrained('users');
    $table->text('agenda')->nullable();                     // Agenda rapat (markdown)
    $table->text('discussion')->nullable();                 // Pembahasan (markdown)
    $table->text('decisions')->nullable();                  // Keputusan rapat (markdown)
    $table->text('action_items')->nullable();               // Tindak lanjut: JSON [{task, pic, deadline}]
    $table->json('attendees')->nullable();                  // [user_id, user_id, ...]
    $table->enum('status', ['draft', 'finalized'])->default('draft');
    $table->timestamps();
});
```

##### [NEW] `app/Http/Controllers/MeetingNoteController.php`
```php
class MeetingNoteController extends Controller
{
    public function index()                     // List semua notulensi rapat
    public function create()                    // Form buat notulensi
    public function store(Request $request)      // Simpan notulensi
    public function show($id)                   // Detail notulensi: agenda, pembahasan, keputusan, action items
    public function edit($id)                   // Edit notulensi (hanya status draft)
    public function update($id, Request $r)     // Update
    public function finalize($id)               // Finalisasi: tidak bisa diedit lagi
    public function exportPdf($id)              // Export notulensi ke PDF (kop sekolah, format resmi)
    public function actionItemTracker()         // Tracking action items: apa sudah dikerjakan? Siapa PIC?
}
```

##### [NEW] `resources/views/meeting-notes/`
```
resources/views/meeting-notes/
├── index.blade.php                 # DataTable: daftar notulensi, filter per tipe/bulan
├── create.blade.php                # Form: wizard step 1 (info umum) → step 2 (agenda) → step 3 (keputusan)
├── show.blade.php                  # Detail: formatted markdown, daftar hadir, action items
├── edit.blade.php                  # Edit form (same as create)
├── action-tracker.blade.php        # DataTable: action items lintas rapat, PIC, deadline, status ✅/⏳
└── pdf.blade.php                   # Template PDF: kop SMA ISA 1, format notulensi resmi
```

---

### 📢 Modul 15.2: Sistem Pengumuman yang Lebih Rich

**Status:** Selesai ✅ (Sistem pengumuman rich dengan kategori, lampiran berkas, pin dashboard, acknowledgement, read receipts, dan dashboard widget mading digital telah diselesaikan secara penuh).

#### Rencana Implementasi

##### [MODIFY] Migration: `enhance_announcements_table`
```php
// Tambah kolom:
$table->enum('priority', ['normal', 'important', 'urgent'])->default('normal');
$table->enum('category', ['akademik', 'keuangan', 'kegiatan', 'keislaman', 'umum'])->default('umum');
$table->string('attachment_path')->nullable();          // File lampiran (PDF/DOC/gambar)
$table->boolean('is_pinned')->default(false);           // Pin ke atas dashboard
$table->boolean('require_acknowledgement')->default(false); // Ortu/siswa harus klik "Sudah Dibaca"
$table->integer('read_count')->default(0);
```

##### [NEW] `app/Models/AnnouncementRead.php`
```php
// Track siapa yang sudah membaca pengumuman
Schema::create('announcement_reads', function (Blueprint $table) {
    $table->id();
    $table->foreignId('announcement_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamp('read_at');

    $table->unique(['announcement_id', 'user_id']);
});
```

##### [MODIFY] `app/Http/Controllers/AnnouncementController.php`
```php
// Tambah method:
public function pin($announcement)              // Pin/unpin pengumuman
public function readReceipt($announcement)       // Siapa saja yang sudah baca
public function acknowledge($announcement)       // User klik "Sudah Dibaca"
```

---

### 📅 Modul 15.3: Agenda & Todo List Guru

**Status:** Selesai ✅ (Todo list personal productivity untuk guru dengan CRUD lengkap, toggle completion, filter/sort/search, 7 stat cards, dashboard widget quick add, seeder data demo, dan 17 automated tests telah diselesaikan secara penuh).

**Tujuan:** Fitur personal productivity untuk guru — todo list, reminder, dan catatan pribadi yang terintegrasi dengan kalender akademik.

#### Rencana Implementasi

##### [NEW] Migration: `create_teacher_todos_table`
```php
Schema::create('teacher_todos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users');
    $table->string('title');                                // "Siapkan soal UTS Matematika"
    $table->text('description')->nullable();
    $table->date('due_date')->nullable();
    $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
    $table->enum('category', ['mengajar', 'administrasi', 'rapat', 'penilaian', 'lainnya'])->default('mengajar');
    $table->boolean('is_completed')->default(false);
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'is_completed', 'due_date']);
});
```

##### [NEW] `app/Http/Controllers/TeacherTodoController.php`
```php
class TeacherTodoController extends Controller
{
    public function index()                     // List todo: filter completed/pending, sort by deadline
    public function store(Request $request)      // Quick add todo (bisa dari dashboard widget)
    public function toggle($id)                 // Toggle complete/incomplete (AJAX)
    public function update($id, Request $r)     // Edit todo
    public function destroy($id)                // Hapus todo
    public function todayTasks()                // API: todo yang deadline-nya hari ini (untuk dashboard widget)
}
```

##### [NEW] `resources/views/todos/`
```
resources/views/todos/
├── index.blade.php                 # Full page: list todo, filter, sort, CRUD inline
└── widget.blade.php                # Dashboard widget: quick add + list 5 teratas
```

---

## 🧪 Rencana Verifikasi (Semua di Localhost)

### Verifikasi Per Fase

| Fase | Metode Verifikasi |
| :--- | :--- |
| **11 (Fitur Baru)** | `php artisan test` — semua test pass, `php artisan migrate:fresh --seed` — 0 error, buka setiap halaman baru di browser — tidak ada error |
| **12 (Penguatan)** | Dashboard setiap role menampilkan data yang benar, jadwal mendeteksi bentrok, semua view baru accessible |
| **13 (Data Demo)** | `php artisan migrate:fresh --seed` menghasilkan data realistis, setiap halaman punya data untuk ditampilkan (bukan kosong) |
| **14 (UI/UX)** | Visual check seluruh halaman: konsisten, responsive (375px), dark mode, animasi smooth |
| **15 (Productivity)** | Notulensi bisa dibuat + export PDF, pengumuman rich berfungsi, todo list bisa CRUD + toggle |

### Command Verifikasi Cepat
```bash
# 1. Reset database + seed data demo
php artisan migrate:fresh --seed

# 2. Jalankan semua test
php artisan test --parallel

# 3. Buka di browser
php artisan serve
# → Login sebagai setiap role (superadmin, guru, siswa, orang tua)
# → Verifikasi dashboard, navigasi, semua halaman baru
```

---

## 📋 Urutan Prioritas Implementasi

| Prioritas | Modul | Estimasi | Status |
| :---: | :--- | :--- | :---: |
| 🔴 **P0** | 11.3: Penilaian Harian & Remedial | 3-4 hari | ✅ Selesai |
| 🔴 **P0** | 12.1: Penguatan Dashboard per Role | 3-4 hari | ✅ Selesai |
| 🔴 **P0** | 13.1: Mega Seeder — Data Realistis | 3-4 hari | ✅ Selesai |
| 🟠 **P1** | 11.2: Mutaba'ah Ibadah Lengkap | 2-3 hari | ✅ Selesai |
| 🟠 **P1** | 11.1: Tahfidz Komprehensif | 2-3 hari | ✅ Selesai |
| 🟠 **P1** | 11.5: Presensi Kegiatan Sekolah | 2-3 hari | ✅ Selesai |
| 🟠 **P1** | 12.2: Penguatan Jadwal | 2 hari | ✅ Selesai |
| 🟡 **P2** | 11.4: Item Analysis CBT | 2-3 hari | ✅ Selesai |
| 🟡 **P2** | 12.3: Penguatan E-Learning | 2 hari | ✅ Selesai |
| 🟡 **P2** | 12.4: Penguatan Perpustakaan | 2 hari | ✅ Selesai |
| 🟡 **P2** | 12.5: Penguatan Keuangan | 2 hari | ✅ Selesai |
| 🟡 **P2** | 15.1: Notulensi Rapat | 2-3 hari | ✅ Selesai |
| 🟢 **P3** | 15.2: Pengumuman Rich | 1-2 hari | ✅ Selesai |
| 🟢 **P3** | 15.3: Todo List Guru | 1-2 hari | ✅ Selesai |
| 🟢 **P3** | 14.1: UI Consistency Audit | 3-4 hari | ✅ Selesai |
| 🟢 **P3** | 14.2: Animasi & Micro-Interactions | 2 hari | ✅ Selesai |
| 🟢 **P3** | 14.3: Error & Empty State Pages | 1-2 hari | ✅ Selesai |
| 🟢 **P3** | 14.4: Factory & Test Expansion | 3-4 hari | ✅ Selesai |

**Total Estimasi:** ~38-50 hari kerja (8-10 minggu)

---

## 🛠️ Tech Stack & Dependencies

Tidak ada dependencies baru yang diperlukan — semua fitur bisa dibangun dengan stack yang sudah ada:

| Layer | Teknologi |
| :--- | :--- |
| **Backend** | Laravel 13, PHP 8.3+, Eloquent ORM |
| **Frontend** | Blade Templates, Tailwind CSS v4, Alpine.js |
| **Charts** | ApexCharts.js (sudah terinstall) |
| **PDF Export** | DomPDF (sudah terinstall) |
| **Excel** | Maatwebsite Excel (sudah terinstall) |
| **Real-Time** | Laravel Reverb (sudah terinstall) |
| **Testing** | Pest PHP (sudah terinstall) |
| **Database** | SQLite (development) |
| **WhatsApp API** | Fonnte (sudah terinstall) |
| **Maps** | Leaflet.js (untuk alumni network map) — **baru** |

> 13. Modul 14.1 (UI Consistency Audit) (Selesai ✅) — karena mendefinisikan standar UI premium lewat komponen page-header, data-table, empty-state, stat-card, confirm-modal, form-group, status-badge, dan merefaktorkan modul inti.

---
---

## 📦 FASE 16: Fitur Baru — Modul Kritis yang Belum Ada

**Fokus:** Mengimplementasikan modul-modul besar yang belum ada sama sekali atau sangat dangkal, sehingga platform benar-benar lengkap sebagai Sistem Terpadu sekolah.

---

### 🎓 Modul 16.1: Sistem SPMB Online Komprehensif (Selesai ✅)

**Status Saat Ini:** Direktori `app/Http/Controllers/PPDB/` dan `resources/views/ppdb/` **kosong total** — belum ada controller, view, maupun route PPDB. Hanya ada model `Registration.php` dan `SpmbController.php` di root controller untuk SPMB (yang hanya menangani pendaftaran sederhana). Ini adalah **celah fitur paling kritis** di platform karena PPDB adalah proses tahunan wajib sekolah.

#### Mengapa Penting untuk SMA ISA 1?
PPDB Online adalah **kebutuhan utama sekolah modern**. SMA ISA 1 harus bisa menerima pendaftaran calon siswa baru secara online, mengelola seleksi, dan mengumumkan hasil. Tanpa ini, platform tidak layak disebut "Sistem Terpadu".

#### User Review Required

> [!IMPORTANT]
> **Alur PPDB Online yang Diusulkan:**
> Kami mengusulkan alur PPDB 6 tahap:
> 1. **Pendaftaran Online** — Calon siswa mengisi formulir lengkap + upload dokumen (KK, Ijazah/SKHUN, Foto, Akta) via halaman publik.
> 2. **Verifikasi Berkas** — Admin/Panitia PPDB memverifikasi kelengkapan & keaslian berkas.
> 3. **Tes Seleksi** — Integrasi opsional dengan modul CBT untuk ujian masuk online.
> 4. **Penilaian & Scoring** — Sistem skor otomatis berdasarkan bobot: Nilai UN/rapor (40%), Tes masuk (30%), Prestasi (15%), Tahfidz (15%).
> 5. **Pengumuman** — Halaman publik pengumuman kelulusan + notifikasi WhatsApp ke calon siswa.
> 6. **Daftar Ulang** — Pendaftar yang diterima melakukan daftar ulang + pembayaran biaya masuk.
> Apakah Anda setuju dengan alur 6 tahap ini?

#### Open Questions

> [!NOTE]
> **Gelombang Pendaftaran:**
> SMA ISA 1 biasanya membuka PPDB dalam beberapa gelombang (Gelombang 1, 2, 3). Apakah sistem perlu mendukung multi-gelombang dengan kuota terpisah per gelombang? Kami mengusulkan iya — setiap gelombang memiliki tanggal buka/tutup, kuota, dan biaya yang bisa berbeda.

> [!NOTE]
> **Jalur Pendaftaran:**
> Apakah SMA ISA 1 menggunakan jalur prestasi/jalur reguler/jalur tahfidz? Kami mengusulkan 3 jalur: Reguler, Prestasi (akademik/non-akademik), dan Tahfidz — masing-masing dengan kriteria dan kuota berbeda.

#### Rencana Implementasi

##### [NEW] Migration: `create_ppdb_system_tables`
```php
Schema::create('ppdb_periods', function (Blueprint $table) {
    $table->id();
    $table->foreignId('academic_year_id')->constrained('academic_years');
    $table->string('name');                                  // "PPDB 2026/2027"
    $table->integer('total_quota');                           // Total kuota diterima
    $table->enum('status', ['draft', 'open', 'closed', 'announced'])->default('draft');
    $table->timestamps();
});

Schema::create('ppdb_waves', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ppdb_period_id')->constrained('ppdb_periods')->cascadeOnDelete();
    $table->string('name');                                  // "Gelombang 1"
    $table->date('registration_start');
    $table->date('registration_end');
    $table->date('announcement_date')->nullable();
    $table->integer('quota');
    $table->decimal('registration_fee', 12, 2)->default(0);
    $table->enum('status', ['upcoming', 'open', 'closed', 'announced'])->default('upcoming');
    $table->timestamps();
});

Schema::create('ppdb_tracks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ppdb_period_id')->constrained('ppdb_periods')->cascadeOnDelete();
    $table->string('name');                                  // "Reguler" / "Prestasi" / "Tahfidz"
    $table->integer('quota');
    $table->json('scoring_weights')->nullable();             // {"rapor": 40, "tes": 30, "prestasi": 15, "tahfidz": 15}
    $table->text('requirements')->nullable();                // Persyaratan khusus jalur
    $table->timestamps();
});

Schema::create('ppdb_applicants', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('registration_number')->unique();         // Auto: PPDB-2026-0001
    $table->foreignId('ppdb_wave_id')->constrained('ppdb_waves');
    $table->foreignId('ppdb_track_id')->constrained('ppdb_tracks');
    $table->string('full_name');
    $table->string('nisn')->nullable();
    $table->enum('gender', ['L', 'P']);
    $table->date('birth_date');
    $table->string('birth_place');
    $table->text('address');
    $table->string('previous_school');
    $table->string('parent_name');
    $table->string('parent_phone');
    $table->string('parent_email')->nullable();
    $table->string('photo_path')->nullable();
    $table->string('document_kk_path')->nullable();
    $table->string('document_ijazah_path')->nullable();
    $table->string('document_skhun_path')->nullable();
    $table->string('document_akta_path')->nullable();
    $table->json('rapor_scores')->nullable();                // {"semester_1": 85, "semester_2": 87, ...}
    $table->json('achievement_data')->nullable();            // [{"title": "Juara 1 MTQ", "level": "kota", "year": 2026}]
    $table->json('tahfidz_data')->nullable();                // {"juz_count": 5, "certificate_path": "..."}
    $table->decimal('entrance_test_score', 5, 2)->nullable();
    $table->decimal('total_score', 5, 2)->nullable();        // Skor akhir (auto-calculated)
    $table->integer('rank')->nullable();                     // Peringkat di jalur
    $table->enum('document_status', ['incomplete', 'submitted', 'verified', 'rejected'])->default('incomplete');
    $table->enum('selection_status', ['pending', 'passed', 'failed', 'waitlist', 'accepted', 'enrolled'])->default('pending');
    $table->text('admin_notes')->nullable();
    $table->boolean('has_paid_registration')->default(false);
    $table->boolean('has_reregistered')->default(false);
    $table->timestamps();
});

Schema::create('ppdb_documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ppdb_applicant_id')->constrained('ppdb_applicants')->cascadeOnDelete();
    $table->string('document_type');                         // "kk", "ijazah", "skhun", "akta", "foto", "sertifikat"
    $table->string('file_path');
    $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
    $table->string('rejection_reason')->nullable();
    $table->foreignId('verified_by')->nullable()->constrained('users');
    $table->timestamp('verified_at')->nullable();
    $table->timestamps();
});
```

##### [NEW] Models
```
app/Models/
├── PpdbPeriod.php        → hasMany(PpdbWave, PpdbTrack), belongsTo(AcademicYear)
├── PpdbWave.php          → belongsTo(PpdbPeriod), hasMany(PpdbApplicant)
├── PpdbTrack.php         → belongsTo(PpdbPeriod), hasMany(PpdbApplicant)
├── PpdbApplicant.php     → belongsTo(PpdbWave, PpdbTrack), hasMany(PpdbDocument), HasUuid
└── PpdbDocument.php      → belongsTo(PpdbApplicant, User verified_by)
```

##### [NEW] `app/Http/Controllers/PPDB/PpdbController.php`
```php
class PpdbController extends Controller
{
    // === Halaman Publik (tanpa login) ===
    public function publicLanding()                         // Landing page PPDB: info, jadwal, syarat, alur
    public function publicRegistrationForm($waveId)         // Form pendaftaran online multi-step
    public function publicStore(Request $r)                 // Submit pendaftaran + upload dokumen
    public function publicCheckStatus()                     // Cek status pendaftaran via nomor registrasi + tanggal lahir
    public function publicAnnouncement($periodId)           // Halaman pengumuman kelulusan publik

    // === Panel Admin PPDB ===
    public function dashboard()                             // Dashboard PPDB: statistik pendaftar, kuota, funnel chart
    public function periods()                               // Kelola periode PPDB
    public function storePeriod(Request $r)                 // Buat periode PPDB baru
    public function waves($periodId)                        // Kelola gelombang pendaftaran
    public function storeWave(Request $r)                   // Buat gelombang baru
    public function tracks($periodId)                       // Kelola jalur pendaftaran
    public function storeTrack(Request $r)                  // Buat jalur baru
    public function applicants($waveId)                     // Daftar pendaftar per gelombang + filter jalur
    public function showApplicant($id)                      // Detail pendaftar lengkap
    public function verifyDocument($documentId)             // Verifikasi dokumen (accept/reject)
    public function bulkVerify(Request $r)                  // Verifikasi dokumen massal
    public function scoring($waveId)                        // Halaman scoring: input nilai tes + auto-calculate
    public function runSelection($waveId)                   // Eksekusi seleksi: hitung skor akhir, ranking, tentukan status
    public function publishResults($waveId)                 // Publikasikan hasil seleksi
    public function reregistration($waveId)                 // Kelola daftar ulang
    public function markReregistered($applicantId)          // Tandai sudah daftar ulang
    public function convertToStudent($applicantId)          // Konversi pendaftar diterima menjadi Student + User
    public function exportApplicants($waveId)               // Export data pendaftar ke Excel
    public function printCard($applicantId)                 // Cetak kartu peserta PPDB (PDF)
    public function statistics()                            // Statistik PPDB: tren per tahun, demografi, conversion rate
}
```

##### [NEW] `app/Services/PpdbService.php`
```php
class PpdbService
{
    public function generateRegistrationNumber(int $periodId): string;
    public function calculateApplicantScore(PpdbApplicant $applicant): float;
    public function runBulkSelection(int $waveId): array;   // Return summary: passed, failed, waitlist counts
    public function convertApplicantToStudent(PpdbApplicant $applicant): Student;
    public function getStatistics(int $periodId): array;
    public function getPpdbFunnel(int $periodId): array;     // Funnel: registered → verified → tested → passed → enrolled
    public function sendResultNotification(PpdbApplicant $applicant): void; // WhatsApp result
}
```

##### [NEW] Views di `resources/views/ppdb/`
```
resources/views/ppdb/
├── public/
│   ├── landing.blade.php           # Landing page: hero, alur, jadwal, syarat, FAQ, CTA daftar
│   ├── register.blade.php          # Form pendaftaran multi-step wizard (data diri → ortu → dokumen → konfirmasi)
│   ├── check-status.blade.php      # Cek status: input nomor registrasi + tanggal lahir
│   ├── announcement.blade.php      # Pengumuman kelulusan: search by name/nomor, status result
│   └── print-card.blade.php        # Kartu peserta PPDB (PDF template)
├── admin/
│   ├── dashboard.blade.php         # Dashboard: stat cards, funnel chart, kuota progress bar
│   ├── periods.blade.php           # Kelola periode PPDB
│   ├── waves.blade.php             # Kelola gelombang per periode
│   ├── tracks.blade.php            # Kelola jalur pendaftaran per periode
│   ├── applicants.blade.php        # DataTable pendaftar: filter jalur/status, bulk actions
│   ├── applicant-detail.blade.php  # Detail pendaftar: data lengkap + preview dokumen + timeline status
│   ├── verification.blade.php      # Panel verifikasi dokumen: side-by-side preview + approve/reject
│   ├── scoring.blade.php           # Input skor tes + auto-rank
│   ├── selection.blade.php         # Halaman eksekusi seleksi: preview ranking → confirm → publish
│   ├── reregistration.blade.php    # Tracking daftar ulang
│   └── statistics.blade.php        # Statistik PPDB multi-tahun: chart tren, demografi, conversion
```

##### Demo Data (Seeder)
```php
// PpdbSeeder.php:
// - 1 Periode PPDB aktif "PPDB 2026/2027"
// - 2 Gelombang: Gel 1 (closed, 80 kuota), Gel 2 (open, 40 kuota)
// - 3 Jalur: Reguler (60 kuota), Prestasi (30 kuota), Tahfidz (30 kuota)
// - 50 pendaftar Gel 1: 35 diterima, 10 gagal, 5 waitlist
// - 20 pendaftar Gel 2: status pending
// - Dokumen upload simulasi, skor tes acak, ranking otomatis
```

##### Integrasi Gamifikasi
* Siswa baru yang mendaftar via PPDB online dan diterima: +50 XP "Selamat Datang"

#### Verification Plan

##### Automated Tests
```
tests/Feature/PPDB/PpdbTest.php:
- Calon siswa dapat mengakses landing page dan form pendaftaran
- Calon siswa dapat submit pendaftaran + upload dokumen
- Calon siswa dapat cek status pendaftaran
- Admin dapat memverifikasi dokumen (accept/reject)
- Sistem menghitung skor akhir dengan benar sesuai bobot jalur
- Seleksi menghasilkan ranking dan status yang benar
- Konversi pendaftar → siswa berfungsi (User + Student + Classroom)
- Pengumuman publik menampilkan hasil yang benar
- Export Excel dan cetak kartu PDF berfungsi
```

##### Manual Verification
* Buka halaman landing PPDB publik, isi form pendaftaran lengkap.
* Login sebagai Admin, verifikasi dokumen, input skor tes, jalankan seleksi.
* Buka halaman pengumuman publik, cari nama pendaftar, pastikan status muncul.
* Konversi pendaftar diterima menjadi siswa aktif, pastikan muncul di daftar siswa.

---

### 🎓 Modul 16.2: Overhaul Modul Alumni & Tracer Study (Selesai ✅)

**Status Saat Ini:** Controller `AlumniController` hanya punya 3 method (`index`, `create`, `store`) — sangat dangkal. Tidak ada show, edit, delete, statistik, export, bulk import. `TracerStudyController` juga hanya 3 method tanpa export atau perbandingan multi-tahun.

#### Mengapa Penting untuk SMA ISA 1?
Alumni adalah **aset jangka panjang** SMA ISA 1. Jaringan alumni yang terdokumentasi baik membantu branding sekolah, mentoring siswa aktif, donasi, dan akreditasi (bukti luaran pendidikan).

#### Rencana Implementasi

##### [MODIFY] `app/Http/Controllers/AlumniController.php`
```php
// Refaktor total — dari 3 method menjadi 12+ method:
class AlumniController extends Controller
{
    public function index()                         // Directory alumni: search, filter tahun/universitas/pekerjaan
    public function show($id)                       // Profil alumni lengkap: riwayat di sekolah + karier
    public function create()                        // Form tambah alumni manual
    public function store(Request $r)               // Simpan alumni baru
    public function edit($id)                       // Edit data alumni
    public function update($id, Request $r)         // Update alumni
    public function destroy($id)                    // Hapus data alumni
    public function bulkGraduate(Request $r)        // Kelulusan massal: pilih kelas XII → tandai lulus → generate data alumni
    public function dashboard()                     // Dashboard alumni: stat cards, distribusi universitas, distribusi pekerjaan, peta sebaran
    public function exportExcel()                   // Export data alumni ke Excel
    public function exportPdf()                     // Export buku alumni per angkatan ke PDF
    public function directory()                     // Direktori alumni interaktif: card grid, search, filter
    public function alumniNetworkMap()              // Peta sebaran alumni (kota/provinsi/negara)
}
```

##### [MODIFY] `app/Http/Controllers/TracerStudyController.php`
```php
// Perkuat TracerStudy — dari 3 method menjadi 8 method:
class TracerStudyController extends Controller
{
    public function form()                          // Form tracer study (publik untuk alumni)
    public function store(Request $r)               // Submit tracer study
    public function editResponse($id)               // Alumni edit respons sebelumnya
    public function analytics()                     // Dashboard analytics: chart karier, gaji, kepuasan
    public function comparison()                    // Perbandingan antar-angkatan
    public function exportExcel()                   // Export hasil tracer study ke Excel
    public function exportPdf()                     // Export laporan tracer study ke PDF (format akreditasi)
    public function sendInvitation(Request $r)      // Kirim undangan isi tracer study ke alumni via WhatsApp/email
}
```

##### [NEW] `app/Services/AlumniEnhancedService.php`
```php
class AlumniEnhancedService
{
    public function bulkGraduateStudents(array $studentIds, int $graduationYear): int;
    public function getAlumniDistribution(): array;           // Per universitas, per pekerjaan, per kota
    public function getAlumniGeography(): array;              // Data untuk peta sebaran
    public function getTracerStudyComparison(array $years): array;
    public function generateAlumniBookPdf(int $year): string; // Path ke PDF buku alumni
    public function getResponseRate(int $year): float;
}
```

##### [NEW & MODIFY] Views
```
resources/views/alumni/
├── index.blade.php          # [MODIFY] Revamp: DataTable + advanced search/filter
├── show.blade.php           # [NEW] Profil alumni: foto, riwayat sekolah, karier, kontak
├── create.blade.php         # [MODIFY] Form ditambah: data karier, kontak, foto
├── edit.blade.php           # [NEW] Form edit alumni
├── dashboard.blade.php      # [NEW] Dashboard: stat cards, chart distribusi, donut chart
├── directory.blade.php      # [NEW] Card grid: foto, nama, angkatan, pekerjaan — searchable
├── bulk-graduate.blade.php  # [NEW] Multi-step: pilih kelas → preview siswa → konfirmasi lulus
├── network-map.blade.php    # [NEW] Peta Indonesia + dunia: pin lokasi alumni
├── tracer/
│   ├── analytics.blade.php  # [MODIFY] Revamp: chart ApexCharts, filter per angkatan
│   ├── comparison.blade.php # [NEW] Side-by-side comparison: angkatan A vs B
│   └── invitation.blade.php # [NEW] Form kirim undangan: pilih angkatan, preview pesan
```

---

### 👨‍💼 Modul 16.3: Penguatan Modul HRD & Kepegawaian (Selesai ✅)

**Status Saat Ini:** HRD sudah punya CRUD pegawai, presensi GPS, cuti, dan payroll. Tapi belum ada: evaluasi kinerja, tracking sertifikasi/pelatihan, manajemen kontrak, dan org chart.

#### Mengapa Penting untuk SMA ISA 1?
Sebagai sekolah di bawah YBWSA, SMA ISA 1 memiliki **60+ pegawai** (guru + staf). Evaluasi kinerja tahunan, tracking sertifikasi guru, dan manajemen kontrak adalah kebutuhan operasional HRD yang penting.

#### Rencana Implementasi

##### [NEW] Migration: `create_hrd_enhancements_tables`
```php
Schema::create('performance_evaluations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
    $table->foreignId('evaluator_id')->constrained('users');    // Kepsek / Atasan langsung
    $table->string('period');                                    // "Semester Genap 2025/2026"
    $table->json('criteria_scores');                             // {"pedagogik": 85, "profesional": 90, "sosial": 80, "kepribadian": 88}
    $table->decimal('total_score', 5, 2);
    $table->enum('grade', ['A', 'B', 'C', 'D', 'E']);
    $table->text('strengths')->nullable();
    $table->text('improvements')->nullable();
    $table->text('goals')->nullable();                           // Target semester depan
    $table->enum('status', ['draft', 'submitted', 'acknowledged'])->default('draft');
    $table->timestamps();
});

Schema::create('employee_trainings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
    $table->string('title');                                     // "Workshop Kurikulum Merdeka"
    $table->enum('type', ['workshop', 'seminar', 'sertifikasi', 'diklat', 'webinar', 'lainnya']);
    $table->string('organizer');                                 // "Kemendikbud" / "YBWSA"
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->integer('hours')->nullable();                        // Durasi dalam jam (untuk JP/JPL)
    $table->string('certificate_path')->nullable();
    $table->string('certificate_number')->nullable();
    $table->date('certificate_expiry')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});

Schema::create('employee_documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
    $table->string('title');                                     // "SK Pengangkatan", "Sertifikat Guru"
    $table->enum('type', ['sk', 'kontrak', 'sertifikasi', 'ijazah', 'ktp', 'npwp', 'bpjs', 'lainnya']);
    $table->string('file_path');
    $table->date('issue_date')->nullable();
    $table->date('expiry_date')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

##### [MODIFY] `app/Http/Controllers/HRD/EmployeeController.php`
```php
// Tambahkan method baru:
public function dashboard()                     // Dashboard HRD: statistik pegawai, chart jabatan, umur, gender
public function documents($employeeId)          // Kelola dokumen pegawai
public function storeDocument(Request $r)       // Upload dokumen
public function deleteDocument($id)             // Hapus dokumen
public function trainings($employeeId)          // Riwayat pelatihan per pegawai
public function storeTraining(Request $r)       // Tambah pelatihan
public function evaluation($employeeId)         // Halaman evaluasi kinerja
public function storeEvaluation(Request $r)     // Simpan evaluasi
public function evaluationReport()              // Rekap evaluasi kinerja seluruh pegawai
public function certificationTracker()          // Tracking sertifikasi: siapa yang akan expired
public function orgChart()                      // Bagan organisasi sekolah (tree view)
public function exportEmployeeCard($id)         // Cetak kartu pegawai (ID card PDF)
public function attendanceReport()              // Laporan kehadiran pegawai bulanan
```

##### [NEW] Views
```
resources/views/hrd/
├── dashboard.blade.php          # [NEW] Dashboard HRD: chart distribusi, stat cards
├── documents/
│   └── index.blade.php          # [NEW] Kelola dokumen pegawai + upload
├── trainings/
│   ├── index.blade.php          # [NEW] Riwayat pelatihan pegawai
│   └── tracker.blade.php        # [NEW] Tracking sertifikasi: expiry alert, renewal reminder
├── evaluations/
│   ├── form.blade.php           # [NEW] Form evaluasi kinerja: slider per kriteria
│   ├── report.blade.php         # [NEW] Rekap evaluasi: DataTable + chart radar per pegawai
│   └── show.blade.php           # [NEW] Detail evaluasi individual
├── org-chart.blade.php          # [NEW] Org chart interaktif (tree/hierarchy view)
├── attendance-report.blade.php  # [NEW] Rekap kehadiran bulanan: DataTable + export
└── employee-card.blade.php      # [NEW] Template kartu pegawai PDF
```

---

### 🏫 Modul 16.4: Penguatan Ekskul & Kegiatan Kesiswaan (Selesai ✅)

**Status Saat Ini:** Ekskul sudah punya list, registrasi (max 2), presensi, dan activity logging. Tapi belum ada: CRUD ekskul oleh admin, achievement/lomba tracking, laporan semester, jadwal kalender, dan penilaian ekskul untuk rapor.

#### Rencana Implementasi

##### [NEW] Migration: `create_extracurricular_enhancements`
```php
Schema::create('extracurricular_achievements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('extracurricular_id')->constrained('extracurriculars')->cascadeOnDelete();
    $table->string('title');                                 // "Juara 1 Lomba Debat Bahasa Inggris"
    $table->enum('level', ['sekolah', 'kecamatan', 'kota', 'provinsi', 'nasional', 'internasional']);
    $table->date('event_date');
    $table->string('organizer');
    $table->json('participants')->nullable();                // [student_id, student_id, ...]
    $table->string('certificate_path')->nullable();
    $table->string('photo_path')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});

Schema::create('extracurricular_grades', function (Blueprint $table) {
    $table->id();
    $table->foreignId('extracurricular_member_id')->constrained('extracurricular_members')->cascadeOnDelete();
    $table->foreignId('academic_year_id')->constrained('academic_years');
    $table->enum('grade', ['A', 'B', 'C', 'D'])->nullable();    // Predikat untuk rapor
    $table->text('description')->nullable();                      // Deskripsi capaian
    $table->timestamps();

    $table->unique(['extracurricular_member_id', 'academic_year_id']);
});
```

##### [MODIFY] `app/Http/Controllers/ExtracurricularController.php`
```php
// Tambahkan method baru:
public function create()                        // Form buat ekskul baru (admin)
public function store(Request $r)               // [MODIFY] Simpan ekskul + validasi
public function edit($id)                       // Form edit ekskul
public function update($id, Request $r)         // Update ekskul
public function destroy($id)                    // Hapus ekskul
public function unregister($memberId)           // Siswa keluar dari ekskul
public function achievements($id)               // Daftar prestasi/lomba ekskul
public function storeAchievement(Request $r)    // Tambah prestasi
public function grading($id)                    // Form penilaian ekskul per semester (untuk rapor)
public function storeGrades(Request $r)         // Simpan nilai ekskul batch
public function attendanceReport($id)           // Laporan presensi per ekskul
public function semesterReport($id)             // Laporan semester ekskul: kegiatan, prestasi, presensi, nilai
public function schedule()                      // Kalender jadwal seluruh ekskul
public function exportPdf($id)                  // Export laporan ekskul ke PDF
```

##### [NEW] Views
```
resources/views/extracurricular/
├── create.blade.php             # [NEW] Form buat ekskul baru
├── edit.blade.php               # [NEW] Form edit ekskul
├── achievements.blade.php       # [NEW] Daftar prestasi/lomba: DataTable + upload sertifikat
├── grading.blade.php            # [NEW] Form penilaian batch: DataTable per anggota, pilih grade A-D
├── attendance-report.blade.php  # [NEW] Rekap presensi per ekskul: chart + DataTable
├── semester-report.blade.php    # [NEW] Laporan semester lengkap: kegiatan, prestasi, kehadiran, nilai
├── schedule.blade.php           # [NEW] Kalender jadwal ekskul (calendar view)
└── pdf-report.blade.php         # [NEW] Template cetak laporan ekskul
```

---

### 🕌 Modul 16.5: Penguatan OSIS & MPK (Organisasi Siswa) (Selesai ✅)

**Status Saat Ini:** OSIS hanya punya kas (income/expense) + dashboard. MPK sudah lebih lengkap (audit, LPJ, aspirasi). Belum ada: kepengurusan, pemilihan/voting, program kerja, dan proposal kegiatan.

#### Rencana Implementasi

##### [NEW] Migration: `create_osis_mpk_enhancements`
```php
Schema::create('osis_members', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('students');
    $table->string('position');                              // "Ketua Umum", "Sekretaris", "Sekbid 1"
    $table->foreignId('academic_year_id')->constrained('academic_years');
    $table->enum('status', ['active', 'inactive'])->default('active');
    $table->timestamps();
});

Schema::create('osis_work_programs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('academic_year_id')->constrained('academic_years');
    $table->string('title');                                 // "Bakti Sosial Ramadhan 2026"
    $table->string('sekbid');                                // "Sekbid 1: Keimanan & Ketaqwaan"
    $table->text('description');
    $table->decimal('budget_plan', 12, 2)->default(0);
    $table->decimal('budget_actual', 12, 2)->default(0);
    $table->date('planned_date')->nullable();
    $table->date('actual_date')->nullable();
    $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
    $table->text('report')->nullable();                      // Laporan pelaksanaan
    $table->json('photo_paths')->nullable();
    $table->timestamps();
});

Schema::create('osis_elections', function (Blueprint $table) {
    $table->id();
    $table->foreignId('academic_year_id')->constrained('academic_years');
    $table->string('title');                                 // "Pemilihan Ketua OSIS 2026/2027"
    $table->date('voting_start');
    $table->date('voting_end');
    $table->enum('status', ['upcoming', 'active', 'closed', 'announced'])->default('upcoming');
    $table->timestamps();
});

Schema::create('osis_candidates', function (Blueprint $table) {
    $table->id();
    $table->foreignId('election_id')->constrained('osis_elections')->cascadeOnDelete();
    $table->foreignId('student_id')->constrained('students');
    $table->integer('candidate_number');
    $table->text('vision')->nullable();                      // Visi
    $table->text('mission')->nullable();                     // Misi
    $table->string('photo_path')->nullable();
    $table->integer('vote_count')->default(0);
    $table->timestamps();
});

Schema::create('osis_votes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('election_id')->constrained('osis_elections')->cascadeOnDelete();
    $table->foreignId('voter_id')->constrained('students');
    $table->foreignId('candidate_id')->constrained('osis_candidates');
    $table->timestamps();

    $table->unique(['election_id', 'voter_id']); // 1 siswa 1 suara
});
```

##### [MODIFY] `app/Http/Controllers/OsisController.php`
```php
// Perkuat OSIS — dari 5 method menjadi 15+ method:
public function dashboard()                         // Dashboard OSIS: kas, program kerja, statistik
public function members()                           // Kelola kepengurusan OSIS
public function storeMember(Request $r)             // Tambah pengurus
public function updateMember($id, Request $r)       // Edit jabatan
public function removeMember($id)                   // Hapus pengurus
public function workPrograms()                      // Daftar program kerja
public function storeWorkProgram(Request $r)        // Tambah proker
public function updateWorkProgram($id, Request $r)  // Update proker + upload laporan/foto
public function transactions()                      // [EXISTING] Kas OSIS
public function storeTransaction(Request $r)        // [EXISTING] Tambah transaksi
public function editTransaction($id)                // [NEW] Edit transaksi
public function deleteTransaction($id)              // [NEW] Hapus transaksi
public function elections()                         // Kelola pemilihan OSIS
public function storeElection(Request $r)           // Buat pemilihan baru
public function candidates($electionId)             // Kelola kandidat
public function storeCandidate(Request $r)          // Daftarkan kandidat
public function votingPage($electionId)             // Halaman voting untuk siswa (secure, anonymous)
public function castVote(Request $r)                // Submit suara
public function electionResults($electionId)        // Hasil pemilihan: chart, tabel, pemenang
public function exportReport()                      // [EXISTING] Export PDF + ditambah proker
```

##### [NEW] Views
```
resources/views/extracurricular/osis/
├── dashboard.blade.php         # [MODIFY] Revamp: tambah proker stats, kepengurusan mini
├── members.blade.php           # [NEW] Struktur kepengurusan: org chart + DataTable
├── work-programs.blade.php     # [NEW] Daftar proker: status, budget plan vs actual, timeline
├── election/
│   ├── index.blade.php         # [NEW] Daftar pemilihan + status
│   ├── candidates.blade.php    # [NEW] Kelola kandidat: foto, visi/misi
│   ├── voting.blade.php        # [NEW] Halaman voting siswa: card kandidat + tombol pilih
│   └── results.blade.php       # [NEW] Hasil: chart pie, tabel suara, pemenang announced
```

---

## 📦 FASE 17: Penguatan Modul Existing — Depth & Polish Level 2

**Fokus:** Memperdalam fitur-fitur yang sudah ada tapi masih perlu depth tambahan, terutama di area analytics, chatbot, UKS, dan CMS.

---

### 📊 Modul 17.1: Penguatan Analytics & Predictive Intelligence (Selesai ✅)

**Status Saat Ini:** `AnalyticsController` hanya 1 method (`index`) — semua logic ada di `AnalyticsService` (15.7KB). Belum ada drill-down, date range filter, custom report builder UI, export, atau year-over-year comparison.

#### Rencana Implementasi

##### [MODIFY] `app/Http/Controllers/AnalyticsController.php`
```php
// Perkuat dari 1 method menjadi 10+ method:
public function index()                         // [EXISTING] Dashboard utama analytics
public function attendance(Request $r)          // Drill-down kehadiran: per kelas, per bulan, tren
public function academic(Request $r)            // Drill-down akademik: rata-rata per mapel, distribusi nilai
public function financial(Request $r)           // Drill-down keuangan: collection rate, aging analysis
public function islamic(Request $r)             // Drill-down keislaman: mutaba'ah, tahfidz, event kehadiran
public function studentRisk()                   // Prediksi siswa at-risk: kombinasi kehadiran rendah + nilai rendah + mutaba'ah rendah
public function comparison(Request $r)          // Perbandingan year-over-year / semester-over-semester
public function exportReport(Request $r)        // Export laporan analytics ke PDF/Excel
public function customReport()                  // Custom report builder: pilih metrik, filter, group by
public function saveCustomReport(Request $r)    // Simpan custom report sebagai template
public function scheduledReports()              // Kelola laporan terjadwal
public function heatmap()                       // Heatmap: kehadiran per hari per kelas (matrix view)
```

##### [NEW] `app/Services/PredictiveAnalyticsService.php`
```php
class PredictiveAnalyticsService
{
    public function identifyAtRiskStudents(): Collection;      // Siswa dengan skor risiko tinggi
    public function calculateRiskScore(Student $student): array; // Detail skor risiko: kehadiran, nilai, mutaba'ah, poin
    public function generateTrendForecast(string $metric, int $months): array; // Prediksi tren ke depan
    public function getCorrelationAnalysis(): array;           // Korelasi antara kehadiran ↔ nilai, mutaba'ah ↔ poin, dll
    public function getStudentPerformanceRadar(int $studentId): array; // Data radar chart: 6 dimensi kinerja siswa
}
```

##### [NEW] Views
```
resources/views/analytics/
├── index.blade.php              # [MODIFY] Revamp: tambah date range, filter, navigation cards
├── attendance.blade.php         # [NEW] Drill-down kehadiran: line chart, heatmap, DataTable
├── academic.blade.php           # [NEW] Drill-down akademik: histogram, scatter plot, box plot
├── financial.blade.php          # [NEW] Drill-down keuangan: aging analysis, trend, projection
├── islamic.blade.php            # [NEW] Drill-down keislaman: radar chart, ranking, tren
├── student-risk.blade.php       # [NEW] Prediksi at-risk: card per siswa, skor risiko, rekomendasi intervensi
├── comparison.blade.php         # [NEW] Side-by-side comparison: pilih 2 periode → chart overlay
├── custom-report.blade.php      # [NEW] Report builder: drag-drop metrik, filter builder
└── heatmap.blade.php            # [NEW] Heatmap matrix: baris=kelas, kolom=hari, warna=% kehadiran
```

---

### 🤖 Modul 17.2: Penguatan AI Chatbot & Knowledge Base (Selesai ✅)

**Status Saat Ini:** Chatbot sudah punya FAQ CRUD dan chat interface. Tapi belum ada: admin conversation history, analytics dashboard, eskalasi ke helpdesk, dan context-aware responses.

#### Rencana Implementasi

##### [MODIFY] `app/Http/Controllers/ChatbotController.php`
```php
// Tambahkan method baru:
public function adminDashboard()                // Dashboard chatbot admin: stat cards, popular questions, helpfulness rate
public function conversationHistory()           // Riwayat percakapan semua user (admin view)
public function showConversation($id)           // Detail percakapan individual
public function escalateToTicket($conversationId) // Eskalasi percakapan → tiket helpdesk
public function analytics()                     // Analytics: most asked, helpfulness rate, response time, topic distribution
public function importFaqs(Request $r)          // Import FAQ dari Excel
public function exportFaqs()                    // Export FAQ ke Excel
public function trainSuggestions()              // Saran pelatihan: pertanyaan yang sering gagal dijawab
```

##### [MODIFY] `app/Services/ChatbotService.php`
```php
// Tambahkan:
public function getContextualResponse(User $user, string $message): string;
// → Chatbot aware konteks user:
//   - Siswa bertanya "kapan ujian?" → cek jadwal CBT yang upcoming
//   - Ortu bertanya "berapa tunggakan?" → cek billing anak
//   - Guru bertanya "siapa yang belum absen?" → cek attendance hari ini
public function getAnalytics(): array;           // Popular questions, failure rate, topic clustering
public function getTrainingSuggestions(): array;  // Pertanyaan yang sering gagal dijawab → suggest FAQ baru
```

##### [NEW] Views
```
resources/views/admin/chatbot/
├── dashboard.blade.php          # [NEW] Dashboard: stat cards (total chats, success rate, avg rating)
├── conversations.blade.php      # [NEW] DataTable percakapan: user, tanggal, rating, status
├── conversation-detail.blade.php # [NEW] Detail: bubble chat view, feedback, escalate button
├── analytics.blade.php          # [NEW] Analytics: word cloud, top questions, trend chart
├── import.blade.php             # [NEW] Import FAQ dari Excel
└── train-suggestions.blade.php  # [NEW] Saran pertanyaan baru untuk FAQ
```

---

### 🏥 Modul 17.3: Penguatan UKS & Rekam Medis Siswa (Selesai ✅)

**Status Saat Ini:** UKS sudah lengkap dengan visit CRUD, medicine inventory, statistics, dan PDF report. Tapi belum ada: rekam medis permanen siswa (penyakit kronis, alergi, golongan darah), jadwal health screening, notifikasi ortu, dan edit visit.

#### Rencana Implementasi

##### [NEW] Migration: `create_uks_enhancements`
```php
Schema::create('student_health_records', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
    $table->enum('blood_type', ['A', 'B', 'AB', 'O', 'unknown'])->default('unknown');
    $table->json('allergies')->nullable();                   // ["Debu", "Kacang", "Udang"]
    $table->json('chronic_conditions')->nullable();          // ["Asma", "Diabetes"]
    $table->json('medications')->nullable();                 // Obat rutin yang dikonsumsi
    $table->text('medical_notes')->nullable();               // Catatan medis khusus
    $table->string('emergency_contact_name')->nullable();
    $table->string('emergency_contact_phone')->nullable();
    $table->string('insurance_number')->nullable();          // No. BPJS / asuransi
    $table->decimal('height_cm', 5, 1)->nullable();
    $table->decimal('weight_kg', 5, 1)->nullable();
    $table->enum('vision_status', ['normal', 'minus', 'plus', 'silinder', 'unknown'])->default('unknown');
    $table->timestamps();

    $table->unique('student_id');
});

Schema::create('health_screenings', function (Blueprint $table) {
    $table->id();
    $table->string('title');                                 // "Screening Kesehatan Semester 1"
    $table->date('screening_date');
    $table->foreignId('academic_year_id')->constrained('academic_years');
    $table->json('target_classrooms')->nullable();           // [classroom_id, ...]
    $table->enum('status', ['planned', 'in_progress', 'completed'])->default('planned');
    $table->text('notes')->nullable();
    $table->timestamps();
});

Schema::create('health_screening_results', function (Blueprint $table) {
    $table->id();
    $table->foreignId('screening_id')->constrained('health_screenings')->cascadeOnDelete();
    $table->foreignId('student_id')->constrained('students');
    $table->decimal('height_cm', 5, 1)->nullable();
    $table->decimal('weight_kg', 5, 1)->nullable();
    $table->string('vision_left')->nullable();               // "6/6", "6/12"
    $table->string('vision_right')->nullable();
    $table->string('dental_status')->nullable();
    $table->text('findings')->nullable();
    $table->text('recommendations')->nullable();
    $table->timestamps();
});
```

##### [MODIFY] `app/Http/Controllers/UksController.php`
```php
// Tambahkan method baru:
public function editVisit($id)                  // Form edit kunjungan UKS
public function updateVisit($id, Request $r)    // Update kunjungan
public function healthRecords()                 // Kelola rekam medis siswa
public function showHealthRecord($studentId)    // Detail rekam medis individual
public function updateHealthRecord(Request $r)  // Update rekam medis
public function screeningIndex()                // Daftar health screening
public function storeScreening(Request $r)      // Buat screening baru
public function inputScreeningResults($id)      // Form input hasil screening per siswa
public function storeScreeningResults(Request $r) // Simpan hasil screening batch
public function screeningReport($id)            // Laporan hasil screening (chart + DataTable)
public function exportScreeningPdf($id)         // Export laporan screening ke PDF
public function medicineDispenseLog()           // Log pemberian obat: siapa, kapan, obat apa
public function restockLog()                    // Log pembelian/restock obat
```

---

### 🌐 Modul 17.4: Penguatan CMS & Website Publik (Selesai ✅)

**Status Saat Ini:** CMS sudah lengkap (pages, posts, gallery, testimonials) tapi belum ada rendering frontend publik yang cantik, scheduled publishing, tag management, dan comments.

#### Rencana Implementasi

##### [MODIFY] `app/Http/Controllers/PublicSiteController.php`
```php
// Perkuat halaman publik:
public function home()                          // [EXISTING] Homepage sekolah
public function about()                         // Profil sekolah + visi misi + sejarah
public function news()                          // Daftar berita/artikel (paginated, search, category filter)
public function newsDetail($slug)               // Detail berita: full content, related posts, share buttons
public function gallery()                       // Galeri foto/video sekolah (lightbox gallery)
public function contact()                       // Halaman kontak + Google Maps embed + form inquiry
public function page($slug)                     // Dynamic CMS page rendering
public function facilities()                    // Fasilitas sekolah: foto + deskripsi per ruangan
public function achievements()                  // Prestasi siswa & sekolah: timeline + filter
public function staffDirectory()                // Direktori guru & staff (foto, nama, jabatan, mapel)
public function ppdbInfo()                       // Info PPDB → redirect ke modul PPDB jika aktif
public function virtualTour()                   // Virtual tour sekolah (embedded 360° photos)
```

##### [MODIFY] `app/Http/Controllers/CmsController.php`
```php
// Tambahkan:
public function schedulePost($id, Request $r)   // Jadwalkan publikasi post
public function tagManagement()                 // Kelola tag/kategori
public function mediaLibrary()                  // Perpustakaan media: upload, browse, delete gambar/file
public function seoSettings()                   // Pengaturan SEO global: title template, meta, OG tags
```

##### [NEW & MODIFY] Views
```
resources/views/public/
├── home.blade.php               # [MODIFY] Revamp: hero slider, berita terbaru, prestasi, CTA
├── about.blade.php              # [NEW] Profil sekolah: sejarah, visi misi, sambutan kepsek
├── news/
│   ├── index.blade.php          # [NEW] Grid berita: thumbnail, judul, tanggal, kategori
│   └── show.blade.php           # [NEW] Detail berita: cover image, content, author, share
├── gallery.blade.php            # [NEW] Galeri foto: masonry grid + lightbox
├── contact.blade.php            # [NEW] Kontak: form + maps + info
├── facilities.blade.php         # [NEW] Fasilitas: card grid + foto per ruangan
├── achievements.blade.php       # [NEW] Timeline prestasi sekolah
├── staff-directory.blade.php    # [NEW] Guru & staf: card grid, foto, nama, mapel
└── virtual-tour.blade.php       # [NEW] Virtual tour: embedded 360° viewer
```

---

## 📦 FASE 18: Penguatan Communication, Workflow & Notification

**Fokus:** Meningkatkan kemampuan komunikasi platform, workflow approval, dan notifikasi otomatis.

---

### 📲 Modul 18.1: Penguatan WhatsApp Automation & Broadcast (Selesai ✅)

**Status Saat Ini:** WhatsApp sudah punya test send, settings, dan log. Tapi belum ada: broadcast massal, template management, scheduled messages, delivery tracking, dan automated triggers.

#### Rencana Implementasi

##### [NEW] Migration: `create_whatsapp_enhancements`
```php
Schema::create('whatsapp_templates', function (Blueprint $table) {
    $table->id();
    $table->string('name');                                  // "Reminder SPP", "Info Ujian"
    $table->enum('category', ['keuangan', 'akademik', 'keislaman', 'kegiatan', 'umum']);
    $table->text('content');                                 // Template dengan placeholder: {nama_siswa}, {kelas}, {tagihan}
    $table->json('placeholders')->nullable();                // ["nama_siswa", "kelas", "tagihan"]
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('whatsapp_broadcasts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('template_id')->nullable()->constrained('whatsapp_templates');
    $table->string('title');
    $table->text('message');
    $table->enum('target_type', ['all_parents', 'all_teachers', 'specific_class', 'custom']);
    $table->json('target_ids')->nullable();                  // [classroom_id] atau [user_id]
    $table->integer('total_recipients')->default(0);
    $table->integer('sent_count')->default(0);
    $table->integer('failed_count')->default(0);
    $table->enum('status', ['draft', 'scheduled', 'sending', 'completed', 'cancelled'])->default('draft');
    $table->timestamp('scheduled_at')->nullable();
    $table->timestamp('sent_at')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});

Schema::create('whatsapp_automations', function (Blueprint $table) {
    $table->id();
    $table->string('name');                                  // "Auto-reminder SPP H-3"
    $table->enum('trigger', [
        'billing_due_3_days',        // SPP jatuh tempo H-3
        'billing_overdue',           // SPP lewat jatuh tempo
        'attendance_absent',         // Siswa alpa hari ini
        'exam_upcoming_1_day',       // Ujian CBT besok
        'uks_visit_severe',          // Kunjungan UKS severity berat
        'grade_below_kkm',           // Nilai di bawah KKM
        'mutabaah_grade_d',          // Grade mutaba'ah D
        'tahfidz_behind_target',     // Tahfidz di belakang target
    ]);
    $table->foreignId('template_id')->constrained('whatsapp_templates');
    $table->boolean('is_active')->default(true);
    $table->json('config')->nullable();                      // Konfigurasi tambahan per trigger
    $table->timestamps();
});
```

##### [MODIFY] `app/Http/Controllers/WhatsappController.php`
```php
// Perkuat dari 4 method menjadi 15+ method:
public function dashboard()                     // [NEW] Dashboard WA: sent stats, delivery rate, chart tren
public function templates()                     // Template management: CRUD template
public function storeTemplate(Request $r)
public function updateTemplate($id, Request $r)
public function deleteTemplate($id)
public function broadcasts()                    // Daftar broadcast history
public function createBroadcast()               // Form kirim broadcast: pilih target, template, preview
public function storeBroadcast(Request $r)      // Simpan + kirim/jadwalkan broadcast
public function showBroadcast($id)              // Detail broadcast: delivery stats, failed list
public function retryFailed($broadcastId)       // Kirim ulang yang gagal
public function automations()                   // Daftar automation triggers
public function storeAutomation(Request $r)     // Buat automation baru
public function toggleAutomation($id)           // Enable/disable automation
public function deleteAutomation($id)           // Hapus automation
public function logs(Request $r)                // [MODIFY] Log dengan filter: tanggal, status, tipe
```

##### [NEW] Views
```
resources/views/admin/whatsapp/
├── dashboard.blade.php          # [NEW] Dashboard: sent/delivered/failed stats, chart per bulan
├── templates/
│   ├── index.blade.php          # [NEW] CRUD template: preview, placeholder chips
│   └── create.blade.php         # [NEW] Form buat template: WYSIWYG + placeholder selector
├── broadcasts/
│   ├── index.blade.php          # [NEW] History broadcast: DataTable + filter
│   ├── create.blade.php         # [NEW] Wizard broadcast: target → template → preview → kirim/jadwalkan
│   └── show.blade.php           # [NEW] Detail: delivery stats pie chart, recipient list
├── automations/
│   ├── index.blade.php          # [NEW] Daftar automation: trigger, template, toggle on/off
│   └── create.blade.php         # [NEW] Form: pilih trigger → template → config
```

---

### 🔄 Modul 18.2: Penguatan Approval Workflow

**Status Saat Ini:** Workflow punya pending list, my requests, dan approve/reject sederhana. Tapi belum ada: workflow definition CRUD, multi-step chains, delegation, resubmit after rejection, SLA tracking, dan analytics.

#### Rencana Implementasi

##### [MODIFY] `app/Http/Controllers/WorkflowController.php`
```php
// Perkuat dari 5 method menjadi 12+ method:
public function dashboard()                     // [NEW] Dashboard: pending counts per tipe, avg approval time
public function definitions()                   // [NEW] Kelola definisi workflow
public function storeDefinition(Request $r)     // [NEW] Buat workflow baru
public function updateDefinition($id, Request $r) // [NEW] Edit workflow
public function pending()                       // [EXISTING] Daftar menunggu approval
public function myRequests()                    // [EXISTING] Request saya
public function show($id)                       // [EXISTING] Detail request
public function approve($id)                    // [EXISTING] Setujui
public function reject($id)                     // [EXISTING] Tolak
public function revise($id)                     // [NEW] Kembalikan untuk revisi (bukan tolak final)
public function resubmit($id, Request $r)       // [NEW] Kirim ulang request setelah revisi
public function delegate($id, Request $r)       // [NEW] Delegasikan approval ke orang lain
public function history()                       // [NEW] Riwayat workflow: semua request yang sudah selesai
public function analytics()                     // [NEW] Analytics: avg time, bottleneck analysis, chart
```

##### [NEW] Views
```
resources/views/workflows/
├── dashboard.blade.php          # [NEW] Dashboard: pending stat cards, chart avg time
├── definitions.blade.php        # [NEW] Kelola definisi workflow: tabel definisi + modal CRUD
├── history.blade.php            # [NEW] Riwayat: DataTable semua request, filter status/tipe
├── analytics.blade.php          # [NEW] Analytics: line chart avg time, bar chart per tipe
```

---

### 🔔 Modul 18.3: Penguatan Smart Alert & Notifikasi

**Status Saat Ini:** Smart Alert punya list, acknowledge, resolve, dan student alerts. Tapi belum ada: alert creation UI, notification integration (WhatsApp/email saat alert critical), alert rules config, dan dashboard visualization.

#### Rencana Implementasi

##### [MODIFY] `app/Http/Controllers/SmartAlertController.php`
```php
// Tambahkan method baru:
public function dashboard()                     // [NEW] Dashboard: stat cards, severity distribution chart
public function show($id)                       // [NEW] Detail alert: timeline status changes, related data
public function rulesConfig()                   // [NEW] Konfigurasi aturan alert: threshold per tipe
public function updateRules(Request $r)         // [NEW] Simpan konfigurasi aturan
public function manualCreate(Request $r)        // [NEW] Buat alert manual (untuk kasus khusus)
public function history()                       // [NEW] Riwayat alert yang sudah resolved
public function escalate($id)                   // [NEW] Eskalasi alert ke atasan
public function exportReport(Request $r)        // [NEW] Export laporan alert ke PDF
```

---

## 📦 FASE 19: Data Quality, Testing & Performance

**Fokus:** Meningkatkan kualitas data, coverage testing, dan performa platform.

---

### 📦 Modul 19.1: Mega Seeder v2 — Data PPDB, HRD, Ekskul, OSIS (Selesai ✅)

##### [NEW] Seeders
```
database/seeders/
├── PpdbSeeder.php              # 1 periode, 2 gelombang, 3 jalur, 70 pendaftar (simulasi full funnel)
├── HrdEnhancementSeeder.php    # 10 evaluasi kinerja, 30 pelatihan, 50 dokumen pegawai
├── ExtracurricularSeeder.php   # [MODIFY] Tambah prestasi lomba, nilai ekskul, jadwal
├── OsisMpkSeeder.php           # Kepengurusan OSIS, 8 proker, 1 pemilihan dengan 3 kandidat + 100 suara
├── StudentHealthSeeder.php     # Rekam medis 60 siswa, 1 screening + hasil
├── WhatsappEnhancementSeeder.php # 5 template, 3 automations, 2 broadcast history
├── AnalyticsDataSeeder.php     # Data dummy 2 semester untuk comparison analytics
```

---

### 🧪 Modul 19.2: Test Coverage Expansion (Selesai ✅)

##### [NEW] Tests
```
tests/Feature/
├── PPDB/
│   ├── PpdbPublicTest.php       # Landing, registration form, status check, announcement
│   ├── PpdbAdminTest.php        # Dashboard, verification, scoring, selection
│   └── PpdbConversionTest.php   # Convert applicant → student
├── HRD/
│   ├── PerformanceEvaluationTest.php  # Evaluation CRUD, scoring, reporting
│   ├── TrainingTest.php         # Training CRUD, certification tracking
│   └── EmployeeDocumentTest.php # Document CRUD, expiry alerts
├── Extracurricular/
│   ├── ExtracurricularCrudTest.php    # Create/edit/delete ekskul
│   ├── AchievementTest.php      # Achievement logging
│   └── GradingTest.php          # Semester grading for report card
├── Osis/
│   ├── OsisMemberTest.php       # Member management
│   ├── WorkProgramTest.php      # Work program CRUD
│   └── ElectionTest.php         # Election, voting, results
├── Analytics/
│   ├── DrilldownTest.php        # Each drill-down view loads correctly
│   └── PredictiveTest.php       # At-risk prediction calculations
├── WhatsApp/
│   ├── BroadcastTest.php        # Broadcast CRUD, sending, retry
│   └── AutomationTest.php       # Automation trigger tests
├── UKS/
│   ├── HealthRecordTest.php     # Student health records CRUD
│   └── ScreeningTest.php        # Health screening CRUD + results
├── CMS/
│   ├── PublicSiteTest.php       # All public pages render correctly
│   └── MediaLibraryTest.php     # Media upload, browse, delete
└── Workflow/
    ├── DefinitionTest.php       # Workflow definition CRUD
    └── DelegationTest.php       # Delegation, resubmit, revision
```

> **Status**: Selesai. Seluruh 22 test suite baru berhasil dibuat dan lulus 100% tanpa regresi. Total test suite mencapai **774 tests, 3108 assertions**.

---

### ⚡ Modul 19.3: Performance Optimization (Selesai ✅)

Peningkatan performa aplikasi melalui optimasi database, caching data statis, pengurangan query N+1, dan peningkatan kecepatan seeding/testing.

## User Review Required

> [!NOTE]
> Seluruh optimasi performa dirancang agar aman terhadap integritas data (data integrity) dan kompatibel penuh dengan driver SQLite `:memory:` saat testing.

## Proposed Changes

### 1. Database Indexing & N+1 Queries

#### [NEW] [2026_07_17_000000_add_performance_indexes.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/database/migrations/2026_07_17_000000_add_performance_indexes.php)
* Membuat migration baru untuk menambahkan index performa pada kolom yang paling sering di-query:
  * Indeks pada `grades` (`student_id`, `subject_id`, `recorded_by`)
  * Indeks pada `student_health_records` (`student_id`)
  * Indeks pada `whatsapp_logs` (`broadcast_id`, `status`)

#### [MODIFY] [OsisController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/OsisController.php)
* Eager load relasi `classroom` pada pemuatan aktif `$students` di method `members` dan `candidates` guna mencegah N+1 query saat merender nama kelas siswa di drop-down.

#### [MODIFY] [ExtracurricularController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/ExtracurricularController.php)
* Eager load relasi `advisor` pada seluruh path di method `manage()` untuk meminimalkan query penasihat ekskul.

---

### 2. Caching Data Statis

#### [MODIFY] [Subject.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Models/Subject.php)
* Menambahkan helper static `allCached()` untuk memuat seluruh mata pelajaran dari cache.
* Menambahkan boot observer (`saved`, `deleted`) untuk melakukan cache busting otomatis (`subjects_all`).

#### [MODIFY] [Classroom.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Models/Classroom.php)
* Menambahkan helper static `allCached()` untuk memuat seluruh kelas dari cache.
* Menambahkan boot observer (`saved`, `deleted`) untuk melakukan cache busting otomatis (`classrooms_all`).

#### [MODIFY] [WarmupCache.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Console/Commands/WarmupCache.php)
* Memperbarui command cache warmup agar memuat cache untuk `classrooms_all` dan `subjects_all` secara berkala/manual.

---

### 3. Database Seeding & Testing Speed

#### [MODIFY] [DatabaseSeeder.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/database/seeders/DatabaseSeeder.php)
* Membungkus pemanggilan seeder di dalam satu `DB::transaction(...)` tunggal. Langkah ini memangkas waktu seeding SQLite hingga 10x lipat (karena SQLite hanya perlu melakukan disk-write/commit satu kali di akhir transaksi).

## Verification Plan

### Automated Tests
- Menjalankan seluruh test suite menggunakan `php artisan test` untuk menjamin tidak ada yang rusak pasca-optimasi.
- Menjalankan test secara paralel menggunakan `php artisan test --parallel` untuk memvalidasi kecepatan eksekusi pengujian.

### Manual Verification
- Menjalankan `php artisan db:seed` untuk membandingkan kecepatan seeding sebelum dan sesudah dibungkus transaksi database.
- Memastikan cache di-warmup dengan baik via `php artisan cache:warmup`.

> **Status**: Selesai. Migration index baru ditambahkan, N+1 queries di OsisController & ExtracurricularController dieliminasi, model caching `Subject::allCached()` & `Classroom::allCached()` diimplementasikan, dan DatabaseSeeder dibungkus dalam `DB::transaction`. Seluruh 774 tes tetap hijau.


---

## 📦 FASE 20: Final Polish & Portfolio Showcase

**Fokus:** Finishing touches untuk membuat platform benar-benar layak menjadi portofolio premium.

---

### 🎨 Modul 20.1: Landing Page & Public Website Polish (Selesai ✅)

Redesain halaman landing page publik serta optimalisasi SEO global, penataan aset favicon/manifest untuk PWA, dukungan cetak dokumen, dan kesesuaian aksesibilitas standar Lighthouse.

## User Review Required

> [!IMPORTANT]
> Terdapat perbaikan bug pada `PublicSiteController.php` di mana pencarian jumlah siswa menggunakan status `'aktif'` padahal di database berstatus `'active'`. Ini akan diperbaiki agar statistik jumlah siswa pada landing page menampilkan angka yang riil.

## Proposed Changes

### 1. Landing Page Redesign & Bug Fix

#### [MODIFY] [welcome.blade.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/resources/views/welcome.blade.php)
* Desain ulang tata letak (layout) halaman landing page utama:
  * Hero Section: Menambahkan visual modern premium, transisi teks halus, dan tombol call-to-action dinamis.
  * Counter Stats: Membuat presentasi angka statistik interaktif dengan inisialisasi count-up instan.
  * Testimonials Carousel: Menyediakan slider responsif berbasis AlpineJS / CSS snap scroll yang mudah dioperasikan.
  * Menambahkan tag SEO Meta, Open Graph (OG), dan struktur data JSON-LD (School/LocalBusiness) untuk optimasi mesin pencari.

#### [MODIFY] [PublicSiteController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/PublicSiteController.php)
* Mengubah query pencarian status siswa: `Student::where('status', 'aktif')->count()` menjadi `Student::where('status', 'active')->count()`.

---

### 2. SEO & PWA Asset Integration

#### [MODIFY] [public.blade.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/resources/views/layouts/public.blade.php)
* Menambahkan tag meta SEO global, Open Graph, dan Twitter Cards ke dalam layout publik utama.
* Menghubungkan favicon PWA (`apple-touch-icon`, `icon` 32x32/16x16) serta tautan ke `manifest.json`.

#### [MODIFY] [robots.txt](file:///c:/project/portofolio/project-super-web/sistem-terpadu/public/robots.txt)
* Membatasi akses perayap (crawler) pada halaman panel admin/CBT dan menautkan sitemap global.

#### [NEW] [sitemap.xml](file:///c:/project/portofolio/project-super-web/sistem-terpadu/public/sitemap.xml)
* Membuat berkas sitemap XML statis yang mendaftarkan seluruh tautan publik SISTA untuk mempercepat pengindeksan Google.

---

### 3. Print Stylesheet & Accessibility

#### [MODIFY] [app.css](file:///c:/project/portofolio/project-super-web/sistem-terpadu/resources/css/app.css)
* Menambahkan aturan media `@media print` untuk menyembunyikan elemen navigasi (navbar), tombol aksi, latar belakang gradien/blob, serta memformat teks/tabel laporan agar ramah cetak.
* Melakukan audit aksesibilitas (menambahkan ARIA labels pada tombol ikon, navigasi keyboard terstruktur, serta rasio kontras warna yang nyaman).

## Verification Plan

### Automated Tests
- Menjalankan `php artisan test tests/Feature/CMS/PublicSiteTest.php` untuk memastikan seluruh halaman publik masih dapat diakses dengan baik dan tidak ada error kompilasi Blade.

### Manual Verification
- Membuka halaman depan (`/`) dan memeriksa visualisasi counter statistik (apakah sudah menampilkan angka riil siswa, bukan 0).
- Menguji responsivitas carousel testimoni dan menu navbar pada tampilan mobile (375px).
- Memeriksa struktur JSON-LD menggunakan Rich Results Test mockup atau browser console.
- Menguji fungsi cetak halaman (`Ctrl + P`) untuk melihat kebersihan print stylesheet.

> **Status**: Selesai dengan detail penuh. Landing page didesain ulang total dengan tema teal/emerald premium, count-up AlpineJS, testimonial carousel, SEO Open Graph, JSON-LD Schema.org (`@verbatim`), PWA manifest, sitemap.xml, robots.txt, dan print stylesheet. Bug `@context` & `@keyframes` di Blade berhasil diperbaiki. Menambahkan detail penyelarasan alamat sekolah (Jl. Mataram No.657, Wonodri) pada multi-bahasa (ID, EN, AR) serta data terstruktur JSON-LD, serta menghubungkan halaman-halaman profil statis (About, Contact, Facilities, Achievements, Staff, Virtual Tour) via dropdown "Profil" baru di header & link di footer. **PublicSiteTest: 6/6 PASS. Total suite: 774/774.**


### 📱 Modul 20.2: PWA Enhancement & Responsive Polish (Selesai ✅)

Checklist:
[x] PWA manifest update: correct icons, theme color, scope
[x] Offline page: halaman cantik saat offline
[x] Install prompt: banner "Install SISTA App" di mobile
[x] Push notification: notifikasi browser untuk alert penting
[x] Responsive audit: semua halaman berfungsi di 375px, 768px, 1024px, 1440px
[x] Touch-friendly: semua button/link minimal 44x44px di mobile
[x] Sidebar: collapse otomatis di mobile, swipe gesture
[x] DataTable: horizontal scroll di mobile, column priority

> **Status**: Selesai. PWA manifest terintegrasi warna tema teal (#0d9488), Service Worker teregistrasi di seluruh area publik (landing page dan layout publik), offline.html dioptimalkan, banner install app berbasis Alpine.js ditambahkan di seluruh layout, push notification handler terpasang di service worker, serta media query untuk minimum touch targets (42px) dan responsive horizontal table-scroll disematkan di app.css. Asset terkompilasi sukses dengan Vite.

### 📋 Modul 20.3: Documentation & Developer Portal (Selesai ✅)

Checklist:
[x] README.md update: screenshot, demo credentials, feature list, tech stack
[x] API documentation: Scribe auto-generated
[x] Database schema diagram (ERD): mermaid/dbdiagram.io
[x] Architecture documentation: folder structure, design patterns, naming conventions
[x] Demo walkthrough guide: step-by-step untuk setiap role
[x] Deployment guide: Docker, shared hosting, VPS
[x] Environment setup guide: prerequisites, installation steps

> **Status**: Selesai. Root README.md diperbarui dengan menambahkan detail modul FASE 16-20 lengkap, Scribe API documentation diregenerasi sukses (39 endpoints), database schema didokumentasikan di docs/DATABASE.md lengkap dengan diagram ERD Mermaid, panduan arsitektur diperbarui di docs/ARCHITECTURE.md, panduan alur demo peran (WALKTHROUGH.md) dibuat lengkap, panduan deployment diperbarui di docs/DEPLOYMENT.md, dan panduan instalasi lokal diperbarui di docs/INSTALLATION.md. Semua dokumen diselaraskan penuh.

---

## 📦 FASE 21: Fitur Baru — Manajemen Keuangan Syariah, ZIS & Wakaf Sekolah

**Fokus:** Menghadirkan nilai-nilai keislaman pada aspek finansial sekolah melalui implementasi tabungan syariah siswa wadiah, cashless canteen, serta portal Zakat, Infaq, Shodaqoh (ZIS) & Wakaf yang terintegrasi dengan yayasan induk (YBWSA).

---

### 💳 Modul 21.1: Tabungan Wadiah Siswa & Cashless Canteen System (Selesai ✅)

Sistem tabungan internal sekolah berbasis akad Wadiah (titipan amanah). Siswa atau orang tua dapat menyetor dana untuk digunakan sebagai alat pembayaran nontunai (cashless) di kantin dan koperasi sekolah, lengkap dengan rekapitulasi pengeluaran harian dan batasan limit jajan harian dari orang tua.

#### User Review Required
> [!IMPORTANT]
> **Metode Identifikasi Transaksi Kantin:**
> Mengingat platform berjalan penuh di localhost, verifikasi transaksi kantin akan disimulasikan menggunakan scan QR Code unik siswa (generasi statis di layar profil siswa) atau penginputan Nomor Induk Siswa (NIS) oleh kasir kantin. Apakah ini disetujui, atau apakah diperlukan integrasi dengan NFC emulator untuk simulasi kartu fisik?
> *Usulan Kami:* Menggunakan QR Code dinamis berbasis waktu (TOTP) di halaman Portal Siswa yang discan oleh kasir menggunakan kamera web/kamera laptop.

#### Rencana Implementasi

##### [NEW] Migration: `create_syariah_savings_and_canteen_tables`
```php
Schema::create('wadiah_accounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
    $table->string('account_number')->unique();              // Auto: WDH-YYYYMM-NNNN
    $table->decimal('balance', 12, 2)->default(0.00);
    $table->decimal('daily_spending_limit', 12, 2)->default(50000.00); // Batasan belanja harian oleh ortu
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('wadiah_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('wadiah_account_id')->constrained('wadiah_accounts')->cascadeOnDelete();
    $table->enum('type', ['deposit', 'withdrawal', 'purchase', 'refund']);
    $table->decimal('amount', 12, 2);
    $table->decimal('previous_balance', 12, 2);
    $table->decimal('current_balance', 12, 2);
    $table->string('reference_number')->unique();            // Ref transaksi
    $table->string('description')->nullable();
    $table->foreignId('processed_by')->constrained('users'); // Petugas koperasi/kantin/kasir
    $table->timestamps();
});

Schema::create('canteen_merchants', function (Blueprint $table) {
    $table->id();
    $table->string('name');                                  // "Kantin Bu Sri", "Koperasi YBWSA"
    $table->foreignId('owner_user_id')->constrained('users'); // Akun merchant kantin
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('canteen_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('canteen_merchant_id')->constrained('canteen_merchants');
    $table->foreignId('wadiah_transaction_id')->nullable()->constrained('wadiah_transactions')->nullOnDelete();
    $table->json('items_ordered');                           // [{"name":"Nasi Goreng","qty":1,"price":12000}]
    $table->decimal('total_amount', 12, 2);
    $table->timestamps();
});
```

##### [NEW] Models
```
app/Models/
├── WadiahAccount.php      → belongsTo(Student), hasMany(WadiahTransaction)
├── WadiahTransaction.php  → belongsTo(WadiahAccount, User processed_by), hasOne(CanteenTransaction)
├── CanteenMerchant.php    → belongsTo(User), hasMany(CanteenTransaction)
└── CanteenTransaction.php → belongsTo(CanteenMerchant, WadiahTransaction)
```

##### [NEW] `app/Http/Controllers/Finance/WadiahController.php`
```php
class WadiahController extends Controller
{
    public function dashboard()                             // Dashboard tabungan: total tabungan sekolah, chart transaksi
    public function index()                                 // List rekening siswa (admin/TU)
    public function showAccount($id)                        // Detail rekening + riwayat transaksi lengkap
    public function depositForm($accountId)                 // Form setor tunai
    public function storeDeposit(Request $r)                // Simpan setoran tunai
    public function withdrawForm($accountId)                // Form tarik tunai
    public function storeWithdraw(Request $r)               // Simpan penarikan tunai
    public function setLimit($accountId, Request $r)        // Atur limit harian (oleh orang tua/wali)
    public function exportPassbook($accountId)              // Cetak buku tabungan PDF
}
```

##### [NEW] `app/Http/Controllers/Finance/CanteenController.php`
```php
class CanteenController extends Controller
{
    public function merchantDashboard()                     // Panel pemilik kantin: penjualan hari ini, daftar transaksi
    public function posTerminal()                           // Terminal kasir kantin (tambah item belanjaan → scan QR/input NIS)
    public function processPayment(Request $r)              // Eksekusi pembayaran wadiah, validasi saldo & limit harian
    public function settlementReport()                      // Laporan pencairan dana kantin ke rekening bank merchant
}
```

##### [NEW] `app/Services/WadiahService.php`
```php
class WadiahService
{
    public function createAccount(Student $student): WadiahAccount;
    public function deposit(WadiahAccount $account, float $amount, int $processedBy, string $desc = ''): WadiahTransaction;
    public function withdraw(WadiahAccount $account, float $amount, int $processedBy, string $desc = ''): WadiahTransaction;
    public function purchase(WadiahAccount $account, float $amount, int $merchantUserId, array $items): WadiahTransaction;
    public function checkDailyLimit(WadiahAccount $account, float $amountRequested): bool;
}
```

---

### 🕌 Modul 21.2: Portal ZIS & Wakaf YBWSA Sekolah (Selesai ✅)

Penyediaan kanal donasi digital khusus untuk seluruh civitas akademika (guru, siswa, orang tua) yang disalurkan langsung ke program YBWSA. Mencakup pelacakan dana tersalurkan dan sertifikat apresiasi elektronik (e-sertifikat).

#### Rencana Implementasi

##### [NEW] Migration: `create_zis_wakaf_tables`
```php
Schema::create('charity_campaigns', function (Blueprint $table) {
    $table->id();
    $table->string('title');                                 // "Wakaf Pembangunan Masjid Sultan Agung"
    $table->enum('type', ['zakat', 'infaq', 'shodaqoh', 'wakaf']);
    $table->text('description');
    $table->decimal('target_amount', 12, 2)->nullable();
    $table->decimal('collected_amount', 12, 2)->default(0);
    $table->date('end_date')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('charity_donations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('charity_campaign_id')->constrained('charity_campaigns')->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained('users'); // NULL jika anonim
    $table->string('donor_name');                            // "Hamba Allah" jika anonim
    $table->decimal('amount', 12, 2);
    $table->enum('payment_method', ['transfer', 'wadiah_wallet', 'cash']);
    $table->enum('status', ['pending', 'verified', 'cancelled'])->default('pending');
    $table->string('evidence_path')->nullable();             // Bukti transfer
    $table->boolean('is_anonymous')->default(false);
    $table->string('receipt_number')->unique();              // ZIS-YYYYMMDD-NNNN
    $table->timestamps();
});
```

##### [NEW] `app/Http/Controllers/Finance/CharityController.php`
```php
class CharityController extends Controller
{
    public function index()                                 // List campaign ZIS & Wakaf aktif bagi publik/siswa
    public function campaignDetail($id)                     // Detail campaign + list donatur terbaru
    public function donateForm($campaignId)                 // Form pengisian donasi
    public function submitDonation(Request $r)              // Kirim donasi (upload bukti / potong saldo wadiah)
    public function manageCampaigns()                       // Admin: CRUD campaign
    public function verifyDonations()                       // Admin: daftar verifikasi pembayaran donasi
    public function approveDonation($id)                    // Admin: verifikasi sukses, generate receipt & e-sertifikat
    public function printReceipt($id)                       // Unduh kuitansi resmi donasi PDF
}
```

---

## 📦 FASE 22: Fitur Baru — Integrasi Dapodik/EMIS & AI Copilot untuk Guru

**Fokus:** Otomatisasi sinkronisasi data sekolah ke sistem Kemendikbud & Kemenag, serta pemanfaatan kecerdasan buatan untuk meningkatkan efisiensi tugas guru.

---

### 🖥️ Modul 22.1: Dapodik & EMIS Sync Mapper (Selesai ✅)

SMA Islam Sultan Agung 1 Semarang harus melaporkan data ganda ke Kemendikbud (Dapodik) dan Kemenag (EMIS). Modul ini menyediakan visual mapper untuk memetakan kolom database lokal ke skema ekspor EMIS/Dapodik, mendeteksi ketidakcocokan data (mismatches), dan memvalidasi kelengkapan data siswa/guru sebelum ekspor.

#### Rencana Implementasi

##### [NEW] Migration: `create_sync_mapping_tables`
```php
Schema::create('sync_configs', function (Blueprint $table) {
    $table->id();
    $table->string('system_target');                         // "dapodik" atau "emis"
    $table->string('local_table');                           // "students", "teachers", "classrooms"
    $table->json('field_mapping');                           // Map local_col => target_col
    $table->timestamps();
});

Schema::create('sync_reports', function (Blueprint $table) {
    $table->id();
    $table->string('system_target');
    $table->timestamp('sync_run_at');
    $table->integer('records_processed');
    $table->integer('records_success');
    $table->integer('records_failed');
    $table->json('error_logs')->nullable();                  // Detail data mana yang gagal beserta alasannya
    $table->foreignId('run_by')->constrained('users');
    $table->timestamps();
});
```

##### [NEW] `app/Http/Controllers/Admin/SyncController.php`
```php
class SyncController extends Controller
{
    public function dashboard()                             // Tampilan status sinkronisasi, log terakhir, widget alert kegagalan
    public function mappingForm($target)                    // Halaman setting pemetaan kolom database
    public function saveMapping(Request $r)                 // Simpan pemetaan kolom
    public function runValidation($target)                  // Audit data lokal: cek NISN kosong, NUPTK salah format, dll
    public function exportData($target, $type)              // Generate file ekspor (.xlsx / .xml / .json) siap unggah
    public function syncHistory()                           // Riwayat laporan ekspor/sinkronisasi
}
```

---

### 🤖 Modul 22.2: AI Teacher Copilot (RPP & CBT Generator) (Selesai ✅)

Asisten AI khusus guru untuk membantu menyusun Rencana Pelaksanaan Pembelajaran (RPP) berbasis Kurikulum Merdeka dan membuat butir soal ujian secara otomatis berdasarkan modul ajar. Karena berjalan di localhost, modul ini akan menyediakan integrasi API key OpenAI/Gemini API yang fleksibel, lengkap dengan local fallback model berbasis aturan (rule-based) jika koneksi internet terputus.

#### Rencana Implementasi

##### [NEW] `app/Services/AiCopilotService.php`
```php
class AiCopilotService
{
    public function generateLessonPlan(string $subject, string $topic, string $gradeLevel, array $competencies): string;
    public function generateExamQuestions(string $materialText, string $type, int $count, string $difficulty): array;
}
```

##### [NEW] `app/Http/Controllers/Academic/AiCopilotController.php`
```php
class AiCopilotController extends Controller
{
    public function index()                                 // Menu dashboard AI Teacher Copilot
    public function lessonPlanForm()                        // Form pembuat RPP otomatis
    public function generateLessonPlan(Request $r)          // Kirim prompt ke AI, dapatkan output markdown RPP
    public function saveLessonPlanToCurriculum(Request $r)  // Simpan hasil RPP ke tabel lesson_plans internal
    public function questionGeneratorForm()                 // Form pembuat soal otomatis (input teks materi / silabus)
    public function generateQuestions(Request $r)           // Kirim materi ke AI, hasilkan butir soal pilihan ganda/esai
    public function importQuestionsToBank(Request $r)       // Masukkan soal hasil generate ke bank soal CBT internal
}
```

##### [NEW] Views
```
resources/views/academic/ai-copilot/
├── index.blade.php              # Navigasi utama AI Copilot
├── lesson-plan.blade.php        # Form input kompetensi inti + area output RPP editor (split-screen)
└── question-generator.blade.php # Generator soal: input teks modul ajar → output grid butir soal dengan pilihan jawaban
```

---

## 📦 FASE 23: Fitur Baru — Manajemen Aset IoT (Smart School Inventory) & Barcode

**Fokus:** Digitalisasi pencatatan aset fisik sekolah menggunakan pelabelan QR Code, integrasi tiket perawatan sarana prasarana, serta integrasi peminjaman buku perpustakaan via scan barcode.

---

### 🏷️ Modul 23.1: QR Code Asset Tracking & Maintenance Ticket (Selesai ✅)

Pelabelan seluruh barang inventaris sekolah (meja, kursi, komputer lab, AC, proyektor) dengan QR Code unik. Guru dan siswa dapat memindai QR Code tersebut untuk melaporkan kerusakan langsung, otomatis menerbitkan tiket perawatan bagi waka sarpras.

#### Rencana Implementasi

##### [MODIFY] Migration: `enhance_assets_for_qr_and_maintenance`
```php
Schema::table('assets', function (Blueprint $table) {
    $table->string('qr_code')->nullable()->unique()->after('code');
});

Schema::create('asset_maintenance_tickets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
    $table->foreignId('reported_by')->constrained('users');
    $table->text('issue_description');
    $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
    $table->enum('status', ['pending', 'investigating', 'repairing', 'resolved', 'scrapped'])->default('pending');
    $table->string('photo_evidence')->nullable();
    $table->text('resolution_notes')->nullable();
    $table->foreignId('resolved_by')->nullable()->constrained('users'); // Petugas sarpras
    $table->timestamp('resolved_at')->nullable();
    $table->timestamps();
});
```

##### [NEW] `app/Http/Controllers/Admin/AssetMaintenanceTicketController.php`
```php
class AssetMaintenanceTicketController extends Controller
{
    public function scanQr($qrCode)                          // Endpoint redirect scan QR: tampilkan info detail aset + tombol lapor
    public function createTicketForm($assetId)               // Form pelaporan masalah
    public function storeTicket(Request $r)                  // Simpan tiket keluhan sarpras
    public function index()                                  // Admin Sarpras: List tiket masuk + filter status/prioritas
    public function editTicket($id)                          // Update progres perbaikan
    public function updateTicketStatus($id, Request $r)      // Ubah status + catatan penyelesaian
    public function generateQrLabels()                       // Cetak label lembaran QR Code massal (PDF grid)
}
```

##### [NEW] Views
```
resources/views/assets/maintenance/
├── scan.blade.php               # Halaman responsif mobile: info barang & form cepat laporkan masalah
├── index.blade.php              # Dashboard Sarpras: Kanban board tiket perbaikan aset
├── ticket-detail.blade.php      # Riwayat kerusakan per barang + status penanganan
└── qr-print.blade.php           # Lembaran cetak stiker QR Code (layout CSS grid print)
```

---

### 📚 Modul 23.2: Barcode & NFC Integrasi Perpustakaan (Selesai ✅)

Peningkatan sirkulasi perpustakaan dengan integrasi barcode scanner untuk mempercepat proses pencatatan peminjaman (borrow) dan pengembalian (return) buku fisik.

#### Rencana Implementasi

##### [MODIFY] `app/Http/Controllers/LibraryController.php`
```php
// Tambahkan method baru untuk sirkulasi cepat:
public function quickCirculationTerminal()               // Terminal sirkulasi cepat perpustakaan
public function processBarcodeBorrow(Request $r)         // Proses pinjam buku via input scan barcode buku + NIS siswa
public function processBarcodeReturn(Request $r)         // Proses kembali buku via input scan barcode buku
```

##### [NEW] Views
```
resources/views/library/
└── quick-terminal.blade.php     # Terminal minimalis: autofokus input scanner, scan siswa → scan buku → selesai (AJAX feedback)
```

---

## 📦 FASE 24: Fitur Baru — Keamanan Sistem, Audit Keuangan & Forensic Log

**Fokus:** Menjamin akuntabilitas pengelolaan keuangan sekolah serta perlindungan data dari serangan siber melalui forensic logging, security alert, dan audit double-entry otomatis.

---

### ⚖️ Modul 24.1: Double-Entry Ledger Auditor & Fraud Detector (Selesai ✅)

Mengingat platform memiliki sistem pencatatan keuangan double-entry (`JournalEntry` & `JournalEntryLine`), modul ini bertindak sebagai auditor internal otomatis. Sistem akan memindai seluruh entri jurnal keuangan secara berkala, memastikan keseimbangan debit-kredit, dan mendeteksi adanya transaksi aneh (misal: pengeluaran tanpa akun kas berpasangan, atau perubahan manual saldo akun di luar entri jurnal).

#### Rencana Implementasi

##### [NEW] `app/Services/FinancialAuditorService.php`
```php
class FinancialAuditorService
{
    public function auditLedgerBalance(): array;             // Deteksi selisih total debit vs kredit global
    public function findUnbalancedEntries(): Collection;      // Cari entri jurnal yang debit & kreditnya tidak balance
    public function detectManualBalanceModifications(): array; // Bandingkan saldo akun kalkulasi historis vs saldo kolom 'balance' riil
    public function runAuditCheck(): array;                  // Eksekusi semua modul audit, kembalikan summary temuan
}
```

##### [NEW] `app/Http/Controllers/Finance/FinancialAuditController.php`
```php
class FinancialAuditController extends Controller
{
    public function index()                                 // Dashboard audit keuangan: status audit terakhir, rekap temuan
    public function runAudit()                              // Jalankan audit langsung, tampilkan log hasil
    public function showUnbalancedJournalDetails($id)       // Tampilkan detail jurnal bermasalah
    public function exportAuditReport()                     // Unduh laporan hasil audit keuangan PDF resmi
}
```

##### [NEW] Views
```
resources/views/finance/audit/
├── index.blade.php              # Dashboard status audit keuangan (badge hijau: Aman, merah: Bahaya/Selisih)
└── report.blade.php             # Tampilan log audit terperinci dan instruksi perbaikan jurnal selisih
```

---

### 🛡️ Modul 24.2: Security Forensic Log & Intrusion Prevention Dashboard (Selesai ✅)

Memonitor aktivitas keamanan sistem secara forensik, melacak detail login mencurigakan (perubahan IP tiba-tiba, user agent tidak wajar), mencatat modifikasi data kritis (misal: pengubahan nilai raport di luar jam kerja), dan menyediakan tombol darurat "Lockdown System" bagi superadmin jika terindikasi adanya serangan.

#### Rencana Implementasi

##### [NEW] Migration: `create_security_forensic_tables`
```php
Schema::create('security_threat_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('event_type');                            // "brute_force", "unauthorized_escalation", "impossible_travel"
    $table->string('severity');                              // "low", "medium", "high", "critical"
    $table->string('ip_address');
    $table->string('user_agent');
    $table->json('payload_data')->nullable();                // Detail request mencurigakan
    $table->boolean('is_resolved')->default(false);
    $table->timestamps();
});

Schema::table('settings', function (Blueprint $table) {
    $table->boolean('is_system_lockdown')->default(false);   // Flag darurat global
});
```

##### [NEW] Middleware: `SystemLockdownMiddleware`
```php
class SystemLockdownMiddleware
{
    public function handle($request, Closure $next)
    {
        // Jika status lockdown aktif, blokir seluruh rute kecuali halaman login admin tertentu
        if (Setting::get('is_system_lockdown', false)) {
            if (!$request->is('admin/security*') && !$request->is('logout')) {
                abort(503, 'Sistem dinonaktifkan sementara karena alasan keamanan.');
            }
        }
        return $next($request);
    }
}
```

##### [NEW] `app/Http/Controllers/Admin/SecurityDashboardController.php`
```php
class SecurityDashboardController extends Controller
{
    public function dashboard()                             // Dashboard keamanan: visualisasi login gagal, aktivitas admin
    public function threatLogs()                            // List log ancaman/ancaman keamanan sistem
    public function toggleLockdown()                        // Aktifkan / nonaktifkan status lockdown darurat sekolah
    public function blockIp(Request $r)                     // Blokir IP address tertentu dari akses web
    public function activeSessions()                        // Daftar session aktif seluruh user saat ini
    public function terminateSession($sessionId)            // Paksa logout salah satu user yang mencurigakan
}
```

##### [NEW] Views
```
resources/views/admin/security/
├── dashboard.blade.php          # Monitor ancaman keamanan real-time, tombol Lockdown merah menyala
└── logs.blade.php               # Forensic audit trail: pencatatan detail siapa mengubah apa, kapan, dan di mana
```

---

## 🧪 Rencana Verifikasi Fase 21-24

### 1. Automated Tests (Unit & Feature)

##### [NEW] Tests
```
tests/Feature/Finance/WadiahSavingsTest.php:
- Setor tunai wadiah menambah saldo dengan benar.
- Tarik tunai memotong saldo & memicu exception jika saldo tidak cukup.
- Batasan limit jajan harian berfungsi menolak transaksi koperasi jika terlampaui.

tests/Feature/Finance/ZisWakafTest.php:
- Pendaftar dapat mengirim donasi ke program YBWSA.
- Admin dapat memverifikasi donasi & kuitansi ZIS terbit dengan nomor unik.

tests/Feature/Academic/SyncDapodikEmisTest.php:
- Menjamin pemetaan kolom dan ekspor XML/JSON file sync lolos validasi skema nasional.

tests/Feature/Academic/AiCopilotTest.php:
- Output RPP & Soal CBT tergenerate dengan format yang valid dan tersimpan di bank soal lokal.

tests/Feature/Admin/AssetMaintenanceTest.php:
- Scanning QR Code mengembalikan informasi aset secara presisi.
- Pengaduan masalah sarpras otomatis menerbitkan tiket maintenance berstatus pending.

tests/Feature/Finance/LedgerAuditTest.php:
- Auditor mendeteksi secara akurat jika entri debit vs kredit jurnal keuangan tidak berimbang (unbalanced).

tests/Feature/Security/ForensicSecurityTest.php:
- Sistem mendeteksi ancaman brute force & menolak akses rute saat lockdown aktif.
```

### 2. Manual Verification
* Akses terminal POS kantin, uji coba transaksi potong saldo dengan NIS siswa yang memiliki saldo cukup vs kurang.
* Jalankan simulasi audit ledger keuangan, sengaja buat satu jurnal tidak berimbang di database, pastikan dashboard audit memunculkan peringatan merah bahaya.
* Tekan tombol "Lockdown System", verifikasi bahwa rute e-learning/SPP siswa langsung memunculkan halaman error 503 Maintenance.

---

## 📦 FASE 25: Blueprint Refaktorisasi & Purifikasi UI/UX — Eliminasi Total Emoji untuk Standar Corporate & Enterprise Professional

**Fokus Utama:** Penghapusan 100% seluruh karakter emoji dari seluruh komponen UI Blade, cetakan dokumen PDF, pesan balasan Controller/Service, notifikasi email/WhatsApp, serta seeder data master. Karakter emoji digantikan secara sistematis menggunakan Ikon Vektor SVG (Heroicons/Lucide), komponen badge Tailwind berspesifikasi enterprise, serta tipografi formal sesuai standar identitas korporat Yayasan Badan Wakaf Sultan Agung (YBWSA).

---

### 🏛️ 25.1: Latar Belakang & Rasionalitas Arsitektural (Selesai ✅)

#### 1. Kebutuhan Standar Enterprise Korporat
Aplikasi **Sistem Terpadu (SISTA)** dikembangkan untuk SMA Islam Sultan Agung 1 Semarang yang berada di bawah naungan yayasan resmi. Penggunaan emotikon/emoji kasual (seperti 🚀, 🕌, 📊, 📝, ✅, ⚠️, 🔥) memberikan kesan informal dan tidak selaras dengan citra kelembagaan pendidikan formal.

#### 2. Inkonsistensi Rendering Lintas Platform (Cross-Platform Discrepancy)
Karakter Unicode Emoji dirender secara berbeda oleh vendor sistem operasi:
- Windows (Segoe UI Emoji) vs macOS/iOS (Apple Color Emoji) vs Android (Noto Color Emoji) vs Linux (Noto/Twitter Emoji).
- Perbedaan warna, bentuk, dan resolusi menyebabkan tampilan antarmuka (UI) tidak konsisten di layar pengguna yang berbeda.
- Beberapa sistem operasi atau browser versi lama merender emoji menjadi karakter kotak rusak `[?]` atau simbol tidak dikenal.

#### 3. Kerusakan Engine Rendering PDF (DomPDF Font Engine Failure)
Mesin pencetak PDF (`dompdf/dompdf`) yang digunakan untuk mencetak Rapor, Kwitansi SPP, Surat Resmi, Slip Gaji, dan Sertifikat Tahfidz **tidak mendukung karakter UTF-8 Multibyte 4-byte (Emoji)** secara native tanpa font khusus yang berukuran sangat besar:
- Keberadaan emoji pada berkas Blade PDF menyebabkan karakter terpotong, muncul tanda tanya `?`, kotak hitam, atau menyebabkan eror kompilasi font `Font_Exception`.

#### 4. Integritas Data & Log Sistem
Menghindari isu encoding database (`utf8mb3` vs `utf8mb4`) pada string flash message session, log audit, dan log WhatsApp, yang berpotensi memotong string teks saat menyimpan ke database jika charset tidak dikonfigurasi dengan sempurna.

---

### 🗺️ 25.2: Pemetaan Komprehensif Lokasi Penggunaan Emoji di Codebase

Audit komprehensif memetakan seluruh titik ditemukannya karakter emoji pada 5 layer utama aplikasi:

| Layer Aplikasi | Lokasi File | Jenis Konten Emoji | Dampak Masalah | Rencana Purifikasi |
| :--- | :--- | :--- | :--- | :--- |
| **Layer 1: Blade UI Views** | `resources/views/components/*.blade.php`<br>`resources/views/**/*.blade.php` | Navigasi sidebar, header halaman, badge status, card widget, tombol aksi | Tampilan kasual & inkonsisten antar OS | Ganti dengan `<x-heroicon-*/>` atau inline SVG + Badge Tailwind |
| **Layer 2: PDF & Print Views** | `resources/views/**/pdf*.blade.php`<br>`resources/views/**/print*.blade.php` | Header dokumen, status pembayaran, penilaian, ikon cetak | Karakter `[?]` / cetakan PDF rusak | Hapus 100% emoji, gunakan styling CSS border/badge murni |
| **Layer 3: Controller & Service** | `app/Http/Controllers/**/*.php`<br>`app/Services/**/*.php` | Flash message (`session()->flash`), Toast notification, Exception text | Text string terpolusi emoji | Hapus prefix emoji pada teks flash message |
| **Layer 4: Notification & Mail** | `app/Notifications/**/*.php`<br>`app/Mail/**/*.php`<br>`app/Services/WhatsappService.php` | Subject email, isi WhatsApp broadcast, notifikasi push | Terlihat seperti spam/chat kasual | Gunakan format pesan formal profesional |
| **Layer 5: Seeder & Data Demo** | `database/seeders/*.php` | Data pengumuman, forum thread, FAQ chatbot, mutabaah items | Data demo terkontaminasi emoji | Pembersihan data master via seeder refactored |

---

### 📐 25.3: Standar Substitusi & UI Design System Replacement Protocol

Untuk menggantikan karakter emoji, ditetapkan 4 protokol substitusi desain visual enterprise:

#### Protocol 1: Substitusi Status Badge (Menggantikan ✅, ⚠️, ❌, ⏳, 🔴, 🟢, 🟡)
Penggunaan emoji status diganti penuh dengan komponen Blade `<x-status-badge>` atau kelas Tailwind CSS pill badge dengan warna semantik yang memenuhi standar kontras WCAG AAA:

```html
<!-- SEBELUM (Kasual dengan Emoji) -->
<span class="badge">✅ Lunas</span>
<span class="badge">⚠️ Terlambat</span>
<span class="badge">❌ Belum Bayar</span>

<!-- SESUDAH (Standar Corporate Enterprise) -->
<!-- Status Sukses / Lunas -->
<span class="inline-flex items-center gap-x-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    Lunas
</span>

<!-- Status Peringatan / Terlambat -->
<span class="inline-flex items-center gap-x-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
    <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    Terlambat
</span>

<!-- Status Gagal / Belum Bayar -->
<span class="inline-flex items-center gap-x-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
    <svg class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
    Belum Dibayar
</span>
```

#### Protocol 2: Substitusi Ikon Modul & Navigasi Sidebar (Menggantikan 🕌, 📊, 📝, 💰, 🎓, 📅, 🏆)
Seluruh emoji navigasi di `resources/views/components/sidebar.blade.php` diganti 100% menggunakan SVG Vektor (Heroicons Outline / Solid 24px) dengan presisi pixel-perfect:

| Modul Navigasi | Emoji Lama | Substitusi Ikon SVG Heroicon | Deskripsi Vektor |
| :--- | :---: | :--- | :--- |
| **Keislaman & Tahfidz** | 🕌 / 📖 | `heroicon-o-book-open` / `heroicon-o-academic-cap` | Vektor Kitab/Al-Qur'an & Kubah Geometris Minimalis |
| **Mutaba'ah Ibadah** | 🌙 / 🤲 | `heroicon-o-sparkles` / `heroicon-o-calendar-days` | Vektor Kalender Keaktifan Ibadah |
| **Keuangan & SPP** | 💰 / 💳 | `heroicon-o-banknotes` / `heroicon-o-credit-card` | Vektor Uang Kertas & Kartu Transaksi Resmi |
| **Akademik & Nilai** | 📊 / 🎓 | `heroicon-o-chart-bar` / `heroicon-o-academic-cap` | Vektor Grafik Batang & Toga Akademik |
| **Presensi KBM** | 📅 / ✅ | `heroicon-o-clipboard-document-check` | Vektor Papan Klip Kehadiran Resmi |
| **Jurnal & Notulensi** | 📝 / 📋 | `heroicon-o-document-text` | Vektor Berkas Dokumen Resmi |
| **Perpustakaan** | 📚 | `heroicon-o-rectangle-stack` | Vektor Tumpukan Katalog Buku |
| **Konseling (BK)** | 💬 | `heroicon-o-chat-bubble-left-right` | Vektor Gelembung Diskusi Resmi |

#### Protocol 3: Substitusi Gamifikasi & Poin Siswa (Menggantikan 🔥, ⭐, 👑, ⚡)
Poin XP, Streak, dan Achievement Siswa diubah dari simbol emoji playful menjadi indikator visual enterprise:
- **Streak Harian (🔥)**: Digantikan dengan komponen visual **"Istiqomah Tracker"** menggunakan indikator hari aktif berwarna emerald-600 (`bg-emerald-600`) dan badge tingkat keaktifan.
- **Badge Achievement (⭐ / 👑)**: Digantikan dengan **Badge Vektor Logam / Tingkat Capaian Resmi** (Tingkat *Mumtaz*, *Jayyid Jiddan*, *Jayyid*) dengan latar belakang gradien halus khas YBWSA (Teal/Emerald).

#### Protocol 4: Purifikasi Cetakan PDF & Laporan Resmi
Seluruh cetakan PDF (DomPDF) mematuhi **Aturan Nol Emoji (Zero-Emoji Policy)**:
- Menggunakan font standar PDF (`Helvetica`, `Times-Roman`, atau `Courier`).
- Penanda status menggunakan teks kapital murni (`LUNAS`, `PROSES`, `PERLU REVISI`) dalam boks batas (border) tipis CSS tanpa gambar/emotikon.

---

### 📋 25.4: Katalog Pemetaan Substitusi Karakter (Complete Emoji Replacement Catalog)

Berikut adalah katalog pemetaan menyeluruh untuk 40+ karakter emoji yang ditemukan di codebase beserta padanan penggantinya:

| Emoji Original | Kategori | Lokasi Temuan Utama | Substitusi Ikon SVG / Teks Formal | Kelas Styling Tailwind / Kode |
| :---: | :--- | :--- | :--- | :--- |
| 🚀 | Header / Launcher | `implementation_plan.md`, Welcome page | `heroicon-o-rocket-launch` / Hapus | `w-5 h-5 text-emerald-600` |
| 📊 | Dashboard / Analytics | Sidebar, Admin Controllers | `heroicon-o-chart-pie` | `w-5 h-5 text-slate-500` |
| 🕌 | Keislaman / Tahfidz | Sidebar, Modul Tahfidz | `heroicon-o-book-open` | `w-5 h-5 text-emerald-600` |
| 🌙 | Mutaba'ah Ibadah | Modul Mutaba'ah | `heroicon-o-moon` / `heroicon-o-sparkles` | `w-5 h-5 text-amber-500` |
| 🎓 | Akademik / Rapor | Modul Rapor, Dashboard Siswa | `heroicon-o-academic-cap` | `w-5 h-5 text-blue-600` |
| 💰 | Keuangan / SPP | Modul Keuangan, Invoicing | `heroicon-o-banknotes` | `w-5 h-5 text-emerald-600` |
| 📝 | Jurnal / Assessment | Modul Penilaian Harian | `heroicon-o-document-edit` | `w-5 h-5 text-slate-600` |
| 📅 | Jadwal / Presensi | Modul Penjadwalan | `heroicon-o-calendar` | `w-5 h-5 text-indigo-600` |
| ✅ | Status Sukses | Flash Session, DataTable, PDF | Teks "Selesai" / "Lunas" + SVG Check | `bg-emerald-100 text-emerald-800` |
| ⚠️ | Status Peringatan | Flash Warning, Smart Alerts | Teks "Peringatan" + SVG Exclamation | `bg-amber-100 text-amber-800` |
| ❌ | Status Gagal / Batal | Flash Error, Modal Confirmation | Teks "Gagal" / "Ditolak" + SVG X | `bg-rose-100 text-rose-800` |
| ⏳ | Status Pending | Status Permohonan, Workflow | Teks "Menunggu" + SVG Clock | `bg-sky-100 text-sky-800` |
| 🔴 | Indicator Critical | Smart Alert, Audit Log | Teks "Tinggi" / Badge Merah | `bg-red-500 rounded-full w-2 h-2` |
| 🟢 | Indicator Active | Status User Online, Session | Teks "Aktif" / Badge Hijau | `bg-emerald-500 rounded-full w-2 h-2` |
| 🟡 | Indicator Medium | Status Warning, Moderasi | Teks "Sedang" / Badge Kuning | `bg-amber-500 rounded-full w-2 h-2` |
| 🔥 | Gamifikasi Streak | Dashboard Siswa, Leaderboard | Teks "Istiqomah" + SVG Bolt | `text-amber-600 font-semibold` |
| ⭐ | Achievement | Rating, Award Siswa | SVG Star Vector | `w-4 h-4 text-amber-400 fill-amber-400` |
| 🏆 | Ranking / Juara | Leaderboard Tahfidz | SVG Trophy Vector | `w-5 h-5 text-amber-500` |
| 📚 | Perpustakaan | Modul Perpustakaan | `heroicon-o-book-open` | `w-5 h-5 text-sky-600` |
| 💬 | Forum / Chatbot | Modul Forum & Chatbot | `heroicon-o-chat-bubble-bottom-center-text` | `w-5 h-5 text-slate-500` |
| 🎒 | Siswa / SPMB | Modul Kesiswaan | `heroicon-o-user-group` | `w-5 h-5 text-blue-500` |
| 👨‍💼 | HRD / Pegawai | Modul Kepegawaian | `heroicon-o-briefcase` | `w-5 h-5 text-slate-700` |
| 🏷️ | Asset IoT / QR | Modul Inventaris Sarpras | `heroicon-o-qr-code` | `w-5 h-5 text-teal-600` |
| 🛡️ | Keamanan / Audit | Modul Security & Audit | `heroicon-o-shield-check` | `w-5 h-5 text-rose-600` |
| 🖥️ | Integrasi System | Modul Sync Dapodik/EMIS | `heroicon-o-arrow-path-rounded-square` | `w-5 h-5 text-cyan-600` |
| 💳 | Cashless / Wadiah | Modul Tabungan Siswa | `heroicon-o-credit-card` | `w-5 h-5 text-emerald-600` |

---

### 🛠️ 25.5: Eksekusi Refaktorisasi & Automasi Pipeline

Proses eliminasi emoji dikerjakan secara sistematis dalam 6 tahap terkontrol:

#### Tahap 1: Eksekusi Script Audit Regex (Audit & Detection)
Menjalankan script pemindai Unicode Emoji berbasis Regex di PowerShell/Bash untuk membuat daftar seluruh berkas dan nomor baris yang mengandung karakter emoji:

```powershell
# Script Pemindai Emoji Codebase SISTA
$emojiRegex = '[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]'
Get-ChildItem -Path ./resources, ./app, ./database -Recurse -Include *.blade.php, *.php | ForEach-Object {
    $matches = Select-String -Path $_.FullName -Pattern $emojiRegex -AllMatches
    if ($matches) {
        Write-Host "Emoji Found in: $($_.FullName)" -ForegroundColor Yellow
    }
}
```

#### Tahap 2: Refaktorisasi Komponen Reusable Blade UI
1. Update `resources/views/components/sidebar.blade.php`: Hapus seluruh emoji dari teks header menu dan link navigasi, ganti dengan komponen SVG Heroicons.
2. Update `resources/views/components/page-header.blade.php`: Hapus prefix emoji dari judul dan deskripsi halaman.
3. Update `resources/views/components/status-badge.blade.php`: Pastikan 100% badge menggunakan ikon SVG dan warna Tailwind semantik.
4. Update `resources/views/components/stat-card.blade.php`: Ganti ikon stat card berbasis emoji dengan SVG Heroicons dinamis.

#### Tahap 3: Cleansing Controller Flash Session & Notification String
Mengedit seluruh file Controller di `app/Http/Controllers/` dan `app/Services/`:
- Mengubah `session()->flash('success', '✅ Data berhasil disimpan')` menjadi `session()->flash('success', 'Data berhasil disimpan.')`.
- Mengubah `session()->flash('error', '❌ Terjadi kesalahan')` menjadi `session()->flash('error', 'Terjadi kesalahan sistem. Silakan coba lagi.')`.

#### Tahap 4: Sanitasi Cetakan PDF & Print Layouts
Memeriksa seluruh berkas cetakan di `resources/views/**/pdf*.blade.php`:
- Hapus semua simbol Unicode emoji.
- Pastikan komponen tabel dan badge PDF menggunakan teks murni dengan style CSS border yang aman untuk engine DomPDF.

#### Tahap 5: Purifikasi Database Seeders & Demo Data
Memperbarui berkas seeder di `database/seeders/`:
- Hapus emoji dari judul pengumuman demo (`AnnouncementSeeder.php`).
- Hapus emoji dari judul thread forum demo (`ForumSeeder.php`).
- Hapus emoji dari FAQ Chatbot (`ChatbotFaqSeeder.php`).
- Hapus emoji dari deskripsi mutabaah & poin pelanggaran.

#### Tahap 6: Pembuatan Automated Architecture Guard Test (Anti-Emoji Governance)
Membuat file test baru `tests/Feature/Architecture/NoEmojiGovernanceTest.php` untuk memastikan tidak ada emoji baru yang masuk ke codebase di masa mendatang:

```php
namespace Tests\Feature\Architecture;

use Tests\TestCase;

class NoEmojiGovernanceTest extends TestCase
{
    /** @test */
    public function views_and_controllers_must_not_contain_emojis()
    {
        $emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u';
        
        $directories = [
            resource_path('views'),
            app_path('Http/Controllers'),
            app_path('Services'),
        ];

        $filesWithEmoji = [];

        foreach ($directories as $dir) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile() && in_array($file->getExtension(), ['php', 'blade.php'])) {
                    $content = file_get_contents($file->getPathname());
                    if (preg_match($emojiPattern, $content)) {
                        $filesWithEmoji[] = str_replace(base_path(), '', $file->getPathname());
                    }
                }
            }
        }

        $this->assertEmpty(
            $filesWithEmoji,
            "Emoji ditemukan di file berikut. Harap ganti dengan ikon SVG / teks formal:\n" . implode("\n", $filesWithEmoji)
        );
    }
}
```

---

### 🧪 25.7: Rencana Verifikasi Purifikasi UI/UX

#### 1. Automated Verification
- Jalankan test governance anti-emoji: `php artisan test --filter=NoEmojiGovernanceTest` (Harus PASS 100%).
- Jalankan seluruh suite pengujian aplikasi: `php artisan test` untuk memastikan tidak ada regresi pada pengujian UI/Feature yang ada.

#### 2. Visual & PDF Inspection
- Membuka halaman Dashboard per role (Admin, Guru, Siswa, Ortu) dan memastikan seluruh navigasi sidebar & header bersih dari emoji.
- Melakukan ekspor PDF Rapor, Kwitansi SPP, dan Slip Gaji untuk memastikan rendering PDF 100% rapi tanpa karakter rusak `[?]`.
- Memeriksa tampilan PWA mobile di layar 375px untuk memastikan kepatuhan standar profesionalisme UI korporat YBWSA.

---

## 📦 FASE 26: Arsitektur Enterprise Lite & High-Availability Technology Standards (Adopsi Teknologi Skala Enterprise)

**Fokus Utama:** Mentransformasi aplikasi web Sistem Terpadu (SISTA) SMA Islam Sultan Agung 1 Semarang dari aplikasi monolithic standar menjadi platform berskala enterprise (Enterprise Lite Edition). Mengadopsi pola arsitektur, infrastruktur, observability, serta keamanan modern yang biasa digunakan oleh platform web layanan berskala besar (high-availability enterprise web applications).

---

### 🏛️ 26.1: Modular Domain-Driven Architecture & Single-School Enterprise Core (Selesai ✅)

Sistem dirancang secara khusus (**Single-School Dedicated Core**) untuk **SMA Islam Sultan Agung 1 Semarang** tanpa kompleksitas *multi-tenant overhead*. Fokus utama adalah pemisahan batas domain bisnis (*Bounded Context*) agar aplikasi monolithic berskala besar ini tetap bersih, mudah dirawat, dan berkinerja tinggi.

- **Single-School Dedicated Core**:
  - Seluruh skema database dan entitas disesuaikan secara khusus untuk struktur operasional tunggal SMA Islam Sultan Agung 1 Semarang (YBWSA).
  - Tanpa isolasi tenant ganda atau *overhead* `tenant_id` untuk memastikan eksekusi query SQL berjalan dengan latensi sekecil mungkin tanpa filter tambahan di setiap klausa `WHERE`.
- **Domain-Driven Modular Boundaries**:
  - Pengelompokan logika domain ke dalam struktur modul internal yang terisolasi rapi:
    - `App\Domains\Academic` (Kurikulum, Rapor, CBT, Penilaian Harian)
    - `App\Domains\Keislaman` (Tahfidz, Mutaba'ah, Presensi Kegiatan Keagamaan)
    - `App\Domains\Finance` (SPP, Wadiah, ZIS/Wakaf YBWSA, Accounting Ledger)
    - `App\Domains\HumanResources` (Pegawai, Presensi GPS, Payroll, Evaluasi Kinerja)
    - `App\Domains\Kesiswaan` (OSIS, MPK, Ekstrakurikuler, BK/Konseling)
- **Dedicated School Branding & Settings Engine**:
  - Pengaturan terpusat eksklusif SMA ISA 1 Semarang di tabel `settings` (Logo YBWSA, Visi-Misi Sekolah, Alamat Wonodri Semarang, Tanda Tangan Digital Kepala Sekolah & Waka).

```php
// app/Domains/Academic/Services/GradeCalculationService.php
namespace App\Domains\Academic\Services;

class GradeCalculationService
{
    public function calculateFinalGrade(int $studentId, int $subjectId): float
    {
        // Logika domain terisolasi khusus akademik SMA ISA 1
    }
}
```

---

### ⚡ 26.2: Asynchronous Event-Driven Architecture & Queue Systems (Selesai ✅)

Mengadopsi pemrosesan latar belakang (asynchronous processing) untuk seluruh operasi berat guna menjamin latensi respon HTTP pengguna tetap di bawah **100ms** (p99).

- **Event Bus & Event Listeners**:
  - `StudentGraduated` → `GenerateAlumniRecord`, `NotifyParentsViaWhatsApp`, `ArchiveElearningAccess`.
  - `PaymentReceived` → `UpdateBillingStatus`, `IssueWadiahReceipt`, `LogJournalEntry`.
  - `ExamCompleted` → `CalculateCbtScore`, `TriggerItemAnalysisJob`.
- **Queue Pipeline & Worker Management**:
  - Pengelompokan Queue berdasarkan prioritas: `high` (WhatsApp, OTP, Flash Alert), `default` (CBT Score, Attendance), `low` (PDF Generation, Report Sync).
  - **Dead Letter Queue (DLQ)** & Automatic Exponential Backoff Retry untuk pekerjaan yang gagal.
  - Dasbor Pemantauan Queue (`QueueMonitorController`) berbasis web di localhost.

---

### 🚀 26.3: Multi-Layer Distributed Caching & Tag Invalidation (Selesai ✅)

Mengadopsi pola caching berlapis (Multi-Tier Caching Strategy) seperti aplikasi enterprise:

1. **Layer 1 - In-Memory Request Cache**: Caching per HTTP Lifecycle menggunakan Singleton Service.
2. **Layer 2 - Shared Key-Value Cache**: Caching data konstan (Mata Pelajaran, Tahun Ajaran, Daftar Ruangan, Peran & Izin User).
3. **Layer 3 - HTTP Response Cache Gateway**: Cache halaman publik statis (Landing Page, Profil Sekolah, Berita CMS) menggunakan header `Cache-Control` & ETag validation.
4. **Cache Invalidation via Tags**:
   - `Cache::tags(['students', 'class-10a'])->flush()` saat ada mutasi siswa.

---

### 🛡️ 26.4: API Gateway Pattern, Rate Limiting & Circuit Breakers

Penerapan kontrol lalu lintas dan ketahanan sistem terhadap kegagalan pihak ketiga (3rd party dependencies):

- **API Gateway & Versioning**:
  - Prefix rute terstruktur `/api/v1/` dan `/api/v2/` dengan API Key Authentication (`Bearer Token` / Sanctum).
- **Rate Limiting (Leaky Bucket / Token Bucket)**:
  - Pembatasan request berbasis peran: Siswa (60 req/min), Guru (120 req/min), Public API (20 req/min), Login Attempt (5 req/min).
- **Circuit Breaker Pattern**:
  - Melindungi aplikasi dari hang/crash ketika layanan eksternal (Fonnte WA Gateway, Midtrans Payment, Kemendikbud Dapodik) lambat atau down. Jika gagal 5x berturut-turut, sistem otomatis mengaktifkan *Fallback Mode* tanpa membuat user menunggu timeout.

---

### 📊 26.5: Enterprise Observability, Telemetry & APM Dashboard

Menyediakan transparansi kesehatan aplikasi tingkat enterprise:

- **Correlation ID (Distributed Tracing)**:
  - Setiap HTTP Request diberikan header unik `X-Correlation-ID` yang diteruskan ke log database, queue job, dan respon API untuk memudahkan penelusuran eror lintas modul (end-to-end tracing).
- **System APM & Health Metrics Dashboard**:
  - Dasbor metrik sistem di `/admin/observability`:
    - Respon Time P50, P90, P99.
    - Database Query Counter & Slow Query Detector (> 200ms).
    - Memory Usage & Storage IO Monitor.
    - SLA & Error Budget Tracker (Target Uptime: 99.9%).

---

### 🔑 26.6: Fine-Grained RBAC, ABAC & Immutable Audit Ledger

Sistem otorisasi dan akuntabilitas tingkat tinggi:

- **Hybrid RBAC + ABAC (Role-Based & Attribute-Based Access Control)**:
  - Otorisasi dinamis berdasarkan kombinasi Peran + Atribut (misal: Guru hanya bisa mengedit nilai mata pelajaran yang ia ampu di kelas tempat ia mengajar pada semester aktif).
- **Immutable Tamper-Evident Audit Trail**:
  - Pencatatan log perubahan data sensitif (Nilai Rapor, Saldo SPP, Perubahan User) dilengkapi nilai Hashing SHA-256 berantai (Merkle Tree / Cryptographic Ledger) untuk mencegah manipulasi data dari tingkat database langsung.
- **Enterprise SSO & OAuth2 / SAML Ready**:
  - Arsitektur terintegrasi dengan Google Workspace for Education & SSO YBWSA Pusat.

---

### 🚩 26.7: Zero-Downtime Feature Flag Engine & Blue-Green Readiness

Manajemen rilis fitur berskala enterprise tanpa downtime:

- **Feature Flag System (`FeatureFlagService`)**:
  - Peluncuran fitur baru secara bertahap (Canary Release / Percentage Rollout) tanpa perlu redeploy kode.
  - Sakelar On/Off instan di admin panel untuk modul opsional (misal: AI Copilot, Cashless Kantin, Maintenance Mode per Modul).
- **Blue-Green Migration Safety**:
  - Aturan migrasi database non-breaking (Expand & Contract Pattern): penambahan kolom baru dibuat nullable/default terlebih dahulu sebelum deprecation kolom lama.

---

### 🔒 26.8: Compliance Perlindungan Data Pribadi (PDPL / GDPR Ready)

Kepatuhan terhadap Undang-Undang Perlindungan Data Pribadi (UU PDP No. 27 Tahun 2022):

- **Encryption at Rest**: Enkripsi kolom sensitif (NIK, Nomor Telepon, Alamat Rumah, Rekam Medis UKS) menggunakan AES-256-GCM.
- **Data Anonymization & Pseudonymization**: Fitur otomatis untuk menyamarkan nama dan identitas siswa saat mengekspor data untuk keperluan riset/statistik eksternal.
- **Automated Data Archival Policy**: Kebijakan otomatis mengarsipkan data histori > 5 tahun ke tabel arsip dingin (*Cold Storage Tables*) untuk menjaga ukuran database utama tetap efisien.

---

## 📋 Urutan Prioritas Implementasi (Updated Juli 2026)

| Prioritas | Modul | Estimasi | Status |
| :---: | :--- | :--- | :---: |
| 🔴 **P0** | 11.3: Penilaian Harian & Remedial | 3-4 hari | ✅ Selesai |
| 🔴 **P0** | 12.1: Penguatan Dashboard per Role | 3-4 hari | ✅ Selesai |
| 🔴 **P0** | 13.1: Mega Seeder — Data Realistis | 3-4 hari | ✅ Selesai |
| 🟠 **P1** | 11.2: Mutaba'ah Ibadah Lengkap | 2-3 hari | ✅ Selesai |
| 🟠 **P1** | 11.1: Tahfidz Komprehensif | 2-3 hari | ✅ Selesai |
| 🟠 **P1** | 11.5: Presensi Kegiatan Sekolah | 2-3 hari | ✅ Selesai |
| 🟠 **P1** | 12.2: Penguatan Jadwal | 2 hari | ✅ Selesai |
| 🟡 **P2** | 11.4: Item Analysis CBT | 2-3 hari | ✅ Selesai |
| 🟡 **P2** | 12.3: Penguatan E-Learning | 2 hari | ✅ Selesai |
| 🟡 **P2** | 12.4: Penguatan Perpustakaan | 2 hari | ✅ Selesai |
| 🟡 **P2** | 12.5: Penguatan Keuangan | 2 hari | ✅ Selesai |
| 🟡 **P2** | 15.1: Notulensi Rapat | 2-3 hari | ✅ Selesai |
| 🟢 **P3** | 15.2: Pengumuman Rich | 1-2 hari | ✅ Selesai |
| 🟢 **P3** | 15.3: Todo List Guru | 1-2 hari | ✅ Selesai |
| 🟢 **P3** | 14.1: UI Consistency Audit | 3-4 hari | ✅ Selesai |
| 🟢 **P3** | 14.2: Animasi & Micro-Interactions | 2 hari | ✅ Selesai |
| 🟢 **P3** | 14.3: Error & Empty State Pages | 1-2 hari | ✅ Selesai |
| 🟢 **P3** | 14.4: Factory & Test Expansion | 3-4 hari | ✅ Selesai |
| 🔴 **P0** | **16.1: Sistem SPMB Online Komprehensif** | **5-7 hari** | ✅ Selesai |
| 🔴 **P0** | **16.2: Overhaul Alumni & Tracer Study** | **3-4 hari** | ✅ Selesai |
| 🟠 **P1** | **16.3: Penguatan HRD & Kepegawaian** | **4-5 hari** | ✅ Selesai |
| 🟠 **P1** | **16.4: Penguatan Ekskul & Kesiswaan** | **3-4 hari** | ✅ Selesai |
| 🟠 **P1** | **16.5: Penguatan OSIS & MPK** | **3-4 hari** | ✅ Selesai |
| 🟡 **P2** | **17.1: Analytics & Predictive Intelligence** | **4-5 hari** | ✅ Selesai |
| 🟡 **P2** | **17.2: AI Chatbot Enhancement** | **2-3 hari** | ✅ Selesai |
| 🟡 **P2** | **17.3: Penguatan UKS & Rekam Medis** | **3-4 hari** | ✅ Selesai |
| 🟡 **P2** | **17.4: Penguatan CMS & Website Publik** | **3-4 hari** | ✅ Selesai |
| 🟠 **P1** | **18.1: WhatsApp Automation & Broadcast** | **3-4 hari** | ✅ Selesai |
| 🟢 **P3** | **18.2: Penguatan Workflow** | **2-3 hari** | ✅ Selesai |
| 🟢 **P3** | **18.3: Penguatan Smart Alert** | **2 hari** | ✅ Selesai |
| 🟡 **P2** | **19.1: Mega Seeder v2** | **3-4 hari** | ✅ Selesai |
| 🟡 **P2** | **19.2: Test Coverage Expansion** | **4-5 hari** | ✅ Selesai |
| 🟢 **P3** | **19.3: Performance Optimization** | **2-3 hari** | ✅ Selesai |
| 🟢 **P3** | **20.1: Landing Page Polish** | **2-3 hari** | ✅ Selesai |
| 🟢 **P3** | **20.2: PWA Enhancement** | **2 hari** | ✅ Selesai |
| 🟢 **P3** | **20.3: Documentation & Dev Portal** | **2-3 hari** | ✅ Selesai |
| 🔴 **P0** | **21.1: Tabungan Wadiah & Cashless Kantin** | **4-5 hari** | ✅ Selesai |
| 🟠 **P1** | **21.2: Portal ZIS & Wakaf YBWSA** | **3-4 hari** | ✅ Selesai |
| 🔴 **P0** | **22.1: Dapodik & EMIS Sync Mapper** | **3-4 hari** | ✅ Selesai |
| 🟠 **P1** | **22.2: AI Teacher Copilot (RPP & CBT)** | **4-5 hari** | ✅ Selesai |
| 🟡 **P2** | **23.1: IoT QR Code Asset Tracking** | **3-4 hari** | ✅ Selesai |
| 🟡 **P2** | **23.2: Barcode & NFC Integrasi Library** | **2-3 hari** | ✅ Selesai |
| 🔴 **P0** | **24.1: Double-Entry Ledger Auditor** | **3-4 hari** | ✅ Selesai |
| 🟢 **P3** | **24.2: Security Dashboard & Intrusion Alert** | **3-4 hari** | ✅ Selesai |
| 🔴 **P0** | **25.1: Purifikasi UI/UX — Eliminasi Total Emoji** | **2-3 hari** | ✅ Selesai |
| 🔴 **P0** | **26.1: Modular Domain Boundaries (Single-School Enterprise Core)** | **3-4 hari** | ✅ Selesai |
| 🟠 **P1** | **26.2: Event-Driven Queue & Background Bus** | **3-4 hari** | ✅ Selesai |
| 🟡 **P2** | **26.3: Distributed Multi-Layer Cache Strategy** | **2-3 hari** | ✅ Selesai |
| 🔴 **P0** | **26.4: API Gateway, Rate Limit & Circuit Breakers**| **3-4 hari** | ⬜ Belum |
| 🟢 **P3** | **26.5: APM Observability, Tracing & Health Dashboard**| **3-4 hari** | ⬜ Belum |
| 🔴 **P0** | **26.6: Immutable Audit Ledger & Enterprise RBAC**| **4-5 hari** | ⬜ Belum |
| 🟢 **P3** | **26.7: Zero-Downtime Feature Flag Engine** | **2-3 hari** | ⬜ Belum |
| 🟠 **P1** | **26.8: PDPL Privacy & Data Anonymization Engine** | **3-4 hari** | ⬜ Belum |

---

## 📊 Statistik Codebase Setelah Semua Fase Selesai (Target Akhir Enterprise Edition)

| Metrik | Sekarang | Target Akhir (Fase 24) | **Target Akhir Enterprise (Fase 26)** |
| :--- | :---: | :---: | :---: |
| **Model Eloquent** | 119 | ~158 | **~175** |
| **Controller** | ~77 | ~105 | **~120** |
| **Service Class** | 39 | ~54 | **~68** |
| **Migration** | 99 | ~118 | **~132** |
| **View Directory** | 61 | ~85 | **~95** |
| **Blade Templates** | ~350 | ~480 | **~540** |
| **Test Files** | ~90 | ~135 | **~160** |
| **Seeder Files** | 30 | ~45 | **~55** |
| **Factory Files** | 29 | ~45 | **~55** |
| **Demo Records** | ~3000 | ~7500+ | **~10.000+** |

---

> [!IMPORTANT]
> Seluruh adopsi arsitektur enterprise di atas dirancang **Enterprise Lite Edition (Localhost Ready)** — dapat dijalankan 100% menggunakan `php artisan serve` + SQLite/Database lokal, tanpa mengharuskan server fisik multi-node atau Kubernetes, namun memiliki kesiapan struktur (architecture readiness) tingkat tinggi untuk portofolio teknologi berstandar industri.

> [!TIP]
> **Urutan Implementasi Fase 26 (Rekomendasi Enterprise):**
> 1. **Modul 26.2 (Event-Driven Queue & Background Bus)** — memisahkan tugas berat dari HTTP Request lifecycle.
> 2. **Modul 26.4 (API Gateway & Rate Limiting)** — mengamankan API endpoint dan mengintegrasikan Circuit Breaker.
> 3. **Modul 26.6 (Immutable Audit Ledger)** — menjamin keamanan dan anti-tamper log transaksi keuangan & akademik.
> 4. **Modul 26.5 (Enterprise Observability & APM)** — menyediakan visibilitas P99 latency dan query slow log.
> 5. **Modul 26.7 (Feature Flag Engine)** — manajemen peluncuran modul baru tanpa redeploy.
