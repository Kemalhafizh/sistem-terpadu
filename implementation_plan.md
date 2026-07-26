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

### 🛡️ 26.4: API Gateway Pattern, Rate Limiting & Circuit Breakers (Selesai ✅)

Penerapan kontrol lalu lintas dan ketahanan sistem terhadap kegagalan pihak ketiga (3rd party dependencies):

- **API Gateway & Versioning**:
  - Prefix rute terstruktur `/api/v1/` dan `/api/v2/` dengan API Key Authentication (`Bearer Token` / Sanctum).
- **Rate Limiting (Leaky Bucket / Token Bucket)**:
  - Pembatasan request berbasis peran: Siswa (60 req/min), Guru (120 req/min), Public API (20 req/min), Login Attempt (5 req/min).
- **Circuit Breaker Pattern**:
  - Melindungi aplikasi dari hang/crash ketika layanan eksternal (Fonnte WA Gateway, Midtrans Payment, Kemendikbud Dapodik) lambat atau down. Jika gagal 5x berturut-turut, sistem otomatis mengaktifkan *Fallback Mode* tanpa membuat user menunggu timeout.

---

### 📊 26.5: Enterprise Observability, Telemetry & APM Dashboard (Selesai ✅)

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

### 🔑 26.6: Fine-Grained RBAC, ABAC & Immutable Audit Ledger (Selesai ✅)

Sistem otorisasi dan akuntabilitas tingkat tinggi:

- **Hybrid RBAC + ABAC (Role-Based & Attribute-Based Access Control)**:
  - Otorisasi dinamis berdasarkan kombinasi Peran + Atribut (misal: Guru hanya bisa mengedit nilai mata pelajaran yang ia ampu di kelas tempat ia mengajar pada semester aktif).
- **Immutable Tamper-Evident Audit Trail**:
  - Pencatatan log perubahan data sensitif (Nilai Rapor, Saldo SPP, Perubahan User) dilengkapi nilai Hashing SHA-256 berantai (Merkle Tree / Cryptographic Ledger) untuk mencegah manipulasi data dari tingkat database langsung.
- **Enterprise SSO & OAuth2 / SAML Ready**:
  - Arsitektur terintegrasi dengan Google Workspace for Education & SSO YBWSA Pusat.

---

### 🚩 26.7: Zero-Downtime Feature Flag Engine & Blue-Green Readiness (Selesai ✅)

Manajemen rilis fitur berskala enterprise tanpa downtime:

- **Feature Flag System (`FeatureFlagService`)**:
  - Peluncuran fitur baru secara bertahap (Canary Release / Percentage Rollout) tanpa perlu redeploy kode.
  - Sakelar On/Off instan di admin panel untuk modul opsional (misal: AI Copilot, Cashless Kantin, Maintenance Mode per Modul).
- **Blue-Green Migration Safety**:
  - Aturan migrasi database non-breaking (Expand & Contract Pattern): penambahan kolom baru dibuat nullable/default terlebih dahulu sebelum deprecation kolom lama.

---

### 🔒 26.8: Compliance Data Privacy Engine (UU PDP - Perlindungan Data Pribadi) (Selesai ✅) (PDPL / GDPR Ready)

Kepatuhan terhadap Undang-Undang Perlindungan Data Pribadi (UU PDP No. 27 Tahun 2022):

- **Encryption at Rest**: Enkripsi kolom sensitif (NIK, Nomor Telepon, Alamat Rumah, Rekam Medis UKS) menggunakan AES-256-GCM.
- **Data Anonymization & Pseudonymization**: Fitur otomatis untuk menyamarkan nama dan identitas siswa saat mengekspor data untuk keperluan riset/statistik eksternal.
- **Automated Data Archival Policy**: Kebijakan otomatis mengarsipkan data histori > 5 tahun ke tabel arsip dingin (*Cold Storage Tables*) untuk menjaga ukuran database utama tetap efisien.

## FASE 27: Enterprise Operational Intelligence, Data Governance & Automated Disaster Resilience

**Fokus Utama:** Menyempurnakan ekosistem Sistem Terpadu (SISTA) SMA Islam Sultan Agung 1 Semarang dengan kapabilitas operasional kelas enterprise tingkat lanjut (Enterprise Suite Edition). Mencakup Data Warehousing & OLAP Reporting Engine, Real-Time Event Streaming & Webhook Hub, Business Process Automation (BPA) No-Code Rule Engine, Disaster Recovery Automated Simulator, serta Keamanan MFA & Hijacking Prevention.

---

### 27.1: Automated Database Snapshot, Point-In-Time Recovery (PITR) & Disaster Recovery Simulator (Selesai ✅)

Menyediakan ketahanan bencana data (*Disaster Recovery & Data Integrity*) tingkat enterprise. Fitur backup pipeline otomatis berjalan di background queue, dilengkapi verifikasi integritas SHA-256 dan mekanisme pemulihan *point-in-time* sekali klik.

#### Skema Migrasi

```php
Schema::create('data_snapshots', function (Blueprint $table) {
    $table->id();
    $table->string('label')->index();
    $table->string('file_path');
    $table->unsignedBigInteger('file_size_bytes');
    $table->string('checksum_sha256')->nullable();
    $table->enum('type', ['full', 'incremental'])->default('full');
    $table->enum('status', ['pending', 'completed', 'failed', 'verified'])->default('pending');
    $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
    $table->json('metadata')->nullable();
    $table->timestamps();
});
```

#### Spesifikasi Service (`DisasterRecoveryService`)

```php
namespace App\Services;

class DisasterRecoveryService
{
    public function createSnapshot(string $label, string $type, int $userId): \App\Models\DataSnapshot;
    public function verifyChecksum(\App\Models\DataSnapshot $snapshot): bool;
    public function restoreSnapshot(int $snapshotId): bool;
    public function runIntegrityAudit(): array;
    public function listSnapshots(array $filters): \Illuminate\Pagination\LengthAwarePaginator;
    public function purgeExpiredSnapshots(int $retentionDays = 30): int;
}
```

#### Spesifikasi Controller (`DisasterRecoveryController`)

```php
namespace App\Http\Controllers\Admin;

class DisasterRecoveryController extends Controller
{
    public function dashboard();
    public function createSnapshot(Request $request);
    public function downloadSnapshot(int $snapshotId);
    public function verifySnapshot(int $snapshotId);
    public function restoreSnapshot(Request $request, int $snapshotId);
    public function integrityAudit();
}
```

#### Struktur View

```
resources/views/disaster-recovery/
├── dashboard.blade.php          # Dasbor manajemen backup & restore
└── snapshots-list.blade.php     # Daftar snapshot dengan status & aksi
```

---

### 27.2: Enterprise Data Warehouse (EDW) Schema & OLAP Aggregation Engine (Selesai ✅)

Memisahkan kueri transaksi harian (OLTP) dari kueri analitik eksekutif (OLAP) melalui tabel agregasi pra-kalkulasi (*Materialized Data Marts*) untuk menjamin latensi dasbor analisis eksekutif tetap instan tanpa membebani tabel operasional utama.

#### Skema Migrasi

```php
Schema::create('analytical_daily_financials', function (Blueprint $table) {
    $table->id();
    $table->date('date')->unique();
    $table->decimal('total_spp_received', 15, 2)->default(0);
    $table->decimal('total_wadiah_deposits', 15, 2)->default(0);
    $table->decimal('total_wadiah_withdrawals', 15, 2)->default(0);
    $table->decimal('total_zis_donations', 15, 2)->default(0);
    $table->decimal('cash_balance', 15, 2)->default(0);
    $table->integer('records_count')->default(0);
    $table->timestamps();
});

Schema::create('analytical_student_kpi', function (Blueprint $table) {
    $table->id();
    $table->date('date');
    $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
    $table->decimal('attendance_percentage', 5, 2)->default(0);
    $table->decimal('average_grade', 5, 2)->default(0);
    $table->decimal('mutabaah_score', 5, 2)->default(0);
    $table->integer('tahfidz_pages_memorized')->default(0);
    $table->integer('violation_points')->default(0);
    $table->integer('counseling_sessions')->default(0);
    $table->timestamps();
    $table->unique(['date', 'student_id']);
});

Schema::create('analytical_employee_performance', function (Blueprint $table) {
    $table->id();
    $table->date('date');
    $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
    $table->decimal('attendance_score', 5, 2)->default(0);
    $table->decimal('punctuality_score', 5, 2)->default(0);
    $table->integer('tasks_completed')->default(0);
    $table->integer('tasks_pending')->default(0);
    $table->integer('leave_days_used')->default(0);
    $table->timestamps();
    $table->unique(['date', 'employee_id']);
});
```

#### Spesifikasi Service (`EdwService`)

```php
namespace App\Services;

class EdwService
{
    public function aggregateDailyFinancials(\Carbon\Carbon $date): void;
    public function aggregateStudentKpis(\Carbon\Carbon $date): void;
    public function aggregateEmployeePerformance(\Carbon\Carbon $date): void;
    public function refreshAllMarts(\Carbon\Carbon $date): array;
    public function getExecutiveFinancialSummary(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array;
}
```

#### Artisan Command & Scheduled Job

```php
// app/Console/Commands/RefreshAnalyticalMarts.php
class RefreshAnalyticalMarts extends Command
{
    protected $signature = 'edw:refresh-marts {--date=}';
    // Dijadwalkan berjalan setiap tengah malam via $schedule->command('edw:refresh-marts')->daily();
}
```

#### Spesifikasi Controller (`EdwDashboardController`)

```php
namespace App\Http\Controllers\Admin;

class EdwDashboardController extends Controller
{
    public function executiveDashboard(Request $request);    // Rekap finansial & KPI eksekutif
    public function studentAnalytics(Request $request);      // Analitik per siswa / per kelas
    public function financialAnalytics(Request $request);    // Trend penerimaan SPP, Wadiah, ZIS
}
```

#### Struktur View

```
resources/views/edw/
├── executive-dashboard.blade.php   # Dasbor eksekutif Kepala Sekolah
├── student-analytics.blade.php     # Visualisasi KPI akademik siswa
└── financial-analytics.blade.php   # Grafik trend keuangan harian/bulanan
```

---

### 27.3: Real-Time Event Streaming Bus & Webhook Management Hub (Selesai ✅)

Infrastruktur integrasi acara (*Event-Driven Outbound Webhook*) kelas enterprise untuk menghubungkan SISTA dengan sistem eksternal (yayasan YBWSA Pusat, portal Kemenag, gateway perbankan). Payload JSON terenkripsi dilengkapi tanda tangan kriptografi HMAC-SHA256 (`X-Sista-Signature`).

#### Skema Migrasi

```php
Schema::create('webhook_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->string('url');
    $table->string('secret_key');
    $table->json('events');         // ["student.registered", "billing.paid", "grade.published"]
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_triggered_at')->nullable();
    $table->integer('failure_count')->default(0);
    $table->timestamps();
});

Schema::create('webhook_delivery_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('webhook_subscription_id')->constrained()->onDelete('cascade');
    $table->string('event_type');
    $table->json('payload');
    $table->integer('response_status_code')->nullable();
    $table->text('response_body')->nullable();
    $table->timestamp('delivered_at')->nullable();
    $table->integer('duration_ms')->nullable();
    $table->integer('attempts')->default(1);
    $table->timestamps();
});
```

#### Spesifikasi Service (`WebhookManager`)

```php
namespace App\Services;

class WebhookManager
{
    public function registerSubscription(array $data): \App\Models\WebhookSubscription;
    public function dispatchEvent(string $eventName, array $payload): void;
    public function generateSignature(string $payload, string $secret): string;  // HMAC-SHA256
    public function logDelivery(...): void;
}
```

#### Spesifikasi Controller (`WebhookController`)

```php
namespace App\Http\Controllers\Admin;

class WebhookController extends Controller
{
    public function index();                           // Daftar webhook terdaftar
    public function create();                          // Form registrasi webhook baru
    public function store(Request $request);
    public function edit(int $id);
    public function update(Request $request, int $id);
    public function destroy(int $id);
    public function testWebhook(Request $request, int $id);   // Kirim test payload
    public function replayDelivery(int $logId);               // Replay pengiriman gagal
    public function deliveryLogs(int $id);                    // Log pengiriman per webhook
}
```

#### Struktur View

```
resources/views/webhooks/
├── index.blade.php     # Manajemen webhook subscriptions
├── create.blade.php    # Form registrasi URL & event filter
└── logs.blade.php      # Log pengiriman & replay console
```

---

### 27.4: Advanced Multi-Factor Security (MFA/2FA) & Session Hijacking Prevention (Selesai ✅)

Peningkatan lapisan keamanan identitas kelas enterprise: TOTP Two-Factor Authentication, Session Fingerprinting untuk deteksi hijacking, dan Step-Up Elevation Safeguard untuk tindakan berdampak tinggi.

#### Skema Migrasi

```php
Schema::create('user_two_factor_secrets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->text('secret_key');           // Encrypted
    $table->text('recovery_codes');       // JSON Encrypted
    $table->boolean('is_enabled')->default(false);
    $table->timestamp('confirmed_at')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamps();
});

Schema::create('session_fingerprints', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->string('session_id')->index();
    $table->string('ip_address');
    $table->string('ip_subnet_hash')->nullable();
    $table->string('user_agent_hash');
    $table->string('device_fingerprint')->nullable();
    $table->string('location_approximation')->nullable();
    $table->boolean('is_trusted')->default(false);
    $table->timestamp('flagged_at')->nullable();
    $table->timestamps();
});
```

#### Spesifikasi Service (`TwoFactorAuthService` & `SessionFingerprintService`)

```php
namespace App\Services;

class TwoFactorAuthService
{
    public function generateSecret(int $userId): array;
    public function verifyCode(int $userId, string $code): bool;
    public function generateRecoveryCodes(int $userId): array;
    public function enable(int $userId, string $code): bool;
    public function disable(int $userId): bool;
}

class SessionFingerprintService
{
    public function createFingerprint(int $userId, string $sessionId, Request $request): SessionFingerprint;
    public function validateFingerprint(string $sessionId, Request $request): bool;
    public function detectAnomaly(int $userId, Request $request): bool;
    public function freezeSession(string $sessionId): void;
    public function trustDevice(int $fingerprintId): bool;
}
```

