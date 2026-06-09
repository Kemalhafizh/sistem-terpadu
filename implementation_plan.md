# 🚀 Rencana Pengembangan Localhost — Sistem Terpadu SMA Islam Sultan Agung 1 Semarang

> **Konteks Proyek:** Platform **Sistem Terpadu (SISTA)** ini dikembangkan **khusus dan eksklusif untuk SMA Islam Sultan Agung 1 Semarang** — sekolah Islam di bawah naungan **Yayasan Badan Wakaf Sultan Agung (YBWSA)** Semarang. Seluruh fitur, terminologi, alur kerja, dan integrasi keislaman dirancang spesifik untuk kebutuhan operasional SMA ISA 1.

> **Fokus Dokumen:** Seluruh pengembangan di bawah ini dikerjakan **full di localhost** — tanpa deployment, tanpa mobile app, tanpa UAT dengan user nyata. Tujuannya: **menambah fitur baru yang bermakna, memperbaiki fitur yang sudah ada, memperluas data demo, dan memoles UI/UX** agar platform benar-benar lengkap dan siap dipamerkan sebagai portofolio.

---

## 📊 Statistik Codebase Saat Ini (Baseline)

| Metrik | Jumlah |
| :--- | :---: |
| **Model Eloquent** | 107 |
| **Controller** | 68 (root) + 32 (subdirectories) = **~100** |
| **Service Class** | 31 + 2 (Integration/) = **33** |
| **Migration** | 89 |
| **View Directory** | 57 |
| **Blade Templates** | ~289+ |
| **Test Files** | ~78 |
| **Seeder Files** | 18 |
| **Factory Files** | 11 |
| **Policies** | 23 |
| **API Resources** | 13 |
| **Middleware** | 9 |
| **Events (Broadcast)** | 5 |
| **Bahasa (i18n)** | 3 (ID, EN, AR) — masing-masing 6 file |
| **Dokumentasi** | 27 file di `docs/` |
| **Registered Routes** | ~300+ |
| **Demo Records (Seeder)** | ~860 records across 21 tables |

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

**Fokus:** Menambah modul-modul baru yang sangat relevan untuk SMA Islam Sultan Agung 1 — terutama yang berkaitan dengan identitas keislaman sekolah dan kebutuhan akademik yang belum ter-cover.

---

### 🕌 Modul 11.1: Sistem Tahfidz Al-Qur'an Komprehensif

**Status Saat Ini:** Sudah ada model `TahfidzLog`, controller `TahfidzController`, view `tahfidz/`, dan seeder dasar. Tapi fiturnya masih sangat basic — hanya input/view setoran hafalan.

**Apa yang Perlu Ditambah:**

#### Mengapa Penting untuk SMA ISA 1?
SMA Islam Sultan Agung 1 memiliki **program unggulan tahfidz** dengan kelas khusus (X/XI/XII-Tahfidz). Program ini membutuhkan tracking yang detail per juz, per halaman, per ayat — bukan hanya log sederhana. Guru tahfidz perlu dashboard khusus untuk memantau progress seluruh siswa tahfidz.

#### Rencana Implementasi

##### [NEW] Migration: `create_tahfidz_targets_table`
```php
Schema::create('tahfidz_targets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('students');
    $table->foreignId('academic_year_id')->constrained('academic_years');
    $table->integer('target_juz');                          // Target hafalan juz per semester
    $table->integer('target_pages')->nullable();            // Target dalam halaman
    $table->integer('achieved_juz')->default(0);            // Pencapaian actual
    $table->integer('achieved_pages')->default(0);
    $table->enum('status', ['on_track', 'behind', 'exceeded', 'completed'])->default('on_track');
    $table->text('notes')->nullable();                      // Catatan guru tahfidz
    $table->timestamps();

    $table->unique(['student_id', 'academic_year_id']);
});
```

##### [MODIFY] `app/Http/Controllers/TahfidzController.php`
```php
// Tambahkan method baru:
public function dashboard()              // Dashboard guru tahfidz: ringkasan per kelas, target vs actual
public function progressChart($student)  // Chart progress hafalan siswa: juz by month (line chart)
public function setTarget(Request $r)    // Set target semester per siswa
public function bulkInput()              // Input setoran hafalan massal (per kelas sekaligus)
public function exportReport()           // Export laporan tahfidz per kelas (PDF)
public function leaderboard()            // Ranking siswa tahfidz: siapa paling banyak hafalan
```