#### Spesifikasi Controller

```php
// app/Http/Controllers/Auth/TwoFactorController.php
class TwoFactorController extends Controller
{
    public function setup();                       // Halaman setup QR Code TOTP
    public function confirm(Request $request);     // Konfirmasi kode pertama kali
    public function verify(Request $request);      // Verifikasi kode saat login
    public function disable(Request $request);     // Nonaktifkan 2FA
    public function showRecoveryCodes();            // Tampilkan recovery codes
}

// app/Http/Controllers/Auth/SessionSecurityController.php
class SessionSecurityController extends Controller
{
    public function activeSessions();                          // Daftar sesi aktif
    public function revokeSession(Request $request, int $id);  // Paksa logout sesi
    public function trustDevice(Request $request, int $id);    // Tandai perangkat terpercaya
}
```

#### Middleware: `StepUpAuthMiddleware`
Verifikasi ulang kata sandi / 2FA untuk tindakan berdampak tinggi: Pencairan Saldo Wadiah, Finalisasi Nilai Rapor, Perubahan Peran User, System Lockdown.

#### Struktur View

```
resources/views/auth/
├── two-factor-setup.blade.php       # Setup 2FA dengan QR Code
└── two-factor-challenge.blade.php   # Halaman verifikasi kode TOTP saat login

resources/views/security/
└── active-sessions.blade.php        # Daftar sesi aktif dengan info perangkat & lokasi
```

---

### 27.5: Enterprise Business Process Automation (BPA) & Dynamic Rule Engine (Selesai ✅)

Mesin otomatisasi aturan bisnis tanpa kode (*No-Code Business Rule Engine*) yang memungkinkan manajemen sekolah mengonfigurasi alur kerja otomatis berbasis kondisi di 5 domain: Academic, Finance, HR, Kesiswaan, Keislaman.

#### Skema Migrasi

```php
Schema::create('business_rules', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->enum('domain', ['academic', 'finance', 'hr', 'kesiswaan', 'keislaman']);
    $table->string('trigger_event');
    $table->json('conditions');
    $table->json('actions');
    $table->boolean('is_active')->default(true);
    $table->integer('priority')->default(0);
    $table->integer('execution_count')->default(0);
    $table->timestamp('last_triggered_at')->nullable();
    $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
    $table->timestamps();
});

Schema::create('business_rule_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('business_rule_id')->constrained()->onDelete('cascade');
    $table->json('trigger_data');
    $table->json('actions_executed');
    $table->enum('result', ['success', 'partial', 'failed']);
    $table->text('error_message')->nullable();
    $table->timestamp('executed_at');
    $table->timestamps();
});
```

#### Contoh Aturan Bisnis Otomatis

| Kondisi | Aksi Otomatis |
| :--- | :--- |
| Presensi Siswa < 80% DAN Status Tahfidz == Behind | Terbitkan Tiket BK & Kirim WA ke Orang Tua |
| Keterlambatan Pegawai > 3x/Bulan | Terapkan Potongan Penyesuaian di Payroll |
| Poin Pelanggaran Siswa > 50 | Terbitkan SP-1 & Notifikasi Wali Kelas |
| Tunggakan SPP > 30 Hari | Kirim WA Pengingat & Flag Dashboard Keuangan |

#### Spesifikasi Service (`BusinessRuleEngine`)

```php
namespace App\Services;

class BusinessRuleEngine
{
    public function evaluate(string $triggerEvent, array $payload): void;
    public function executeActions(array $actions, array $payload): array;
    public function matchConditions(array $conditions, array $payload): bool;
    public function logExecution(int $ruleId, array $triggerData, array $actionsExecuted, string $result, ?string $errorMsg): void;
}
```

#### Spesifikasi Controller (`BusinessRuleController`)

```php
namespace App\Http\Controllers\Admin;

class BusinessRuleController extends Controller
{
    public function index(Request $request);
    public function create();
    public function store(Request $request);
    public function edit(int $id);
    public function update(Request $request, int $id);
    public function toggleActive(int $id);
    public function executionLogs(int $id);
    public function testRule(Request $request, int $id);   // Simulasi eksekusi aturan
}
```

#### Struktur View

```
resources/views/business-rules/
├── index.blade.php     # Daftar aturan bisnis aktif/nonaktif
├── create.blade.php    # Builder kondisi & aksi (UI form dinamis)
└── logs.blade.php      # Log eksekusi aturan dengan detail hasil
```

---

### 27.6: Enterprise Vendor SLA Monitor & Service Level Tracker (Selesai ✅)

Sistem pemantauan kualitas layanan penyedia pihak ketiga (*Vendor SLA Tracking*) untuk memastikan kepatuhan kontrak ketersediaan layanan eksternal.

#### Skema Migrasi

```php
Schema::create('vendor_services', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('base_url');
    $table->string('health_check_endpoint')->nullable();
    $table->integer('expected_status_code')->default(200);
    $table->decimal('sla_target_uptime_percent', 5, 2)->default(99.90);
    $table->enum('category', ['payment', 'messaging', 'government', 'integration']);
    $table->boolean('is_monitored')->default(true);
    $table->timestamps();
});

Schema::create('vendor_health_checks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vendor_service_id')->constrained()->onDelete('cascade');
    $table->enum('status', ['up', 'down', 'degraded']);
    $table->integer('response_time_ms')->nullable();
    $table->integer('status_code')->nullable();
    $table->text('error_message')->nullable();
    $table->timestamp('checked_at');
    $table->timestamps();
});
```

#### Vendor Terpantau

| Vendor | Kategori | Health Endpoint |
| :--- | :--- | :--- |
| Fonnte WhatsApp Gateway | Messaging | `/api/health` |
| Midtrans Payment Gateway | Payment | `/v2/ping` |
| Kemendikbud Dapodik | Government | `/api/v1/status` |
| Kemenag EMIS | Government | `/api/health` |

#### Spesifikasi Service (`VendorSlaService`)

```php
namespace App\Services;

class VendorSlaService
{
    public function checkHealth(\App\Models\VendorService $vendor): void;
    public function calculateUptime(int $vendorId, Carbon $startDate, Carbon $endDate): float;
    public function calculateP95Latency(int $vendorId, Carbon $startDate, Carbon $endDate): int;
    public function calculateP99Latency(int $vendorId, Carbon $startDate, Carbon $endDate): int;
    public function generateMonthlyReport(int $month, int $year): array;
}
```

#### Spesifikasi Controller (`VendorSlaController`)

```php
namespace App\Http\Controllers\Admin;

class VendorSlaController extends Controller
{
    public function dashboard();                              // Scorecard semua vendor
    public function addVendor(Request $request);
    public function editVendor(Request $request, int $id);
    public function healthHistory(int $id);                   // Riwayat uptime per vendor
    public function monthlyReport(Request $request);          // Laporan SLA bulanan
}
```

#### Struktur View

```
resources/views/vendor-sla/
├── dashboard.blade.php       # Scorecard uptime & latency semua vendor
├── vendor-detail.blade.php   # Grafik riwayat kesehatan per vendor
└── monthly-report.blade.php  # Laporan kepatuhan SLA bulanan
```

---

## FASE 28: Enterprise Communication, Collaboration & Digital Workspace

**Fokus Utama:** Membangun platform komunikasi dan kolaborasi digital internal kelas enterprise untuk seluruh warga sekolah. Mengadopsi arsitektur Event-Driven untuk pengiriman pesan real-time dan notifikasi asinkron.

---

### 28.1: Unified Notification Center & Enterprise Inbox (Selesai ✅)

Multi-channel notification hub terpusat (In-App Bell, Push Notification, Email Digest, WhatsApp) dengan preferensi per pengguna dan pola Publish-Subscribe via Background Queue.

#### Skema Migrasi

```php
Schema::create('notification_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('event_category');
    $table->boolean('channel_inapp')->default(true);
    $table->boolean('channel_email')->default(true);
    $table->boolean('channel_whatsapp')->default(false);
    $table->boolean('channel_push')->default(false);
    $table->timestamps();
    $table->unique(['user_id', 'event_category']);
});

Schema::create('notification_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->text('body');
    $table->enum('channel', ['inapp', 'email', 'whatsapp', 'push']);
    $table->string('event_type');
    $table->timestamp('read_at')->nullable();
    $table->string('action_url')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
});
```

#### Spesifikasi Service (`NotificationDispatchService`)

```php
namespace App\Services\Communication;

class NotificationDispatchService
{
    public function dispatch(int $userId, string $eventType, string $title, string $body, array $metadata = []): void;
    public function dispatchBulk(array $userIds, string $eventType, string $title, string $body): void;
    public function markAsRead(int $notificationId, int $userId): bool;
    public function markAllRead(int $userId): int;
    public function getUserPreferences(int $userId): Collection;
    public function updatePreferences(int $userId, array $preferences): void;
}
```

#### Spesifikasi Controller (`NotificationCenterController`)

```php
namespace App\Http\Controllers\Communication;

class NotificationCenterController extends Controller
{
    public function inbox(Request $request);                 // Daftar notifikasi dengan filter
    public function markRead(Request $request, int $id);     // Mark single as read (AJAX)
    public function markAllRead(Request $request);           // Mark all as read
    public function preferences(Request $request);           // Halaman pengaturan preferensi
    public function updatePreferences(Request $request);     // Simpan preferensi
    public function unreadCount(Request $request);           // API: badge counter via Alpine.js polling
}
```

#### Struktur View

```
resources/views/notifications/
├── inbox.blade.php          # Daftar notifikasi dengan filter unread/all
└── preferences.blade.php    # Toggle switch UI preferensi channel per event
```

---

### 28.2: Internal Messaging & Threaded Discussion System (Selesai ✅)

Sistem pesan internal dengan model percakapan berulir (threaded conversation), mendukung komunikasi langsung Guru-Guru, Guru-WaliMurid, Admin-Anyone, dilengkapi read receipts dan lampiran file.

#### Skema Migrasi

```php
Schema::create('conversations', function (Blueprint $table) {
    $table->id();
    $table->enum('type', ['direct', 'group']);
    $table->string('subject')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});

Schema::create('conversation_participants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamp('last_read_at')->nullable();
    $table->boolean('is_muted')->default(false);
    $table->timestamps();
    $table->unique(['conversation_id', 'user_id']);
});

Schema::create('messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
    $table->foreignId('sender_id')->nullable()->constrained('users');
    $table->text('body');
    $table->string('attachment_path')->nullable();
    $table->boolean('is_system_message')->default(false);
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`MessagingService`**: `createConversation`, `sendMessage`, `getConversations`, `getMessages`, `markAsRead`
- **`MessagingController`**: `index` (split-panel UI), `show`, `createConversation`, `sendMessage`, `search`

#### Struktur View

```
resources/views/messaging/
├── index.blade.php      # Split-panel: sidebar conversation list + message thread
└── compose.blade.php    # Form pesan baru dengan user autocomplete
```

---

### 28.3: Digital Signature & E-Stamp Document Workflow (Selesai ✅)

Sistem tanda tangan digital terenkripsi (E-Stamp) untuk dokumen resmi institusi dengan verifikasi QR Code publik.

#### Skema Migrasi

```php
Schema::create('document_templates', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('category');
    $table->string('blade_template_path');
    $table->json('required_signers');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('document_signatures', function (Blueprint $table) {
    $table->id();
    $table->string('document_type');
    $table->unsignedBigInteger('document_id');
    $table->foreignId('signer_id')->constrained('users');
    $table->string('signer_role');
    $table->string('signature_image_path')->nullable();
    $table->timestamp('signed_at')->nullable();
    $table->string('qr_verification_code')->unique();
    $table->string('ip_address')->nullable();
    $table->timestamps();
    $table->index(['document_type', 'document_id']);
});
```

#### Spesifikasi Service & Controller

- **`DigitalSignatureService`**: `requestSignature`, `sign`, `verify`, `generateVerificationQR`, `getSignatureHistory`
- **`DocumentSignatureController`**: `pendingDocuments`, `signDocument`, `verifySignature` (public), `signatureHistory`

#### Struktur View

```
resources/views/documents/
├── pending-signatures.blade.php   # Antrean dokumen menunggu tanda tangan
├── sign.blade.php                 # Preview dokumen + tombol Sign
└── verify-public.blade.php        # Halaman publik verifikasi QR (tanpa auth)
```

---

### 28.4: Enterprise Calendar & Resource Booking System (Selesai ✅)

Unified Calendar System dan Resource Manager untuk mencegah bentrok jadwal penggunaan aset sekolah (Lab, Aula, Ruang Meeting, Masjid).

#### Skema Migrasi

```php
Schema::create('calendar_events', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->enum('event_type', ['academic', 'meeting', 'ceremony', 'holiday', 'exam', 'extracurricular']);
    $table->dateTime('start_datetime');
    $table->dateTime('end_datetime');
    $table->boolean('is_all_day')->default(false);
    $table->string('location')->nullable();
    $table->foreignId('organizer_id')->constrained('users');
    $table->enum('visibility', ['public', 'staff', 'private'])->default('public');
    $table->string('recurrence_rule')->nullable();
    $table->string('color_hex', 7)->default('#3B82F6');
    $table->timestamps();
});

Schema::create('calendar_event_attendees', function (Blueprint $table) {
    $table->id();
    $table->foreignId('calendar_event_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->enum('rsvp_status', ['pending', 'accepted', 'declined'])->default('pending');
    $table->timestamps();
});

Schema::create('bookable_resources', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->enum('type', ['room', 'facility', 'equipment']);
    $table->integer('capacity')->nullable();
    $table->string('location')->nullable();
    $table->text('description')->nullable();
    $table->boolean('is_available')->default(true);
    $table->timestamps();
});

Schema::create('resource_bookings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bookable_resource_id')->constrained()->cascadeOnDelete();
    $table->foreignId('booked_by')->constrained('users');
    $table->string('purpose');
    $table->dateTime('start_datetime');
    $table->dateTime('end_datetime');
    $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`CalendarService`**: `createEvent`, `updateEvent`, `getEventsForRange`, `checkConflicts`, `syncToGoogleCalendar`