##### [MODIFY] `resources/views/tahfidz/`
```
resources/views/tahfidz/
├── dashboard.blade.php                 # [NEW] Dashboard guru tahfidz: cards per kelas, chart progress
├── show.blade.php                      # [EXISTING] Detail setoran per siswa → tambah progress bar juz
├── bulk-input.blade.php                # [NEW] Form input setoran massal per kelas
├── leaderboard.blade.php               # [NEW] Ranking siswa tahfidz (integrasi gamifikasi)
├── report.blade.php                    # [NEW] Laporan tahfidz per semester (preview sebelum PDF)
└── target-setting.blade.php            # [NEW] Form set target per siswa per semester
```

##### [NEW] `app/Services/TahfidzService.php`
```php
class TahfidzService
{
    public function getClassProgress(int $classroomId): Collection;     // Progress per kelas
    public function getStudentJuzBreakdown(int $studentId): array;      // Detail per juz (1-30)
    public function calculateCompletionPercentage(int $studentId): float;
    public function checkAndUpdateTargetStatus(int $studentId): void;   // Update status: on_track/behind/exceeded
    public function generateSemesterReport(int $classroomId): array;    // Data untuk PDF laporan
}
```

##### Demo Data (Seeder)
```php
// Untuk 6 siswa kelas Tahfidz:
// - 2 siswa: sudah hafal 5 juz (on_track)
// - 2 siswa: sudah hafal 3 juz (behind target 5)
// - 1 siswa: sudah hafal 7 juz (exceeded!)
// - 1 siswa: baru mulai, 1 juz (behind)
// Masing-masing dengan ~10-20 log setoran selama 1 semester
```

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

### 📊 Modul 11.4: Analisis Butir Soal (Item Analysis) untuk CBT

**Status Saat Ini:** CBT (Computer-Based Testing) sudah lengkap — buat soal, buat ujian, siswa mengerjakan, auto-grading, timer, anti-cheat. Tapi setelah ujian selesai, belum ada analisis kualitas soal.

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

### 🎒 Modul 11.5: Presensi Kegiatan & Acara Sekolah

**Status Saat Ini:** Absensi hanya untuk KBM (Kegiatan Belajar Mengajar) reguler di kelas. Belum ada sistem presensi untuk kegiatan lain.

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

### 🔧 Modul 12.2: Penguatan Jadwal Pelajaran

**Status Saat Ini:** Ada `ScheduleController` dengan preset, grid view, dan CRUD. Tapi belum ada: deteksi bentrok, view per guru, view per ruangan, dan print jadwal.

#### Rencana Implementasi

##### [MODIFY] `app/Http/Controllers/ScheduleController.php`
```php
// Tambahkan method baru:
public function teacherSchedule($userId)      // Jadwal per guru (filter by teacher)
public function roomSchedule($roomId)         // Jadwal per ruangan
public function detectConflicts()             // Deteksi bentrok: guru sama di 2 kelas, ruangan ganda
public function printClassSchedule($classId)  // Print jadwal kelas (format PDF landscape)
public function printTeacherSchedule($userId) // Print jadwal guru
public function printMasterSchedule()         // Print jadwal master seluruh sekolah
```

##### [NEW] `resources/views/schedules/`
```
resources/views/schedules/
├── grid.blade.php                  # [EXISTING] → Tambah: warna per mapel, tooltip guru+ruangan
├── teacher-view.blade.php          # [NEW] Jadwal per guru: grid minggu ini, warna per kelas
├── room-view.blade.php             # [NEW] Jadwal per ruangan: siapa pakai jam berapa
├── conflicts.blade.php             # [NEW] List bentrok: guru ganda, ruangan ganda — dengan alert merah
├── print-class.blade.php           # [NEW] Template print: jadwal kelas landscape (PDF)
└── print-master.blade.php          # [NEW] Template print: jadwal master seluruh sekolah
```

---

### 🔧 Modul 12.3: Penguatan E-Learning & Materi Pembelajaran

**Status Saat Ini:** E-Learning sudah cukup lengkap (kelas, materi, assignment, submission, grading, secure mode). Tapi belum ada: diskusi per materi, announcement per kelas, progress tracking siswa.

#### Rencana Implementasi

##### [MODIFY] `app/Http/Controllers/ElearningController.php`
```php
// Tambahkan method baru:
public function classAnnouncement($classId, Request $r)    // Post pengumuman ke kelas e-learning
public function studentProgress($classId, $studentId)      // Progress siswa: materi dibaca, tugas dikerjakan
public function classProgress($classId)                    // Overview: % siswa yang sudah baca materi, submit tugas
public function duplicateClass($classId)                   // Duplikat kelas (untuk semester baru)
public function archiveClass($classId)                     // Arsipkan kelas semester lalu
```