- **`ResourceBookingService`**: `checkAvailability`, `createBooking`, `approveBooking`, `rejectBooking`, `getBookingSchedule`
- **`CalendarController`**: `index` (FullCalendar.js), `createEvent`, `editEvent`, `deleteEvent`
- **`ResourceBookingController`**: `index`, `availabilityCheck`, `createBooking`, `approveBooking`, `myBookings`

#### Struktur View

```
resources/views/calendar/
├── index.blade.php              # Integrasi FullCalendar.js navigasi bulanan/mingguan
└── create-event.blade.php       # Form event dengan Alpine.js recurrence options

resources/views/resources/
├── booking-index.blade.php      # Daftar resource dengan ketersediaan visual
└── availability.blade.php       # Timeline view per ruangan
```

---

## FASE 29: Enterprise Content Delivery, Media Management & Custom Report Engine

**Fokus Utama:** Infrastruktur pengelolaan konten, aset media digital (DAM), ekstraksi data cerdas (IDP/OCR), dan mesin laporan kustom drag-and-drop kelas enterprise.

---

### 29.1: Centralized Digital Asset Management (DAM) (Selesai ✅)

Repositori aset digital tersentralisasi dengan versioning, metadata JSON, tagging hirarkis, dan folder management.

#### Skema Migrasi

```php
Schema::create('media_folders', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('parent_id')->nullable()->constrained('media_folders')->cascadeOnDelete();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});

Schema::create('media_assets', function (Blueprint $table) {
    $table->id();
    $table->string('filename');
    $table->string('original_filename');
    $table->string('mime_type');
    $table->unsignedBigInteger('file_size_bytes');
    $table->string('storage_path');
    $table->string('thumbnail_path')->nullable();
    $table->foreignId('uploaded_by')->constrained('users');
    $table->foreignId('folder_id')->nullable()->constrained('media_folders');
    $table->json('tags')->nullable();
    $table->json('metadata')->nullable();
    $table->integer('version')->default(1);
    $table->boolean('is_public')->default(false);
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`MediaAssetService`**: `upload`, `delete`, `moveToFolder`, `createFolder`, `generateThumbnail`, `getByTag`, `search`, `getStorageUsage`
- **`MediaAssetController`**: `index`, `upload` (Dropzone), `delete`, `createFolder`, `moveToFolder`, `search`, `storageStats`

#### Struktur View

```
resources/views/media/
├── library.blade.php          # File manager UI grid/list view (mirip Google Drive)
├── upload.blade.php           # Komponen dropzone drag-and-drop
└── folder-browser.blade.php   # Sidebar tree-view navigasi folder
```

---

### 29.2: Enterprise Report Builder & Custom Report Designer (Selesai ✅)

Builder laporan drag-and-drop untuk manajemen tingkat atas (Kepala Sekolah, Bendahara) dengan kapabilitas penjadwalan otomatis dan multi-format export.

#### Skema Migrasi

```php
Schema::create('custom_reports', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('data_source');
    $table->json('columns_config');
    $table->json('filters_config');
    $table->json('sort_config');
    $table->enum('chart_type', ['table', 'bar', 'line', 'pie', 'none'])->default('table');
    $table->foreignId('created_by')->constrained('users');
    $table->boolean('is_shared')->default(false);
    $table->string('schedule_cron')->nullable();
    $table->timestamp('last_generated_at')->nullable();
    $table->timestamps();
});

Schema::create('report_exports', function (Blueprint $table) {
    $table->id();
    $table->foreignId('custom_report_id')->nullable()->constrained()->nullOnDelete();
    $table->enum('format', ['pdf', 'xlsx', 'csv']);
    $table->string('file_path');
    $table->foreignId('generated_by')->nullable()->constrained('users');
    $table->unsignedBigInteger('file_size_bytes');
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`ReportBuilderService`**: `buildQuery`, `executeReport`, `exportToPdf`, `exportToExcel`, `exportToCsv`, `scheduleReport`
- **`ReportBuilderController`**: `index`, `create`, `store`, `preview`, `export`, `scheduleReport`, `sharedReports`

#### Struktur View

```
resources/views/reports/
├── builder-index.blade.php    # Daftar laporan tersimpan & template
├── designer.blade.php         # Drag-and-drop kolom (Alpine.js / SortableJS)
├── preview.blade.php          # Pratinjau data tabular dan chart instan
└── export-history.blade.php   # Log unduhan/generasi arsip
```

---

### 29.3: Intelligent Document Processing (IDP) & OCR Engine (Selesai ✅)

Ekstraksi semi-otomatis informasi identitas dari dokumen ter-scan (KTP, KK, Ijazah) untuk akselerasi entri data PPDB/SPMB.

#### Skema Migrasi

```php
Schema::create('ocr_documents', function (Blueprint $table) {
    $table->id();
    $table->string('original_file_path');
    $table->enum('document_type', ['ktp', 'kk', 'akta', 'ijazah', 'other']);
    $table->json('extracted_data')->nullable();
    $table->float('confidence_score')->nullable();
    $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
    $table->foreignId('processed_by')->constrained('users');
    $table->timestamp('processed_at')->nullable();
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`OcrProcessingService`**: `processDocument`, `extractFields`, `mapToStudentProfile`, `validateExtraction`
- **`OcrDocumentController`**: `upload`, `process`, `reviewExtraction`, `applyToRegistration`

#### Struktur View

```
resources/views/ocr/
├── upload.blade.php              # Batch upload dokumen pendaftaran
└── review-extraction.blade.php   # Side-by-side: gambar di kiri, form auto-filled di kanan
```

---

### 29.4: Multi-Format Export Engine & Data Interoperability (Selesai ✅)

Mesin ekspor batch universal mendukung PDF/A, Excel XLSX, CSV, JSON-LD, dan XML (Dapodik/EMIS compatible) dengan background queue untuk dataset besar.

#### Spesifikasi Service & Controller

- **`ExportEngineService`**: `exportToPdfA`, `exportToXlsx`, `exportToCsv`, `exportToJsonLd`, `exportToXml`, `queueLargeExport`
- **`ExportController`**: `selectFormat`, `generateExport`, `downloadExport`, `exportHistory`

#### Struktur View

```
resources/views/exports/
├── format-selector.blade.php   # Modal pemilihan format & rentang data
└── history.blade.php           # Monitoring progres ekspor dengan auto-refresh
```

---

## FASE 30: Enterprise Governance, Risk & Compliance (GRC) & Accreditation Readiness

**Fokus Utama:** Kerangka kerja tata kelola, manajemen risiko, kepatuhan regulasi, dan kesiapan akreditasi sekolah kelas enterprise berdasarkan 8 Standar Nasional Pendidikan (SNP) BAN-SM.

---

### 30.1: BAN-SM Accreditation Readiness Dashboard & Evidence Collector (Selesai ✅)

Sistem pemantauan otomatis kesiapan akreditasi BAN-SM berdasarkan 8 SNP: Standar Isi, Proses, Kompetensi Lulusan, Pendidik & Tenaga Kependidikan, Sarana Prasarana, Pengelolaan, Pembiayaan, Penilaian.

#### Skema Migrasi

```php
Schema::create('accreditation_standards', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();
    $table->string('standard_name');
    $table->string('category');
    $table->text('description')->nullable();
    $table->float('weight')->default(1.0);
    $table->timestamps();
});

Schema::create('accreditation_indicators', function (Blueprint $table) {
    $table->id();
    $table->foreignId('accreditation_standard_id')->constrained()->cascadeOnDelete();
    $table->string('indicator_code');
    $table->string('indicator_name');
    $table->text('description')->nullable();
    $table->enum('data_source', ['auto', 'manual'])->default('manual');
    $table->string('auto_query')->nullable();
    $table->string('target_value');
    $table->timestamps();
});

Schema::create('accreditation_evidences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('accreditation_indicator_id')->constrained()->cascadeOnDelete();
    $table->enum('evidence_type', ['document', 'data', 'photo']);
    $table->string('file_path');
    $table->text('description')->nullable();
    $table->string('academic_year');
    $table->foreignId('uploaded_by')->constrained('users');
    $table->foreignId('verified_by')->nullable()->constrained('users');
    $table->timestamp('verified_at')->nullable();
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`AccreditationService`**: `calculateStandardScore`, `calculateOverallScore`, `mapAutoIndicators`, `getReadinessPercentage`, `generateReport`
- **`AccreditationController`**: `dashboard` (Spider/Radar chart 8 SNP), `standardDetail`, `uploadEvidence`, `verifyEvidence`, `generateReport`, `indicatorMapping`

#### Struktur View

```
resources/views/accreditation/
├── dashboard.blade.php          # Spider/radar chart 8 Standar Nasional
├── standard-detail.blade.php    # Detail indikator per standar
├── upload-evidence.blade.php    # Upload bukti fisik/digital
└── report.blade.php             # Laporan kesiapan akreditasi PDF
```

---

### 30.2: Internal Quality Assurance (IQA) & School Self-Evaluation (EDS) Engine (Selesai ✅)

Platform Evaluasi Diri Sekolah (EDS) digital berbasis siklus untuk evaluasi kinerja internal dan perencanaan perbaikan mutu berkelanjutan.

#### Skema Migrasi

```php
Schema::create('self_evaluation_cycles', function (Blueprint $table) {
    $table->id();
    $table->string('academic_year');
    $table->enum('semester', ['ganjil', 'genap']);
    $table->enum('status', ['draft', 'active', 'completed'])->default('draft');
    $table->date('start_date');
    $table->date('end_date');
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});

Schema::create('self_evaluation_responses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cycle_id')->constrained('self_evaluation_cycles')->cascadeOnDelete();
    $table->foreignId('standard_id')->constrained('accreditation_standards');
    $table->text('question_text');
    $table->text('response_text')->nullable();
    $table->integer('score');  // 1-4 scale
    $table->text('evidence_notes')->nullable();
    $table->foreignId('respondent_id')->constrained('users');
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`QualityAssuranceService`**: `createCycle`, `submitResponse`, `calculateResults`, `generateEdsReport`, `compareWithPrevious`
- **`QualityAssuranceController`**: `cycles`, `createCycle`, `fillEvaluation`, `results`, `compareReport`

#### Struktur View

```
resources/views/quality/
├── cycles.blade.php             # Daftar siklus EDS
├── evaluation-form.blade.php    # Formulir pengisian evaluasi per standar
└── results.blade.php            # Hasil evaluasi & perbandingan antar periode
```

---

### 30.3: Risk Assessment Matrix & Incident Response Workflow (Selesai ✅)

Manajemen risiko berbasis Enterprise Risk Management (ERM) dengan register risiko, matriks 5x5, dan alur respons insiden.

#### Skema Migrasi

```php
Schema::create('risk_registers', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description');
    $table->enum('category', ['Academic', 'Financial', 'Safety', 'IT/Cyber', 'Reputational', 'Regulatory']);
    $table->enum('likelihood', ['rare', 'unlikely', 'possible', 'likely', 'almost_certain']);
    $table->enum('impact', ['insignificant', 'minor', 'moderate', 'major', 'catastrophic']);
    $table->text('mitigation_plan')->nullable();
    $table->foreignId('owner_id')->constrained('users');
    $table->enum('status', ['identified', 'mitigating', 'resolved', 'accepted'])->default('identified');
    $table->date('review_date')->nullable();
    $table->timestamps();
});

Schema::create('incidents', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description');
    $table->string('category');
    $table->enum('severity', ['low', 'medium', 'high', 'critical']);
    $table->foreignId('reported_by')->constrained('users');
    $table->foreignId('assigned_to')->nullable()->constrained('users');
    $table->text('resolution_notes')->nullable();
    $table->enum('status', ['reported', 'investigating', 'resolved', 'closed'])->default('reported');
    $table->timestamp('reported_at');
    $table->timestamp('resolved_at')->nullable();
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`RiskManagementService`**: `assessRisk`, `calculateRiskScore`, `createIncident`, `resolveIncident`, `generateRiskMatrix`
- **`RiskManagementController`**: `riskRegister`, `createRisk`, `editRisk`, `incidentReport`, `createIncident`, `resolveIncident`, `riskMatrix` (5x5 heat map)

#### Struktur View

```
resources/views/risk/
├── register.blade.php       # Register risiko sekolah
├── matrix.blade.php         # Matriks risiko 5x5 color-coded heat map
├── incidents.blade.php      # Daftar insiden terlaporkan
└── incident-detail.blade.php # Detail insiden & resolusi
```

---

### 30.4: Policy & SOP Document Governance System (Selesai ✅)

Repositori SOP dengan manajemen versi dan acknowledgement tracking untuk memastikan kepatuhan seluruh staf.

#### Skema Migrasi

```php
Schema::create('sop_documents', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('code')->unique();
    $table->enum('category', ['academic', 'administrative', 'financial', 'safety', 'it']);
    $table->longText('content_html');
    $table->integer('version')->default(1);
    $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
    $table->date('effective_date');
    $table->date('review_date');
    $table->foreignId('author_id')->constrained('users');
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->timestamps();
});

Schema::create('sop_acknowledgements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sop_document_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamp('acknowledged_at');
    $table->string('ip_address')->nullable();
    $table->timestamps();
    $table->unique(['sop_document_id', 'user_id']);
});
```

#### Spesifikasi Service & Controller

- **`SopGovernanceService`**: `publish`, `archive`, `createVersion`, `requireAcknowledgement`, `getUnacknowledgedUsers`, `getComplianceRate`
- **`SopGovernanceController`**: `index`, `show`, `create`, `store`, `publish`, `acknowledge`, `complianceReport`

#### Struktur View

```
resources/views/sop/
├── index.blade.php          # Searchable SOP library
├── show.blade.php           # Reader interface + tombol Acknowledge
└── compliance.blade.php     # Dashboard persentase kepatuhan per role
```

---

## FASE 31: Enterprise Integration & Interoperability Layer

**Fokus Utama:** Lapisan integrasi dan interoperabilitas sistem untuk menghubungkan SISTA dengan ekosistem teknologi pendidikan nasional dan pihak ketiga secara aman.

---

### 31.1: GraphQL API Federation & API Versioning Strategy (Selesai ✅)

Kapabilitas GraphQL di samping REST API (API coexistence) dengan manajemen API Client, rate limiting, dan versioning v1/v2.

#### Skema Migrasi

```php
Schema::create('api_clients', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('client_id')->unique();
    $table->string('client_secret');
    $table->json('allowed_scopes');
    $table->integer('rate_limit_per_minute')->default(60);
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_used_at')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`GraphqlService`**: `resolveQuery`, `resolveMutation`, `validateSchema`, `introspection`
- **`ApiGatewayController`**: `graphqlEndpoint`, `registerClient`, `revokeClient`, `apiUsageStats`

#### Struktur View

```
resources/views/api/
├── documentation.blade.php      # Dokumentasi API interaktif
├── client-management.blade.php  # Manajemen API clients & keys
└── usage-stats.blade.php        # Statistik penggunaan API
```

---

### 31.2: Enterprise ETL Pipeline & Data Migration Toolkit (Selesai ✅)

Pipeline ETL (Extract-Transform-Load) untuk migrasi data massal dan sinkronisasi sistem eksternal (Dapodik, EMIS, Excel legacy) dengan validasi bertingkat dan rollback.

#### Skema Migrasi

```php
Schema::create('etl_jobs', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->enum('source_type', ['dapodik_xml', 'emis_json', 'excel', 'csv']);
    $table->string('source_file_path');
    $table->string('target_table');
    $table->json('mapping_config');
    $table->json('validation_errors')->nullable();
    $table->enum('status', ['pending', 'extracting', 'transforming', 'loading', 'completed', 'failed'])->default('pending');
    $table->integer('records_total')->default(0);
    $table->integer('records_processed')->default(0);
    $table->integer('records_failed')->default(0);
    $table->timestamp('started_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`EtlPipelineService`**: `createJob`, `extract`, `transform`, `load`, `validate`, `rollback`, `getProgress`
- **`EtlController`**: `index`, `createJob`, `uploadSource`, `configureMapping` (visual column mapper), `execute`, `progress`, `rollback`, `history`

#### Struktur View

```
resources/views/etl/
├── index.blade.php              # Daftar ETL jobs & history
├── configure-mapping.blade.php  # Visual column mapper
├── progress.blade.php           # Progress bar real-time
└── history.blade.php            # Riwayat eksekusi ETL
```

---

### 31.3: Third-Party Plugin Architecture & Extension Registry (Selesai ✅)

Arsitektur plugin-based modular dengan event-driven hooks untuk menambahkan fungsionalitas tanpa mengubah core codebase.

#### Skema Migrasi

```php
Schema::create('plugins', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('version');
    $table->string('author');
    $table->string('entry_class');
    $table->json('config')->nullable();
    $table->boolean('is_enabled')->default(false);
    $table->timestamp('installed_at');
    $table->timestamps();
});

Schema::create('plugin_hooks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
    $table->enum('hook_name', ['sidebar.menu', 'dashboard.widget', 'student.profile.tab', 'settings.panel']);
    $table->integer('priority')->default(10);
    $table->string('handler_class');
    $table->string('handler_method');
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`PluginManagerService`**: `install`, `uninstall`, `enable`, `disable`, `discoverPlugins`, `executeHook`, `getRegisteredHooks`
- **`PluginController`**: `index`, `install`, `uninstall`, `enable`, `disable`, `configure`, `marketplace`

#### Struktur View

```
resources/views/plugins/
├── index.blade.php          # Installed plugins manager
├── marketplace.blade.php    # Plugin marketplace browser
└── configure.blade.php      # Konfigurasi per plugin
```

---

### 31.4: Public API Documentation Portal (OpenAPI 3.0) (Selesai ✅)

Portal dokumentasi API otomatis berstandar OpenAPI 3.0 (Swagger UI) dengan API Playground untuk testing interaktif.

#### Spesifikasi Service & Controller

- **`ApiDocumentationService`**: `generateOpenApiSpec`, `renderSwaggerUi`, `generatePostmanCollection`
- **`ApiDocumentationController`**: `index`, `openApiJson`, `swaggerUi`, `postmanExport`

#### Struktur View

```
resources/views/api-docs/
├── swagger-ui.blade.php    # Swagger UI embedded
└── index.blade.php         # Portal dokumentasi API
```

---

## FASE 32: Enterprise Performance Engineering & Scalability Hardening

**Fokus Utama:** Optimasi performa aplikasi, pengerasan arsitektur untuk skalabilitas horizontal, dan pengujian beban otomatis kelas enterprise.

---

### 32.1: Advanced Database Query Optimizer & N+1 Query Sentinel (Selesai ✅)

Deteksi dan pencegahan N+1 Query secara proaktif menggunakan middleware sentinel, dilengkapi Slow Query Analyzer dan saran optimasi indeks otomatis.

#### Skema Migrasi

```php
Schema::create('query_performance_logs', function (Blueprint $table) {
    $table->id();
    $table->string('route');
    $table->text('query_sql');
    $table->float('execution_time_ms');
    $table->json('bindings');
    $table->boolean('is_slow')->default(false);
    $table->text('stack_trace')->nullable();
    $table->timestamp('created_at')->useCurrent();
});
```

#### Spesifikasi Service & Controller

- **`QueryOptimizerService`**: `detectNPlusOne`, `logSlowQuery`, `suggestIndexes`, `analyzeQueryPlan`, `generateOptimizationReport`
- **`QueryPerformanceController`**: `dashboard`, `slowQueries`, `nPlusOneReport`, `optimizationSuggestions`
- **Middleware**: Auto-detection yang mencatat query > 200ms

#### Struktur View

```
resources/views/performance/
├── query-dashboard.blade.php            # Dashboard performa query
├── slow-queries.blade.php               # Daftar slow queries
└── optimization-suggestions.blade.php   # Saran optimasi indeks & eager loading
```

---

### 32.2: Lazy Loading, Code Splitting & Asset Pipeline Optimization (Selesai ✅)

Peningkatan Core Web Vitals (LCP, FID, CLS) dan Time-to-Interactive (TTI) melalui Vite code splitting, lazy-loaded Blade components, konversi WebP, dan PurgeCSS.

#### Spesifikasi Service

- **`AssetOptimizationService`**: `analyzeBundle`, `generateReport`, `optimizeImages`, `purgeUnusedCss`
- **Konfigurasi**: Penyesuaian `vite.config.js` untuk chunk optimization & kompresi Brotli/Gzip

#### Struktur View

```
resources/views/performance/
└── asset-report.blade.php   # Laporan ukuran bundle & rekomendasi optimasi
```

---

### 32.3: Automated Stress Test & Load Testing Suite (Selesai ✅)

Kerangka pengujian beban terintegrasi (terinspirasi k6/Artillery) untuk memastikan keandalan saat beban puncak (PPDB, Ujian).

#### Skema Migrasi

```php
Schema::create('load_test_results', function (Blueprint $table) {
    $table->id();
    $table->string('scenario_name');
    $table->integer('concurrent_users');
    $table->integer('duration_seconds');
    $table->integer('total_requests');
    $table->float('requests_per_second');
    $table->float('p50_latency_ms');
    $table->float('p95_latency_ms');
    $table->float('p99_latency_ms');
    $table->float('error_rate_percent');
    $table->enum('status', ['running', 'completed', 'failed'])->default('running');
    $table->timestamp('started_at');
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});
```

#### Skenario Pengujian Pra-konfigurasi

| Skenario | Deskripsi | Target Endpoint |
| :--- | :--- | :--- |
| Login Flow | Simulasi login massal | `/login` |
| Dashboard Load | Beban dashboard admin | `/admin/dashboard` |
| Grade Entry | Input nilai rapor massal | `/academic/grades/store` |
| SPP Payment | Pembayaran SPP concurrent | `/finance/spp/pay` |
| Report Generation | Ekspor laporan PDF berat | `/reports/export` |
| PPDB Registration | Pendaftaran PPDB massal | `/ppdb/register` |

#### Spesifikasi Service & Controller

- **`LoadTestService`**: `runScenario`, `generateReport`, `compareResults`, `getBaseline`
- **`LoadTestController`**: `dashboard`, `runTest`, `results`, `compareBaseline`, `scenarios`

#### Struktur View

```
resources/views/load-test/
├── dashboard.blade.php     # Dashboard overview hasil load test
├── run.blade.php           # Form eksekusi skenario baru
├── results.blade.php       # Detail hasil: latensi P50/P95/P99, error rate
└── comparison.blade.php    # Perbandingan baseline vs test terbaru
```

---

### 32.4: Horizontal Scaling Architecture Blueprint & Read Replica Strategy (Selesai ✅)

Blueprint infrastruktur berskala besar: Read Replicas, Connection Pooling, Session Externalization (Redis), dan kesiapan CDN.

#### Skema Migrasi

```php
Schema::create('system_scaling_config', function (Blueprint $table) {
    $table->id();
    $table->string('config_key')->unique();
    $table->text('config_value');
    $table->string('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`ScalingConfigService`**: `getReadReplicaConfig`, `getConnectionPoolConfig`, `getSessionConfig`, `getCdnConfig`, `healthCheck`
- **`ScalingController`**: `dashboard` (architecture diagram), `configureReadReplica`, `configureSessionStorage`, `cdnSetup`

#### Struktur View

```
resources/views/scaling/
├── dashboard.blade.php       # Diagram arsitektur & status node
└── configuration.blade.php   # Panel konfigurasi scaling
```

---

## Rencana Verifikasi Fase 27-32

### 1. Automated Tests

```
tests/Feature/DisasterRecovery/SnapshotTest.php:
- Verifikasi pembuatan snapshot menghasilkan file valid dengan checksum SHA-256.
- Verifikasi restore snapshot mengembalikan data ke state yang benar.

tests/Feature/Edw/AnalyticalMartsTest.php:
- Verifikasi agregasi harian menghasilkan data mart yang akurat.
- Verifikasi endpoint executive dashboard mengembalikan data OLAP tanpa query OLTP.

tests/Feature/Webhook/WebhookDeliveryTest.php:
- Verifikasi signature HMAC-SHA256 terverifikasi dengan benar.
- Verifikasi replay delivery mengirim ulang payload yang sama.

tests/Feature/Auth/TwoFactorAuthTest.php:
- Verifikasi setup TOTP menghasilkan secret key valid.
- Verifikasi step-up auth memblokir aksi sensitif tanpa re-authentication.

tests/Feature/BusinessRule/RuleEngineTest.php:
- Verifikasi evaluasi kondisi memicu aksi yang tepat.
- Verifikasi aturan non-aktif tidak tereksekusi.

tests/Feature/Communication/MessagingTest.php:
- Verifikasi pembuatan percakapan dan pengiriman pesan.
- Verifikasi read receipts tercatat dengan benar.

tests/Feature/Accreditation/AccreditationReadinessTest.php:
- Verifikasi perhitungan skor standar akreditasi.
- Verifikasi auto-mapping indikator dari data existing.

tests/Feature/Integration/GraphqlApiTest.php:
- Verifikasi resolusi query GraphQL untuk Student, Grade, Attendance.
- Verifikasi rate limiting per API client.

tests/Feature/Performance/QuerySentinelTest.php:
- Verifikasi deteksi N+1 query pada route yang dimonitor.
- Verifikasi slow query (>200ms) tercatat ke log.
```

### 2. Manual Verification

- Verifikasi visual Spider/Radar Chart 8 Standar Akreditasi (Fase 30).
- Simulasi pemasangan dan pelepasan Plugin: pastikan Hooks sidebar muncul/hilang tanpa error (Fase 31).
- Cek tab Network di Developer Tools: pastikan ukuran asset lebih kecil berkat code splitting & WebP (Fase 32).
- Jalankan skenario Load Test PPDB: pastikan tidak ada memory leak atau degradasi respons (Fase 32).

---

## FASE 33: Enterprise Financial ERP -- RAPBS Budget Planning, Procurement & Scholarship Management

**Fokus Utama:** Melengkapi ekosistem keuangan sekolah dengan modul perencanaan anggaran multi-tahun (RAPBS), alur pengadaan barang/jasa, dan manajemen beasiswa/bantuan (PIP, BOS, Internal) berbasis Domain-Driven Design untuk tata kelola finansial yang transparan dan akuntabel.

---

### 33.1: RAPBS Multi-Year Budget Planning & Variance Analysis Engine

Modul perencanaan fiskal Enterprise untuk menyusun Rencana Anggaran Pendapatan & Belanja Sekolah (RAPBS) secara digital. Mendukung proyeksi multi-year (3 tahun), hierarki kategori anggaran, serta sistem Variance Analysis secara real-time yang membandingkan planned budget versus actual expense.

#### Skema Migrasi

```php
Schema::create('budget_plans', function (Blueprint $table) {
    $table->id();
    $table->string('fiscal_year', 9);
    $table->string('title');
    $table->enum('status', ['draft', 'submitted', 'approved', 'revised'])->default('draft');
    $table->decimal('total_revenue_planned', 15, 2)->default(0);
    $table->decimal('total_expense_planned', 15, 2)->default(0);
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->timestamp('approved_at')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});

Schema::create('budget_line_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('budget_plan_id')->constrained('budget_plans')->cascadeOnDelete();
    $table->enum('category', ['revenue', 'expense']);
    $table->string('sub_category');
    $table->string('account_code', 50)->index();
    $table->string('description');
    $table->decimal('planned_amount', 15, 2);
    $table->decimal('actual_amount', 15, 2)->default(0);
    $table->decimal('variance_amount', 15, 2)->virtualAs('planned_amount - actual_amount');
    $table->enum('quarter', ['Q1', 'Q2', 'Q3', 'Q4', 'annual'])->default('annual');
    $table->text('notes')->nullable();
    $table->timestamps();
});

Schema::create('budget_revisions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('budget_plan_id')->constrained('budget_plans')->cascadeOnDelete();
    $table->integer('revision_number');
    $table->text('reason');
    $table->foreignId('revised_by')->constrained('users');
    $table->decimal('old_total', 15, 2);
    $table->decimal('new_total', 15, 2);
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`BudgetPlanningService`**: `createPlan`, `addLineItem`, `calculateVariance`, `submitForApproval`, `approvePlan`, `revisePlan`, `generateRapbsReport`, `forecastNextYear`
- **`BudgetPlanningController`**: `dashboard` (donut chart revenue vs expense), `create`, `store`, `show`, `edit`, `lineItems`, `submitApproval`, `approve`, `varianceReport`, `forecastReport`

#### Struktur View

```
resources/views/budget/
|-- dashboard.blade.php          # OLAP donut chart revenue vs expense
|-- create.blade.php
|-- line-items.blade.php
|-- variance-report.blade.php
|-- forecast.blade.php
```

---

### 33.2: Procurement & Purchase Order Workflow

Sistem Pengadaan Barang dan Jasa terpadu dengan approval workflow bertingkat. Mengelola siklus end-to-end: Procurement Request -> Approval -> Purchase Order (PO) -> Goods Received Note (GRN).

#### Skema Migrasi

```php
Schema::create('procurement_requests', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description');
    $table->enum('department', ['academic', 'administrative', 'facilities', 'it']);
    $table->foreignId('requested_by')->constrained('users');
    $table->enum('urgency', ['low', 'normal', 'high', 'critical'])->default('normal');
    $table->decimal('estimated_budget', 15, 2);
    $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'ordered', 'received', 'completed'])->default('draft');
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->timestamps();
});

Schema::create('purchase_orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('procurement_request_id')->constrained('procurement_requests');
    $table->string('po_number')->unique();
    $table->string('vendor_name');
    $table->string('vendor_contact')->nullable();
    $table->json('items');
    $table->decimal('subtotal', 15, 2);
    $table->decimal('tax_amount', 15, 2)->default(0);
    $table->decimal('total_amount', 15, 2);
    $table->date('delivery_date');
    $table->enum('status', ['issued', 'partial_delivery', 'delivered', 'completed', 'cancelled'])->default('issued');
    $table->foreignId('issued_by')->constrained('users');
    $table->timestamps();
});

Schema::create('goods_received_notes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('purchase_order_id')->constrained('purchase_orders');
    $table->string('grn_number')->unique();
    $table->foreignId('received_by')->constrained('users');
    $table->date('received_date');
    $table->json('items_received');
    $table->text('condition_notes')->nullable();
    $table->boolean('is_complete')->default(true);
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`ProcurementService`**: `createRequest`, `submitForApproval`, `approveRequest`, `rejectRequest`, `createPurchaseOrder`, `recordDelivery`, `completeProcurement`
- **`ProcurementController`**: `dashboard`, `createRequest`, `submitApproval`, `approve`, `reject`, `createPO`, `recordGRN`, `history`

#### Struktur View

```
resources/views/procurement/
|-- dashboard.blade.php
|-- create-request.blade.php
|-- purchase-order.blade.php
|-- grn.blade.php
|-- history.blade.php
```

---

### 33.3: Scholarship & Grant Management Portal

Portal terpusat untuk mengelola beasiswa dan bantuan pendidikan: PIP, BOS, dan beasiswa internal yayasan (Merit/Need-Based). Terintegrasi dengan proses administrasi untuk aplikasi, peninjauan, hingga penyaluran (disbursement).

#### Skema Migrasi

```php
Schema::create('scholarship_programs', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->enum('type', ['pip', 'bos', 'internal_merit', 'internal_need', 'external']);
    $table->enum('source', ['government', 'yayasan', 'corporate', 'individual']);
    $table->string('academic_year', 9);
    $table->integer('total_quota');
    $table->decimal('amount_per_student', 15, 2);
    $table->json('eligibility_criteria');
    $table->date('application_start_date');
    $table->date('application_end_date');
    $table->enum('status', ['draft', 'open', 'closed', 'disbursed'])->default('draft');
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});

Schema::create('scholarship_applications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('scholarship_program_id')->constrained()->cascadeOnDelete();
    $table->foreignId('student_id')->constrained('users');
    $table->json('application_data');
    $table->json('supporting_documents');
    $table->decimal('gpa_score', 4, 2)->nullable();
    $table->decimal('family_income', 15, 2)->nullable();
    $table->string('recommendation_letter_path')->nullable();
    $table->enum('status', ['pending', 'shortlisted', 'approved', 'rejected', 'disbursed'])->default('pending');
    $table->foreignId('reviewed_by')->nullable()->constrained('users');
    $table->text('review_notes')->nullable();
    $table->timestamp('disbursed_at')->nullable();
    $table->timestamps();
});

Schema::create('scholarship_disbursements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('scholarship_application_id')->constrained();
    $table->decimal('amount', 15, 2);
    $table->enum('disbursement_method', ['bank_transfer', 'cash', 'wadiah_credit']);
    $table->string('reference_number')->nullable();
    $table->foreignId('disbursed_by')->constrained('users');
    $table->timestamp('disbursed_at')->useCurrent();
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`ScholarshipService`**: `createProgram`, `openApplications`, `submitApplication`, `reviewApplication`, `approveApplication`, `disburse`, `generateReport`, `calculateEligibility`
- **`ScholarshipController`**: `programs`, `createProgram`, `openApplications`, `applications`, `reviewApplication`, `approve`, `reject`, `disburse`, `report`

#### Struktur View

```
resources/views/scholarship/
|-- programs.blade.php
|-- apply.blade.php
|-- review.blade.php
|-- disbursement.blade.php
|-- report.blade.php
```

---

### 33.4: Cost Center Accounting & Departmental Budget Allocation

Sistem Cost Center untuk melacak efisiensi pusat biaya per departemen (Kurikulum, Kesiswaan, Humas, Sarpras, dsb).

#### Skema Migrasi

```php
Schema::create('cost_centers', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();
    $table->string('name');
    $table->enum('department', ['kurikulum', 'kesiswaan', 'humas', 'sarpras', 'tu', 'keuangan', 'keislaman']);
    $table->foreignId('head_id')->nullable()->constrained('users');
    $table->decimal('annual_budget_allocation', 15, 2);
    $table->decimal('spent_amount', 15, 2)->default(0);
    $table->timestamps();
});

Schema::create('cost_center_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cost_center_id')->constrained()->cascadeOnDelete();
    $table->enum('transaction_type', ['allocation', 'expense', 'adjustment']);
    $table->decimal('amount', 15, 2);
    $table->string('description');
    $table->string('reference_type')->nullable();
    $table->unsignedBigInteger('reference_id')->nullable();
    $table->foreignId('recorded_by')->constrained('users');
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`CostCenterService`**: `allocateBudget`, `recordExpense`, `getUtilization`, `transferBudget`, `generateDepartmentReport`
- **`CostCenterController`**: `dashboard`, `allocate`, `recordExpense`, `utilization`, `departmentReport`

#### Struktur View

```
resources/views/cost-center/
|-- dashboard.blade.php          # Departmental budget utilization bars
|-- allocate.blade.php
|-- transactions.blade.php
```

---

## FASE 34: Student Growth Ecosystem -- Wellbeing, Positive Behavior & Parent Conference

**Fokus Utama:** Memperkuat ekosistem pertumbuhan siswa secara holistik dengan platform kesehatan mental terukur, pengenalan perilaku positif berbasis Profil Pelajar Pancasila (P5) dan Akhlak Mahmudah, serta simplifikasi kolaborasi penjadwalan konsultasi orang tua-guru.

---

### 34.1: Student Mental Health & Emotional Wellbeing Screening Platform

Platform instrumen skrining kesehatan mental dan kesejahteraan emosional (mengadaptasi instrumen SRQ-20 dan PHQ-9). Dilengkapi fitur Private Mood Journal untuk analitik dini risiko mental siswa.

#### Skema Migrasi

```php
Schema::create('wellbeing_screenings', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->enum('screening_type', ['srq20', 'phq9', 'custom']);
    $table->string('academic_year', 9);
    $table->enum('semester', ['odd', 'even']);
    $table->string('target_grade')->nullable();
    $table->enum('status', ['draft', 'active', 'completed'])->default('draft');
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});

Schema::create('wellbeing_screening_responses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('wellbeing_screening_id')->constrained()->cascadeOnDelete();
    $table->foreignId('student_id')->constrained('users');
    $table->json('responses');
    $table->integer('total_score');
    $table->enum('risk_level', ['low', 'moderate', 'high', 'critical']);
    $table->timestamp('screened_at');
    $table->boolean('followup_required')->default(false);
    $table->text('followup_notes')->nullable();
    $table->foreignId('counselor_id')->nullable()->constrained('users');
    $table->timestamps();
});

Schema::create('wellbeing_journals', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('users');
    $table->tinyInteger('mood_level');
    $table->text('journal_entry')->nullable();
    $table->json('activities')->nullable();
    $table->float('sleep_hours')->nullable();
    $table->boolean('is_private')->default(true);
    $table->timestamp('created_at')->useCurrent();
});
```

#### Spesifikasi Service & Controller

- **`WellbeingService`**: `createScreening`, `submitResponse`, `calculateRiskLevel`, `flagHighRisk`, `generateClassReport`, `getStudentHistory`, `getMoodTrends`
- **`WellbeingController`**: `screenings`, `createScreening`, `activateScreening`, `fillScreening` (student), `results` (counselor heatmap), `studentDetail`, `moodJournal` (student private), `classReport`

#### Struktur View

```
resources/views/wellbeing/
|-- screenings.blade.php
|-- fill-screening.blade.php         # Student view
|-- results.blade.php                # Counselor heatmap
|-- student-detail.blade.php
|-- mood-journal.blade.php           # Student private mood tracker
|-- class-report.blade.php
```

---

### 34.2: Positive Behavior Recognition & Character Report Card (Profil Pelajar Pancasila / P5)

Engine asesmen karakter mencakup 6 dimensi P5 nasional (Beriman, Berkebinekaan Global, Bergotong-royong, Mandiri, Bernalar Kritis, Kreatif) dan dimensi karakter Islam (Akhlak Mahmudah: Jujur, Amanah, Istiqomah, Tawadhu, Ta'awun).

#### Skema Migrasi

```php
Schema::create('character_dimensions', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->enum('category', ['p5', 'islamic']);
    $table->text('description');
    $table->string('icon_name');
    $table->integer('display_order');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('character_assessments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('assessor_id')->constrained('users');
    $table->foreignId('character_dimension_id')->constrained();
    $table->string('academic_year', 9);
    $table->enum('semester', ['odd', 'even']);
    $table->tinyInteger('score');
    $table->text('evidence_notes')->nullable();
    $table->timestamps();
});

Schema::create('merit_points', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('awarded_by')->constrained('users');
    $table->foreignId('character_dimension_id')->nullable()->constrained();
    $table->integer('points');
    $table->string('reason');
    $table->string('event_context')->nullable();
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`P5AssessmentService`**: `assessStudent`, `calculateDimensionAverage`, `generateP5ReportCard`, `getLeaderboard`, `awardMeritPoints`, `generateClassP5Summary`
- **`P5AssessmentController`**: `dimensions`, `assess`, `studentReport` (radar chart), `classReport`, `leaderboard`, `awardMerit`, `reportCard`

#### Struktur View

```
resources/views/character/
|-- dimensions.blade.php
|-- assess.blade.php                 # Rubric form per student
|-- student-report.blade.php         # Radar chart 6+5 dimensions
|-- class-report.blade.php
|-- leaderboard.blade.php
|-- report-card.blade.php
```

---

### 34.3: Parent-Teacher Conference (PTC) Appointment Scheduler

Modul penjadwalan mandiri (Self-Service Scheduling) untuk konsultasi antara wali murid dan wali kelas dengan limitasi slot durasi dan pencegahan double-booking.

#### Skema Migrasi

```php
Schema::create('ptc_sessions', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('academic_year', 9);
    $table->enum('semester', ['odd', 'even']);
    $table->date('date');
    $table->time('start_time');
    $table->time('end_time');
    $table->integer('slot_duration_minutes')->default(15);
    $table->string('location');
    $table->foreignId('organizer_id')->constrained('users');
    $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
    $table->integer('max_slots_per_teacher');
    $table->timestamps();
});

Schema::create('ptc_slots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ptc_session_id')->constrained()->cascadeOnDelete();
    $table->foreignId('teacher_id')->constrained('users');
    $table->time('start_time');
    $table->time('end_time');
    $table->foreignId('parent_id')->nullable()->constrained('users');
    $table->foreignId('student_id')->nullable()->constrained('users');
    $table->text('topic')->nullable();
    $table->enum('status', ['available', 'booked', 'completed', 'cancelled', 'no_show'])->default('available');
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`PtcSchedulerService`**: `createSession`, `generateSlots`, `bookSlot`, `cancelBooking`, `markCompleted`, `markNoShow`, `getTeacherSchedule`, `getParentBookings`, `generateAttendanceReport`
- **`PtcSchedulerController`**: `sessions`, `createSession`, `generateSlots`, `availableSlots` (parent view), `bookSlot`, `cancelBooking`, `teacherSchedule`, `attendanceReport`

#### Struktur View

```
resources/views/ptc/
|-- sessions.blade.php
|-- create-session.blade.php
|-- available-slots.blade.php        # Parent: visual time picker
|-- teacher-schedule.blade.php       # Teacher: timeline view
|-- attendance-report.blade.php
```

---

### 34.4: Student Lifecycle Orchestration Engine

Mesin pelacakan lifecycle terpusat: Prospective -> Applicant -> Enrolled -> Active -> Graduated -> Alumni. Mengimplementasikan milestone tracking, kalkulasi retensi cohort, dan deteksi risiko dropout.

#### Skema Migrasi

```php
Schema::create('student_lifecycle_events', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->enum('event_type', ['prospective', 'applied', 'accepted', 'enrolled', 'promoted', 'retained', 'transferred_in', 'transferred_out', 'suspended', 'graduated', 'dropped_out']);
    $table->string('from_status')->nullable();
    $table->string('to_status');
    $table->string('academic_year', 9);
    $table->enum('semester', ['odd', 'even']);
    $table->foreignId('triggered_by')->constrained('users');
    $table->json('metadata')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});

Schema::create('student_lifecycle_milestones', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->enum('milestone_type', ['first_day', 'first_exam', 'first_achievement', 'tahfidz_30juz', 'graduated', 'alumni_registered']);
    $table->timestamp('achieved_at');
    $table->string('description');
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`StudentLifecycleService`**: `recordEvent`, `getTimeline`, `getCurrentPhase`, `calculateRetentionRate`, `getDropoutRiskStudents`, `generateCohortAnalysis`, `getMilestones`
- **`StudentLifecycleController`**: `timeline` (vertical timeline), `cohortAnalysis`, `retentionDashboard`, `atRiskStudents`, `milestones`

#### Struktur View

```
resources/views/lifecycle/
|-- student-timeline.blade.php       # Vertical timeline UI
|-- cohort-analysis.blade.php
|-- retention-dashboard.blade.php
|-- at-risk.blade.php
```

---

## FASE 35: Enterprise Academic Intelligence -- Kurikulum Merdeka Competency Mapping & LMS 2.0

**Fokus Utama:** Membangun kerangka kerja Kurikulum Merdeka yang komprehensif (Capaian Pembelajaran, Tujuan Pembelajaran, Alur Tujuan Pembelajaran) serta meningkatkan LMS ke standar interoperabilitas internasional.

---

### 35.1: Kurikulum Merdeka Competency Mapping Engine (CP/TP/ATP Framework)

Pemetaan hierarkis arsitektur pedagogis: Elemen CP -> Capaian Pembelajaran (CP) -> Tujuan Pembelajaran (TP) -> Alur Tujuan Pembelajaran (ATP). Fase E (Kelas X) dan Fase F (Kelas XI-XII) dengan evaluasi berbasis Taksonomi Bloom.

#### Skema Migrasi

```php
Schema::create('learning_outcomes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
    $table->enum('phase', ['E', 'F']);
    $table->string('element');
    $table->string('cp_code')->unique();
    $table->text('cp_description');
    $table->timestamps();
});

Schema::create('learning_objectives', function (Blueprint $table) {
    $table->id();
    $table->foreignId('learning_outcome_id')->constrained()->cascadeOnDelete();
    $table->string('tp_code');
    $table->text('tp_description');
    $table->enum('semester', ['ganjil', 'genap']);
    $table->integer('sequence_order');
    $table->enum('assessment_type', ['formatif', 'sumatif']);
    $table->enum('bloom_taxonomy_level', ['remember', 'understand', 'apply', 'analyze', 'evaluate', 'create']);
    $table->timestamps();
});

Schema::create('atp_mappings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('learning_objective_id')->constrained()->cascadeOnDelete();
    $table->integer('week_number');
    $table->integer('teaching_hours');
    $table->text('learning_activities');
    $table->text('assessment_method');
    $table->text('resources')->nullable();
    $table->timestamps();
});

Schema::create('competency_achievements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('learning_objective_id')->constrained()->cascadeOnDelete();
    $table->string('academic_year');
    $table->enum('status', ['not_started', 'in_progress', 'achieved', 'exceeded']);
    $table->text('evidence_notes')->nullable();
    $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('assessed_at')->nullable();
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`CompetencyMappingService`**: `createLearningOutcome`, `createObjective`, `mapToAtp`, `assessStudentCompetency`, `getStudentCompetencyProfile`, `getClassCompetencyHeatmap`, `generateCompetencyReport`
- **`CompetencyMappingController`**: `outcomes` (per subject), `createOutcome`, `objectives`, `createObjective`, `atpMapping` (Gantt-like week planner), `assessCompetency`, `studentProfile` (mastery radar chart), `classHeatmap` (green/yellow/red matrix), `competencyReport`

#### Struktur View

```
resources/views/competency/
|-- outcomes.blade.php               # CP tree view per mata pelajaran
|-- objectives.blade.php             # TP list per CP
|-- atp-mapping.blade.php            # Gantt-like week planner
|-- assess.blade.php
|-- student-profile.blade.php        # Mastery radar chart
|-- class-heatmap.blade.php          # Green/yellow/red cell matrix
```

---

### 35.2: Multi-Year Academic Planning & Graduation Pathway Tracker

Perencanaan akademik 3 tahun SMA (Kelas X -> XI -> XII), tracking peminatan (IPA/IPS/Bahasa/Keagamaan), dan Graduation Readiness Checker.

#### Skema Migrasi

```php
Schema::create('academic_pathways', function (Blueprint $table) {
    $table->id();
    $table->enum('name', ['IPA', 'IPS', 'Bahasa', 'Keagamaan']);
    $table->string('description');
    $table->json('required_subjects');
    $table->integer('required_credits');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('student_pathway_enrollments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('academic_pathway_id')->constrained()->cascadeOnDelete();
    $table->timestamp('enrolled_at');
    $table->enum('status', ['active', 'completed', 'changed']);
    $table->timestamps();
});

Schema::create('graduation_requirements', function (Blueprint $table) {
    $table->id();
    $table->enum('requirement_type', ['subject_credit', 'attendance_minimum', 'tahfidz_minimum', 'community_service', 'thesis', 'exam_pass']);
    $table->string('description');
    $table->string('threshold_value');
    $table->boolean('is_mandatory')->default(true);
    $table->timestamps();
});

Schema::create('student_graduation_progress', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('graduation_requirement_id')->constrained()->cascadeOnDelete();
    $table->string('current_value');
    $table->boolean('is_fulfilled')->default(false);
    $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('verified_at')->nullable();
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`AcademicPathwayService`**: `enrollStudent`, `trackProgress`, `checkGraduationReadiness`, `generateTranscript`, `switchPathway`, `getPathwayAnalytics`
- **`AcademicPathwayController`**: `pathways`, `enrollStudent`, `progressDashboard`, `graduationReadiness` (checklist), `transcriptPreview`, `pathwayAnalytics`

#### Struktur View

```
resources/views/pathway/
|-- index.blade.php                  # Pathway cards
|-- enroll.blade.php
|-- progress-dashboard.blade.php     # Credit tracking progress bars
|-- graduation-readiness.blade.php   # Validation checklist
|-- transcript-preview.blade.php
```

---

### 35.3: SCORM/xAPI Learning Content Interoperability Layer

Import dan playback konten SCORM 1.2/2004 serta xAPI (Tin Can) statement recording sebagai Learning Record Store (LRS) lite.

#### Skema Migrasi

```php
Schema::create('scorm_packages', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->enum('version', ['scorm12', 'scorm2004', 'xapi']);
    $table->string('file_path');
    $table->json('manifest_data')->nullable();
    $table->string('entry_point');
    $table->foreignId('elearning_class_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
    $table->enum('status', ['processing', 'ready', 'error']);
    $table->timestamps();
});

Schema::create('scorm_tracking', function (Blueprint $table) {
    $table->id();
    $table->foreignId('scorm_package_id')->constrained()->cascadeOnDelete();
    $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
    $table->enum('lesson_status', ['not_attempted', 'incomplete', 'completed', 'passed', 'failed']);
    $table->float('score_raw')->nullable();
    $table->float('score_max')->nullable();
    $table->integer('total_time_seconds')->default(0);
    $table->text('suspend_data')->nullable();
    $table->timestamp('last_accessed_at')->nullable();
    $table->timestamps();
});

Schema::create('xapi_statements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
    $table->string('verb');
    $table->string('object_type');
    $table->string('object_id');
    $table->json('result')->nullable();
    $table->json('context')->nullable();
    $table->timestamp('timestamp_at');
    $table->timestamp('stored_at');
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`ScormService`**: `uploadPackage`, `parseManifest`, `launchContent`, `recordTracking`, `getProgress`, `syncXapiStatements`
- **`ScormController`**: `packages`, `upload`, `launch` (iframe player), `tracking`, `progress`, `xapiEndpoint` (LRS endpoint)

#### Struktur View

```
resources/views/scorm/
|-- packages.blade.php
|-- upload.blade.php
|-- player.blade.php                 # Iframe SCORM player wrapper
|-- progress.blade.php
```

---

### 35.4: Virtual Classroom & Video Conferencing Integration Hub

Integrasi dengan Zoom / Google Meet / Jitsi Meet. One-click meeting creation dari jadwal pelajaran dengan pemantauan kehadiran otomatis.

#### Skema Migrasi

```php
Schema::create('virtual_classrooms', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->enum('provider', ['zoom', 'google_meet', 'jitsi']);
    $table->string('meeting_id')->nullable();
    $table->string('meeting_url');
    $table->string('meeting_password')->nullable();
    $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('schedule_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('elearning_class_id')->nullable()->constrained()->nullOnDelete();
    $table->dateTime('start_time');
    $table->dateTime('end_time')->nullable();
    $table->integer('duration_minutes');
    $table->enum('status', ['scheduled', 'live', 'ended', 'cancelled']);
    $table->string('recording_url')->nullable();
    $table->integer('attendees_count')->default(0);
    $table->timestamps();
});

Schema::create('virtual_classroom_attendances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('virtual_classroom_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamp('joined_at');
    $table->timestamp('left_at')->nullable();
    $table->integer('duration_minutes')->nullable();
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`VirtualClassroomService`**: `createMeeting`, `startMeeting`, `endMeeting`, `recordAttendance`, `getMeetingHistory`, `generateAttendanceReport`
- **`VirtualClassroomController`**: `index` (upcoming/live/past tabs), `create`, `start`, `join` (lobby), `end`, `attendanceReport`, `history`

#### Struktur View

```
resources/views/virtual-classroom/
|-- index.blade.php                  # Upcoming/Live/Past Tabs
|-- create.blade.php
|-- join.blade.php                   # Pre-meeting lobby
|-- attendance.blade.php
```

---

## FASE 36: Enterprise Human Capital Excellence -- 360 Evaluation, CPD & Leadership Pipeline

**Fokus Utama:** Membangun instrumen holistik untuk penilaian guru berbasis 360 derajat, dokumentasi Continuing Professional Development (CPD), pengelolaan talenta eksekutif pendidikan, serta analisis dan pemerataan beban kerja.

---

### 36.1: 360-Degree Teacher Evaluation & EDOM

Penilaian holistik dari 5 perspektif: Kepala Sekolah (supervisor), Rekan Sejawat (peer), Siswa (EDOM), Orang Tua, dan Self-Assessment. Data dikonsolidasikan dalam Radar Chart untuk menghasilkan Development Plan spesifik.

#### Skema Migrasi

```php
Schema::create('evaluation_360_cycles', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('academic_year');
    $table->string('semester');
    $table->enum('status', ['draft', 'active', 'collecting', 'analyzing', 'completed']);
    $table->date('start_date');
    $table->date('end_date');
    $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
    $table->timestamps();
});

Schema::create('evaluation_360_instruments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cycle_id')->constrained('evaluation_360_cycles')->cascadeOnDelete();
    $table->enum('evaluator_type', ['supervisor', 'peer', 'student', 'parent', 'self']);
    $table->json('questions');
    $table->enum('scale_type', ['likert5', 'likert4', 'descriptive']);
    $table->boolean('is_anonymous')->default(true);
    $table->timestamps();
});