##### [MODIFY] Views
```
resources/views/elearning/
├── class.blade.php                 # [EXISTING] → Tambah: section announcement di atas
│                                   #            → Tambah: progress bar per materi (sudah dibaca / belum)
├── student-progress.blade.php      # [NEW] Detail per siswa: checklist materi dibaca, tugas submitted, nilai
├── class-progress.blade.php        # [NEW] Overview kelas: chart % completion, list siswa + status
```

---

### 🔧 Modul 12.4: Penguatan Modul Perpustakaan

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

**Status Saat Ini:** Finance sudah lengkap (billing, payment, bulk generate, mock gateway, receipt, accounting). Tapi belum ada: laporan tunggakan per kelas, proyeksi pendapatan, surat tagihan otomatis.

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

**Status Saat Ini:** Pengumuman sudah ada tapi sangat basic (judul, konten, target role/kelas). Belum ada: kategori, lampiran, pin ke dashboard, priority level.

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
| 🟠 **P1** | 11.2: Mutaba'ah Ibadah Lengkap | 2-3 hari | 🚧 In Progress |
| 🟠 **P1** | 11.1: Tahfidz Komprehensif | 2-3 hari | ⬜ TODO |
| 🟠 **P1** | 11.5: Presensi Kegiatan Sekolah | 2-3 hari | ⬜ TODO |
| 🟠 **P1** | 12.2: Penguatan Jadwal | 2 hari | ⬜ TODO |
| 🟡 **P2** | 11.4: Item Analysis CBT | 2-3 hari | ⬜ TODO |
| 🟡 **P2** | 12.3: Penguatan E-Learning | 2 hari | ⬜ TODO |
| 🟡 **P2** | 12.4: Penguatan Perpustakaan | 2 hari | ⬜ TODO |
| 🟡 **P2** | 12.5: Penguatan Keuangan | 2 hari | ⬜ TODO |
| 🟡 **P2** | 15.1: Notulensi Rapat | 2-3 hari | ⬜ TODO |
| 🟢 **P3** | 15.2: Pengumuman Rich | 1-2 hari | ⬜ TODO |
| 🟢 **P3** | 15.3: Todo List Guru | 1-2 hari | ⬜ TODO |
| 🟢 **P3** | 14.1: UI Consistency Audit | 3-4 hari | ⬜ TODO |
| 🟢 **P3** | 14.2: Animasi & Micro-Interactions | 2 hari | ⬜ TODO |
| 🟢 **P3** | 14.3: Error & Empty State Pages | 1-2 hari | ⬜ TODO |
| 🟢 **P3** | 14.4: Factory & Test Expansion | 3-4 hari | ⬜ TODO |

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

---

## 📊 Statistik Codebase Setelah Semua Fase Selesai (Target)

| Metrik | Sekarang | Target |
| :--- | :---: | :---: |
| **Model Eloquent** | 107 | ~120 |
| **Controller** | ~100 | ~112 |
| **Service Class** | 33 | ~40 |
| **Migration** | 89 | ~100 |
| **View Directory** | 57 | ~65 |
| **Blade Templates** | ~289 | ~340 |
| **Test Files** | ~78 | ~90 |
| **Seeder Files** | 18 | ~30 |
| **Factory Files** | 11 | ~24 |
| **Demo Records** | ~860 | ~3000+ |

---

> [!IMPORTANT]
> Seluruh pengembangan di atas dijalankan **100% di localhost** menggunakan `php artisan serve` + SQLite. Tidak membutuhkan server, domain, atau layanan pihak ketiga. Semua fitur bisa dibangun, ditest, dan di-demo langsung dari laptop developer.

> [!TIP]
> **Mulai dari P0 (Prioritas Tertinggi):**
> 1. Modul 11.3 (Penilaian Harian) (Selesai ✅) — karena ini fitur akademik inti yang belum ada
> 2. Modul 12.1 (Dashboard Enhancement) (Selesai ✅) — karena dashboard adalah halaman pertama yang dilihat user
> 3. Modul 13.1 (Mega Seeder) (Selesai ✅) — karena data demo yang rich membuat semua fitur terlihat hidup
> 4. Modul 11.2 (Mutaba'ah Ibadah Lengkap) (Selesai ✅) — karena tracking ibadah adalah program unggulan keagamaan