Schema::create('evaluation_360_responses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('instrument_id')->constrained('evaluation_360_instruments')->cascadeOnDelete();
    $table->foreignId('evaluatee_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
    $table->json('scores');
    $table->text('comments')->nullable();
    $table->timestamp('submitted_at');
    $table->timestamps();
});

Schema::create('evaluation_360_summaries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cycle_id')->constrained('evaluation_360_cycles')->cascadeOnDelete();
    $table->foreignId('evaluatee_id')->constrained('users')->cascadeOnDelete();
    $table->float('supervisor_avg')->nullable();
    $table->float('peer_avg')->nullable();
    $table->float('student_avg')->nullable();
    $table->float('parent_avg')->nullable();
    $table->float('self_score')->nullable();
    $table->float('overall_score')->nullable();
    $table->json('strengths');
    $table->json('improvements');
    $table->timestamp('generated_at');
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`Evaluation360Service`**: `createCycle`, `createInstrument`, `submitResponse`, `generateSummary`, `calculateScores`, `getRanking`, `generateDevelopmentPlan`
- **`Evaluation360Controller`**: `cycles`, `createCycle`, `instruments`, `fillEvaluation` (Likert form), `results` (spider chart 5 perspectives), `summaryReport`, `developmentPlan`, `ranking`

#### Struktur View

```
resources/views/evaluation-360/
|-- cycles.blade.php
|-- fill-evaluation.blade.php        # Likert scaling form
|-- results.blade.php                # Radar/spider chart 5 perspectives
|-- summary-report.blade.php
|-- development-plan.blade.php
|-- ranking.blade.php
```

---

### 36.2: Continuing Professional Development (CPD/PKB) Credit Tracker

Pelacakan PKB (Pengembangan Keprofesian Berkelanjutan) guru sesuai Permendikbud: Pengembangan Diri, Publikasi Ilmiah, Karya Inovatif, Pelatihan/Workshop, Sertifikasi.

#### Skema Migrasi

```php
Schema::create('cpd_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('description');
    $table->float('credit_weight');
    $table->integer('max_credits_per_year')->nullable();
    $table->timestamps();
});

Schema::create('cpd_activities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
    $table->foreignId('cpd_category_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->text('description');
    $table->string('provider')->nullable();
    $table->date('start_date');
    $table->date('end_date');
    $table->integer('hours');
    $table->float('credits_earned');
    $table->string('certificate_path')->nullable();
    $table->enum('status', ['pending', 'verified', 'rejected']);
    $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('verified_at')->nullable();
    $table->timestamps();
});

Schema::create('cpd_targets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
    $table->string('academic_year');
    $table->float('target_credits');
    $table->float('achieved_credits')->default(0);
    $table->enum('status', ['on_track', 'behind', 'exceeded']);
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`CpdTrackerService`**: `logActivity`, `verifyActivity`, `calculateCredits`, `getProgress`, `checkCompliance`, `generateCpdReport`, `suggestActivities`
- **`CpdTrackerController`**: `dashboard` (progress bar & credit breakdown), `logActivity`, `verifyActivity`, `myProgress`, `complianceReport`, `teamOverview`

#### Struktur View

```
resources/views/cpd/
|-- dashboard.blade.php              # Target vs achieved progress bar
|-- log-activity.blade.php
|-- my-progress.blade.php
|-- compliance-report.blade.php
|-- team-overview.blade.php          # Managerial/Principal view
```

---

### 36.3: School Leadership Succession Planning & Talent Pipeline

Manajemen Human Capital untuk suksesi pimpinan sekolah (Kepala Sekolah, Waka, Koordinator) dengan readiness assessment dan development plan.

#### Skema Migrasi

```php
Schema::create('leadership_positions', function (Blueprint $table) {
    $table->id();
    $table->enum('title', ['kepala_sekolah', 'waka_kurikulum', 'waka_kesiswaan', 'waka_humas', 'waka_sarpras', 'koordinator_bk', 'koordinator_tahfidz']);
    $table->foreignId('current_holder_id')->nullable()->constrained('users')->nullOnDelete();
    $table->enum('succession_priority', ['critical', 'high', 'medium']);
    $table->date('review_date');
    $table->timestamps();
});

Schema::create('succession_candidates', function (Blueprint $table) {
    $table->id();
    $table->foreignId('leadership_position_id')->constrained()->cascadeOnDelete();
    $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
    $table->enum('readiness_level', ['ready_now', 'ready_1yr', 'ready_3yr', 'developing']);
    $table->json('strengths');
    $table->json('development_areas');
    $table->text('development_plan')->nullable();
    $table->foreignId('endorsed_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`SuccessionPlanningService`**: `identifyPosition`, `addCandidate`, `assessReadiness`, `createDevelopmentPlan`, `generatePipelineReport`
- **`SuccessionPlanningController`**: `positions` (org chart), `addCandidate`, `assessReadiness`, `developmentPlan`, `pipelineReport`

#### Struktur View

```
resources/views/succession/
|-- positions.blade.php              # Org chart with succession status
|-- candidates.blade.php
|-- assess.blade.php
|-- pipeline-report.blade.php
```

---

### 36.4: Teacher Workload Balancing & Capacity Dashboard

Analisis dan pemerataan beban kerja guru: jam mengajar, tugas tambahan, bimbingan, dan beban ekstrakurikuler.

#### Skema Migrasi

```php
Schema::create('teacher_workloads', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
    $table->string('academic_year');
    $table->string('semester');
    $table->integer('teaching_hours_per_week');
    $table->foreignId('homeroom_class_id')->nullable()->constrained('classrooms')->nullOnDelete();
    $table->json('additional_duties');
    $table->json('extracurricular_duties');
    $table->float('total_workload_score');
    $table->enum('status', ['under', 'balanced', 'over']);
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`WorkloadBalancingService`**: `calculateWorkload`, `getWorkloadDistribution`, `suggestRebalancing`, `getCapacityReport`
- **`WorkloadBalancingController`**: `dashboard` (bar chart workload comparison), `teacherDetail`, `rebalancingSuggestions`, `capacityReport`

#### Struktur View

```
resources/views/workload/
|-- dashboard.blade.php              # Bar chart: teacher workload comparison
|-- teacher-detail.blade.php
|-- rebalancing.blade.php
|-- capacity-report.blade.php
```

---

## FASE 37: Enterprise UX Mastery -- Accessibility, Internationalization & Design System

**Fokus Utama:** Memastikan platform SISTA memenuhi standar aksesibilitas internasional WCAG 2.2 AA+, dukungan penuh konten Arab (RTL), dan membangun Design System Component Library yang terdokumentasi.

---

### 37.1: WCAG 2.2 AA+ Accessibility Hardening & Screen Reader Optimization

Audit dan hardening aksesibilitas untuk seluruh 145+ view directories: color contrast (4.5:1 minimum), focus indicators, ARIA labels/landmarks, keyboard navigation.

#### Skema Migrasi

```php
Schema::create('accessibility_audit_logs', function (Blueprint $table) {
    $table->id();
    $table->string('page_route');
    $table->string('audit_type');
    $table->integer('issues_found')->default(0);
    $table->integer('issues_resolved')->default(0);
    $table->string('wcag_level');
    $table->string('audited_by')->nullable();
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`AccessibilityService`**: `runAudit`, `generateReport`, `getComplianceScore`, `trackResolution`
- **`AccessibilityController`**: `dashboard` (compliance score gauge), `audit`, `report`, `resolveIssue`
- **Artisan Command**: `php artisan accessibility:audit --route=*`

#### Checklist Implementasi Utama

- Semua form inputs wajib memiliki associated `<label>` dan error announcements via `aria-describedby`
- Interactive elements wajib memiliki visible focus indicators (2px solid ring)
- Gambar wajib atribut `alt` deskriptif atau `aria-hidden="true"` untuk dekoratif
- Modal dialog wajib menerapkan focus trapping dan closeable via Escape
- Tabel data wajib `scope="row"` / `scope="col"` pada header dan elemen `<caption>`
- Skip navigation link di setiap halaman
- Live region (`aria-live="polite"`) untuk notifikasi dinamis
- Touch target minimal 44x44px untuk mobile

#### Struktur View

```
resources/views/accessibility/
|-- dashboard.blade.php              # Compliance score gauge
|-- audit-report.blade.php
```

---

### 37.2: Advanced Internationalization (i18n) & Full Arabic (RTL) Content Support

Penguatan dukungan bahasa Arab dengan layout RTL, CSS logical properties, dan kalender Hijriah.

#### Skema Migrasi

```php
Schema::create('translation_overrides', function (Blueprint $table) {
    $table->id();
    $table->string('locale');
    $table->string('key');
    $table->text('value');
    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
    $table->unique(['locale', 'key']);
});
```

#### Spesifikasi Service & Controller

- **`TranslationService`**: `getTranslation`, `overrideTranslation`, `exportTranslations`, `importTranslations`, `getCompletionPercentage`
- **`TranslationController`**: `index` (translation manager), `override`, `export`, `import`, `completionReport`

#### Checklist Implementasi Utama

- CSS logical properties (`margin-inline-start` menggantikan `margin-left`)
- Elemen `<bdi>` dan `<bdo>` untuk teks campuran Arab/Latin
- Deteksi otomatis `dir="rtl"` pada `<html>` berdasarkan active locale
- Font rendering: Noto Sans Arabic / Amiri untuk konten Arab
- Penomoran Arabic-Indic numerals
- Dukungan konversi penanggalan ke kalender Hijriah

#### Struktur View

```
resources/views/i18n/
|-- translation-manager.blade.php
|-- completion-report.blade.php
```

---

### 37.3: Enterprise Design System Component Library (Blade Component Kit)

Pustaka komponen Blade terdokumentasi dan terstandarisasi untuk konsistensi visual di seluruh platform.

#### Daftar Komponen Utama

- **Layout:** `x-page-header`, `x-section-card`, `x-stat-card`, `x-empty-state`, `x-two-column-layout`
- **Navigation:** `x-sidebar-nav`, `x-breadcrumb`, `x-tab-group`, `x-pagination`
- **Data Display:** `x-data-table`, `x-status-badge`, `x-avatar`, `x-timeline`, `x-chart-container`
- **Forms:** `x-form-input`, `x-form-select`, `x-form-textarea`, `x-form-checkbox`, `x-form-toggle`, `x-form-file-upload`, `x-form-date-picker`, `x-form-search`
- **Feedback:** `x-toast`, `x-alert-banner`, `x-progress-bar`, `x-loading-skeleton`, `x-confirm-dialog`
- **Overlays:** `x-modal`, `x-slide-over`, `x-dropdown-menu`, `x-tooltip`

#### Spesifikasi Controller

- **`DesignSystemController`**: `showcase` (component gallery per kategori), `documentation`

#### Struktur View

```
resources/views/design-system/
|-- showcase.blade.php               # Visual component gallery
|-- documentation.blade.php
```

---

### 37.4: Print-Optimized Stylesheet Engine for All Reports

Sistem pencetakan enterprise yang menjamin semua cetakan (Rapor, Surat, Tagihan, Presensi) memiliki format sempurna di atas kertas.

#### Spesifikasi Service

- **`PrintStylesheetService`**: `generatePrintLayout`, `configurePaperSize`, `setMargins`, `addWatermark`, `addHeaderFooter`

#### Checklist Implementasi Utama

- File stylesheet khusus (`print.css`) dengan rules `@media print`
- Kontrol page break: `break-before: page`, `break-after: page`, `break-inside: avoid`
- Hidden UI elements (sidebar, navbar, action buttons) via `.no-print`
- Preset dimensi kertas A4 dan Legal
- Watermark kop surat resmi SMA Islam Sultan Agung 1 Semarang
- Penomoran halaman dinamis via CSS counters
- Posisi QR code verifikasi tanda tangan digital
- Color-safe mode untuk cetak grayscale

---

## FASE 38: Enterprise Deployment & Production Hardening Blueprint

**Fokus Utama:** Menyiapkan blueprint lengkap untuk deployment produksi, termasuk konfigurasi environment, strategi versioning, dan pengujian end-to-end otomatis.

---

### 38.1: Production Environment Configuration & Secret Management

Sentralisasi pengelolaan environment variable untuk tahapan deployment: development, staging, production.

#### Skema Migrasi

```php
Schema::create('system_configurations', function (Blueprint $table) {
    $table->id();
    $table->string('environment');
    $table->string('config_group');
    $table->string('config_key');
    $table->text('config_value_encrypted');
    $table->text('description')->nullable();
    $table->boolean('is_sensitive')->default(false);
    $table->string('last_updated_by')->nullable();
    $table->timestamps();
    $table->unique(['environment', 'config_key']);
});
```

#### Spesifikasi Service & Controller

- **`EnvironmentConfigService`**: `getConfig`, `setConfig`, `validateEnvironment`, `generateEnvFile`, `rotateSecrets`, `healthCheck`
- **`EnvironmentConfigController`**: `dashboard` (environment health status), `configure`, `validateEnv`, `generateEnv`, `secretRotation`

#### Checklist Implementasi Utama

- Dokumentasi `.env.example` untuk 50+ variabel
- Secret Rotation strategy untuk API keys, DB passwords, Application Key
- Validasi otomatis saat application boot
- Database Connection Pooling configuration
- Queue Worker supervisor configuration (Supervisor/systemd)
- Centralized Log Rotation (monolog daily, retensi 30 hari)

#### Struktur View

```
resources/views/environment/
|-- dashboard.blade.php
|-- configure.blade.php
```

---

### 38.2: Automated Database Seeding Strategy & Data Anonymization for Staging

Strategi pengisian data berdasarkan target environment.

#### Spesifikasi Service

- **`DatabaseSeedingService`**: `seedDevelopment`, `seedStaging`, `seedProduction`, `anonymizeData`

#### Konfigurasi Environment

- **Development:** Full Mega Seeder (~25.000 records) dengan penamaan lokal Indonesia
- **Staging:** Data anonymized (nama -> faker, NIK -> hashed, foto -> placeholder)
- **Production:** Hanya lookup data esensial (Tahun Ajaran, Master Mapel, Ruangan, Setting Default)

#### Custom Artisan Commands

- `php artisan db:seed-dev`
- `php artisan db:seed-staging`
- `php artisan db:seed-production`
- `php artisan db:anonymize`

---

### 38.3: Release Versioning, Semantic Changelog & Rollback Strategy

Tata kelola SemVer (Semantic Versioning) dan rekam jejak perilisan untuk platform SISTA.

#### Skema Migrasi

```php
Schema::create('release_versions', function (Blueprint $table) {
    $table->id();
    $table->string('version')->unique();
    $table->string('release_type');
    $table->text('changelog_summary');
    $table->json('breaking_changes')->nullable();
    $table->integer('migration_count')->default(0);
    $table->string('released_by');
    $table->timestamp('released_at');
    $table->boolean('rollback_tested')->default(false);
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`ReleaseManagerService`**: `createRelease`, `generateChangelog`, `tagVersion`, `validateMigrations`, `testRollback`
- **`ReleaseManagerController`**: `index` (version timeline), `createRelease`, `changelog`, `rollbackTest`

#### Checklist Implementasi Utama

- Automated changelog dari Conventional Commits
- Validasi graf dependensi migration
- Pre-release checklist (tests pass, no breaking API, Blue-Green readiness)
- Rollback Playbook: `php artisan migrate:rollback --step=N`
- Backup database sebelum setiap rilis

#### Struktur View

```
resources/views/releases/
|-- index.blade.php                  # Version timeline
|-- changelog.blade.php
|-- rollback-test.blade.php
```

---

### 38.4: Comprehensive End-to-End (E2E) Testing Suite Blueprint

Pengujian E2E menggunakan Laravel Dusk / Playwright untuk memastikan skenario bisnis berjalan tanpa hambatan.

#### Skema Migrasi

```php
Schema::create('e2e_test_runs', function (Blueprint $table) {
    $table->id();
    $table->string('suite_name');
    $table->string('browser');
    $table->integer('total_tests')->default(0);
    $table->integer('passed')->default(0);
    $table->integer('failed')->default(0);
    $table->integer('skipped')->default(0);
    $table->float('duration_seconds')->default(0);
    $table->string('status');
    $table->string('triggered_by');
    $table->timestamp('started_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->string('report_path')->nullable();
    $table->timestamps();
});
```

#### Spesifikasi Service & Controller

- **`E2eTestService`**: `runSuite`, `generateReport`, `compareWithBaseline`, `getFlakeyTests`
- **`E2eTestController`**: `dashboard` (test run history), `runSuite`, `results`, `flakeyTests`, `compareBaseline`

#### 10 Skenario Uji Prioritas

| No | Skenario | Endpoint |
| :---: | :--- | :--- |
| 1 | Login & Authentication (12 Role) | `/login` |
| 2 | Student Registration (SPMB Full Flow) | `/spmb/register` |
| 3 | Grade Entry & Report Card Generation | `/grades/store` |
| 4 | SPP Payment & Receipt Download | `/finance/spp/pay` |
| 5 | Attendance Marking (Student + Employee) | `/attendance/store` |
| 6 | Tahfidz Assessment Submission | `/tahfidz/store` |
| 7 | E-Learning Material Upload & Viewing | `/elearning/upload` |
| 8 | WhatsApp Notification Trigger | `/whatsapp/send` |
| 9 | Budget Planning Workflow (Create -> Approve) | `/budget/approve` |
| 10 | Digital Signature Document Flow | `/documents/sign` |

#### Struktur View

```
resources/views/e2e/
|-- dashboard.blade.php              # Test run history
|-- results.blade.php
|-- flakey-tests.blade.php
```

---

## Rencana Verifikasi Fase 33-38

### 1. Automated Tests

```
tests/Feature/Budget/RapbsPlanningTest.php:
- Verifikasi pembuatan RAPBS menghasilkan line items terstruktur.
- Verifikasi variance calculation akurat (planned vs actual).

tests/Feature/Procurement/PurchaseOrderTest.php:
- Verifikasi alur approval procurement request.
- Verifikasi GRN tercatat setelah delivery.

tests/Feature/Scholarship/ScholarshipApplicationTest.php:
- Verifikasi eligibility check berdasarkan GPA dan income.
- Verifikasi disbursement tercatat ke wadiah account.

tests/Feature/Wellbeing/WellbeingScreeningTest.php:
- Verifikasi kalkulasi risk level dari SRQ-20 score.
- Verifikasi high-risk alert dispatch ke counselor.

tests/Feature/Character/P5AssessmentTest.php:
- Verifikasi assessment 6 dimensi P5 menghasilkan radar chart data.
- Verifikasi merit points terakumulasi per student.

tests/Feature/Academic/CompetencyMappingTest.php:
- Verifikasi hierarki CP -> TP -> ATP terbentuk dengan benar.
- Verifikasi student competency heatmap menghasilkan data akurat.

tests/Feature/HumanCapital/Evaluation360Test.php:
- Verifikasi multi-rater scoring menghasilkan summary yang benar.
- Verifikasi anonymous evaluation tidak mengekspos evaluator_id.

tests/Feature/Deployment/ReleaseVersioningTest.php:
- Verifikasi migration rollback berjalan tanpa error.
- Verifikasi changelog generation dari conventional commits.
```

### 2. Manual Verification

- Verifikasi RAPBS variance report menampilkan donut chart yang akurat (Fase 33).
- Simulasi booking slot PTC oleh parent dan validasi timeline view di sisi guru (Fase 34).
- Verifikasi SCORM player dapat memutar konten interaktif dalam iframe (Fase 35).
- Verifikasi radar chart evaluasi 360 menampilkan 5 perspektif dengan benar (Fase 36).
- Lighthouse Accessibility Score harus mencapai 100 (Fase 37).
- Eksekusi E2E full suite harus selesai kurang dari 15 menit dengan 100% pass rate (Fase 38).

---

## Urutan Prioritas Implementasi (Updated Juli 2026 -- Ultimate Suite Edition)

| Prioritas | Modul | Estimasi | Status |
| :---: | :--- | :--- | :---: |
| P0 | 11.3: Penilaian Harian & Remedial | 3-4 hari | Selesai |
| P0 | 12.1: Penguatan Dashboard per Role | 3-4 hari | Selesai |
| P0 | 13.1: Mega Seeder Data Realistis | 3-4 hari | Selesai |
| P1 | 11.2: Mutaba'ah Ibadah Lengkap | 2-3 hari | Selesai |
| P1 | 11.1: Tahfidz Komprehensif | 2-3 hari | Selesai |
| P1 | 11.5: Presensi Kegiatan Sekolah | 2-3 hari | Selesai |
| P1 | 12.2: Penguatan Jadwal | 2 hari | Selesai |
| P2 | 11.4: Item Analysis CBT | 2-3 hari | Selesai |
| P2 | 12.3-12.5: Penguatan E-Learning, Perpustakaan, Keuangan | 6 hari | Selesai |
| P3 | 14.1-14.4: UI Consistency, Animasi, Error Pages, Tests | 10 hari | Selesai |
| P3 | 15.1-15.3: Notulensi, Pengumuman, Todo List | 5 hari | Selesai |
| P0 | 16.1: Sistem SPMB Online Komprehensif | 5-7 hari | Selesai |
| P0 | 16.2: Overhaul Alumni & Tracer Study | 3-4 hari | Selesai |
| P1 | 16.3-16.5: HRD, Ekskul, OSIS & MPK | 10-13 hari | Selesai |
| P2 | 17.1-17.4: Analytics, AI Chatbot, UKS, CMS | 12-16 hari | Selesai |
| P1 | 18.1: WhatsApp Automation & Broadcast | 3-4 hari | Selesai |
| P3 | 18.2-18.3: Workflow & Smart Alert | 4-5 hari | Selesai |
| P2 | 19.1-19.2: Mega Seeder v2 & Test Coverage | 7-9 hari | Selesai |
| P3 | 19.3-20.3: Performance, Landing Page, PWA, Docs | 8-11 hari | Selesai |
| P0 | 21.1: Tabungan Wadiah & Cashless Kantin | 4-5 hari | Selesai |
| P1 | 21.2: Portal ZIS & Wakaf YBWSA | 3-4 hari | Selesai |
| P0 | 22.1: Dapodik & EMIS Sync Mapper | 3-4 hari | Selesai |
| P1 | 22.2: AI Teacher Copilot (RPP & CBT) | 4-5 hari | Selesai |
| P2 | 23.1-23.2: IoT QR Code Asset & Barcode Library | 5-7 hari | Selesai |
| P0 | 24.1: Double-Entry Ledger Auditor | 3-4 hari | Selesai |
| P3 | 24.2: Security Dashboard & Intrusion Alert | 3-4 hari | Selesai |
| P0 | 25.1: Purifikasi UI/UX Eliminasi Total Emoji | 2-3 hari | Selesai |
| P0 | 26.1: Modular Domain Boundaries (Single-School Core) | 3-4 hari | Selesai |
| P1 | 26.2: Event-Driven Queue & Background Bus | 3-4 hari | Selesai |
| P2 | 26.3: Distributed Multi-Layer Cache Strategy | 2-3 hari | Selesai |
| P0 | 26.4: API Gateway, Rate Limit & Circuit Breakers | 3-4 hari | Selesai |
| P3 | 26.5: APM Observability, Tracing & Health Dashboard | 3-4 hari | Selesai |
| P0 | 26.6: Immutable Audit Ledger & Enterprise RBAC | 4-5 hari | Selesai |
| P3 | 26.7: Zero-Downtime Feature Flag Engine | 2-3 hari | Selesai |
| P1 | 26.8: PDPL Privacy & Data Anonymization Engine | 3-4 hari | Selesai |
| P0 | 27.1: Disaster Recovery & Automated Snapshot | 3-4 hari | Selesai |
| P1 | 27.2: Enterprise Data Warehouse & OLAP Engine | 4-5 hari | Selesai |
| P2 | 27.3: Event Streaming Bus & Webhook Hub | 3-4 hari | Selesai |
| P0 | 27.4: Advanced TOTP 2FA & Session Hijacking Safeguard | 3-4 hari | Selesai |
| P3 | 27.5: Business Process Automation Rule Engine | 4-5 hari | Selesai |
| P1 | 27.6: Enterprise Vendor SLA Scorecard Monitor | 2-3 hari | Selesai |
| P1 | 28.1: Unified Notification Center & Enterprise Inbox | 3-4 hari | Selesai |
| P2 | 28.2: Internal Messaging & Threaded Discussion | 4-5 hari | Selesai |
| P1 | 28.3: Digital Signature & E-Stamp Workflow | 3-4 hari | Selesai |
| P2 | 28.4: Enterprise Calendar & Resource Booking | 4-5 hari | Selesai |
| P2 | 29.1: Centralized Digital Asset Management (DAM) | 3-4 hari | Selesai |
| P1 | 29.2: Enterprise Report Builder & Designer | 4-5 hari | Selesai |
| P3 | 29.3: Intelligent Document Processing & OCR | 3-4 hari | Selesai |
| P2 | 29.4: Multi-Format Export Engine | 2-3 hari | Selesai |
| P0 | 30.1: BAN-SM Accreditation Readiness Dashboard | 4-5 hari | Selesai |
| P1 | 30.2: Internal Quality Assurance & EDS Engine | 3-4 hari | Selesai |
| P2 | 30.3: Risk Assessment Matrix & Incident Response | 3-4 hari | Selesai |
| P1 | 30.4: Policy & SOP Document Governance | 3-4 hari | Selesai |
| P2 | 31.1: GraphQL API Federation & Versioning | 4-5 hari | Selesai |
| P1 | 31.2: Enterprise ETL Pipeline & Migration Toolkit | 4-5 hari | Selesai |
| P3 | 31.3: Third-Party Plugin Architecture | 3-4 hari | Selesai |
| P2 | 31.4: Public API Documentation Portal (OpenAPI) | 2-3 hari | Selesai |
| P0 | 32.1: Database Query Optimizer & N+1 Sentinel | 3-4 hari | Selesai |
| P2 | 32.2: Lazy Loading, Code Splitting & Asset Optimization | 2-3 hari | Selesai |
| P1 | 32.3: Automated Stress Test & Load Testing Suite | 3-4 hari | Selesai |
| P3 | 32.4: Horizontal Scaling Architecture Blueprint | 2-3 hari | Selesai |
| P0 | 33.1: RAPBS Multi-Year Budget Planning & Variance | 4-5 hari | Selesai ✅ |
| P1 | 33.2: Procurement & Purchase Order Workflow | 3-4 hari | Selesai ✅ |
| P0 | 33.3: Scholarship & Grant Management Portal | 3-4 hari | Selesai ✅ |
| P2 | 33.4: Cost Center Accounting & Budget Allocation | 2-3 hari | Selesai ✅ |
| P0 | 34.1: Student Mental Health & Wellbeing Screening | 4-5 hari | Selesai ✅ |
| P0 | 34.2: Positive Behavior & Character Report Card (P5) | 3-4 hari | Selesai ✅ |
| P1 | 34.3: Parent-Teacher Conference (PTC) Scheduler | 2-3 hari | Selesai ✅ |
| P2 | 34.4: Student Lifecycle Orchestration Engine | 3-4 hari | Selesai ✅ |
| P0 | 35.1: Kurikulum Merdeka Competency Mapping (CP/TP/ATP) | 4-5 hari | Selesai ✅ |
| P1 | 35.2: Multi-Year Academic Planning & Graduation Pathway | 3-4 hari | Selesai ✅ |
| P2 | 35.3: SCORM/xAPI Learning Content Interoperability | 3-4 hari | Selesai ✅ |
| P2 | 35.4: Virtual Classroom & Video Conferencing Hub | 3-4 hari | Selesai ✅ |
| P1 | 36.1: 360-Degree Teacher Evaluation & EDOM | 4-5 hari | Selesai ✅ |
| P1 | 36.2: CPD/PKB Credit Tracker | 3-4 hari | Selesai ✅ |
| P3 | 36.3: School Leadership Succession Planning | 2-3 hari | Selesai ✅ |
| P2 | 36.4: Teacher Workload Balancing & Capacity Dashboard | 2-3 hari | Selesai ✅ |
| P1 | 37.1: WCAG 2.2 AA+ Accessibility Hardening | 3-4 hari | Selesai ✅ |
| P2 | 37.2: Advanced i18n & Arabic (RTL) Support | 3-4 hari | Selesai ✅ |
| P2 | 37.3: Enterprise Design System Component Library | 3-4 hari | Selesai ✅ |
| P3 | 37.4: Print-Optimized Stylesheet Engine | 2-3 hari | Selesai ✅ |
| P1 | 38.1: Production Environment & Secret Management | 2-3 hari | Selesai ✅ |
| P2 | 38.2: Database Seeding Strategy & Anonymization | 2-3 hari | Selesai ✅ |
| P1 | 38.3: Release Versioning & Rollback Strategy | 2-3 hari | Selesai ✅ |
| P0 | 38.4: Comprehensive E2E Testing Suite | 3-4 hari | Selesai ✅ |

---

## Statistik Codebase Setelah Semua Fase Selesai (Target Enterprise Ultimate Suite Edition)

| Metrik | Sekarang | Target Enterprise (Fase 26) | Target Suite (Fase 32) | **Target Ultimate Suite (Fase 38)** |
| :--- | :---: | :---: | :---: | :---: |
| **Model Eloquent** | 193 | ~220 | ~245 | **~310** |
| **Controller** | 139 | ~165 | ~175 | **~215** |
| **Service Class** | 84 | ~100 | ~115 | **~150** |
| **Migration** | 137 | ~170 | ~195 | **~250** |
| **View Directory** | 72 | ~108 | ~145 | **~185** |
| **Blade Templates** | ~500 | ~610 | ~780 | **~950** |
| **Test Files** | ~120 | ~185 | ~240 | **~300** |
| **Seeder Files** | 30 | ~65 | ~80 | **~100** |
| **Factory Files** | 29 | ~65 | ~80 | **~100** |
| **Demo Records** | ~5000 | ~15.000+ | ~25.000+ | **~35.000+** |

---

> [!IMPORTANT]
> Seluruh adopsi arsitektur enterprise di atas dirancang **Enterprise Ultimate Suite Edition (Localhost Ready)** -- dapat dijalankan 100% menggunakan `php artisan serve` + SQLite/Database lokal, tanpa mengharuskan server fisik multi-node atau Kubernetes, namun memiliki kesiapan struktur (architecture readiness) tingkat tinggi untuk portofolio teknologi berstandar industri.

> [!TIP]
> **Urutan Implementasi Rekomendasi (Fase 33-38):**
> 1. **Fase 33** -- Financial ERP (RAPBS, Procurement, Scholarship) sebagai fondasi tata kelola keuangan transparan.
> 2. **Fase 34** -- Student Growth (Wellbeing, P5, PTC) untuk penguatan dimensi pertumbuhan holistik siswa.
> 3. **Fase 35** -- Academic Intelligence (Kurikulum Merdeka, SCORM, Virtual Classroom) untuk kepatuhan regulasi pendidikan nasional.
> 4. **Fase 36** -- Human Capital Excellence (360-Eval, CPD, Succession) untuk pengembangan profesionalisme guru.
> 5. **Fase 37** -- UX Mastery (Accessibility, i18n, Design System) untuk standar kualitas antarmuka berstandar internasional.
> 6. **Fase 38** -- Deployment Blueprint (Environment, Versioning, E2E Testing) untuk kesiapan deployment produksi.

