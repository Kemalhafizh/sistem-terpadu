# 🚀 Rencana Pengembangan & Pembaharuan Lanjutan — Sistem Terpadu SMA Islam Sultan Agung 1 Semarang (Fase 9 & 10)

> **Konteks:** Seluruh fitur dari **Fase 1 s/d Fase 8** telah **SELESAI 100%** (✅ DONE). Platform ini dikembangkan **khusus dan eksklusif untuk SMA Islam Sultan Agung 1 Semarang** — bukan produk SaaS generik. Saat ini memiliki **96 model Eloquent, 70 controller, 24 service class, 81 migration, 54 direktori view, 302 file app, 5 broadcast events, REST API v1, WebSocket real-time (Laravel Reverb), CBT, Smart Alerts, Approval Workflows, Report Builder, e-Portfolio, dan Security Monitoring**. Dokumen ini menyusun **Fase 9 & 10** — fase penyempurnaan akhir untuk menjadikan platform ini benar-benar sempurna dan siap dipakai operasional penuh oleh seluruh civitas akademika SMA Islam Sultan Agung 1 Semarang.

---

## 📊 Ringkasan Pencapaian Lengkap (Fase 1–8 COMPLETED)

| Fase | Cakupan | Jumlah Fitur |
| :---: | :--- | :---: |
| **1** | Keamanan (2FA, Enkripsi, Audit, RBAC), Arsitektur (Service Pattern), DB Optimization, Testing (140+ Pest), Docker, API Docs | ~20 |
| **1** | UI/UX (DataTables, Ctrl+K, Skeleton, Dashboard Drag-Drop), E-Rapor, Billing, Auto-Backup, SPMB, Library, E-Learning, HRD/Payroll, Accounting, Alumni, Career | ~25 |
| **2** | WhatsApp Gateway (Fonnte + Sandbox), In-App Alert Center, Auto-Notif (Absensi/BK/SPP) | ~8 |
| **3–4** | Kalender Akademik, Jurnal Mengajar, Analitik Eksekutif, Surat Digital, Keamanan Lanjutan, UKS, Konseling BK, PWA | ~30 |
| **5–6** | Forum Diskusi, Survei Digital, Inventaris Aset+Peminjaman, Ekskul & OSIS, EDS, Portal Ortu, Kurikulum/Silabus, Helpdesk, Compliance, CMS Landing Page | ~35 |
| **7–8** | Real-Time WebSocket (Reverb), REST API v1, Smart Alerts, CBT/Ujian Online, Workflow Engine, Report Builder, e-Portfolio, Security Monitor, CI/CD | ~30 |
| | **TOTAL** | **~148 fitur** |

### Statistik Codebase Saat Ini

| Metrik | Jumlah |
| :--- | :---: |
| Model Eloquent | 96 |
| Controller | 70 |
| Service Class | 24 |
| Migration | 81 |
| View Directory | 54 |
| Broadcast Events | 5 |
| Total File di `/app` | 302 |

---

## 🗺️ Peta Jalan Pengembangan Baru (Fase 9 & 10)

---

## 📦 FASE 9: AI-Powered, Integrasi Pemerintah & Pengalaman Pengguna Premium

---

### 🤖 Modul 1: AI Assistant Chatbot Sekolah (Lokal) (✅ SELESAI)

**Tujuan:** Menyediakan chatbot AI yang tertanam di setiap halaman platform. Chatbot ini menjawab pertanyaan umum (FAQ), membantu navigasi cepat ke fitur tertentu, dan memungkinkan query data menggunakan bahasa alami. Contoh: "Berapa siswa yang belum bayar SPP bulan ini?", "Buka halaman input nilai kelas X-A", "Siapa yang paling sering alpa minggu ini?".

#### Mengapa Penting?
Dengan 148+ fitur dan 54 halaman, pengguna baru (terutama guru dan orang tua) sering kesulitan menemukan fitur. AI chatbot menjadi *concierge* digital yang mempercepat adopsi dan mengurangi beban helpdesk internal. Query data natural language memungkinkan manajemen SMA ISA 1 mendapatkan insight tanpa harus navigasi ke halaman analitik.

---

#### [NEW] Migration: `create_chatbot_tables`

```php
Schema::create('chatbot_faqs', function (Blueprint $table) {
    $table->id();
    $table->string('question');                         // "Bagaimana cara input nilai?"
    $table->text('answer');                              // "Buka menu Akademik > Input Nilai..."
    $table->string('category')->nullable();              // 'akademik', 'keuangan', 'teknis', 'umum'
    $table->json('keywords')->nullable();                // ["nilai", "input", "grade"]
    $table->integer('usage_count')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index('category');
    $table->fullText(['question', 'answer']);
});

Schema::create('chatbot_conversations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users');
    $table->text('user_message');
    $table->text('bot_response');
    $table->string('intent')->nullable();                // 'faq', 'navigate', 'query_data', 'unknown'
    $table->json('context_data')->nullable();
    $table->float('confidence')->nullable();
    $table->boolean('was_helpful')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'created_at']);
    $table->index('intent');
});
```

#### [NEW] `app/Services/ChatbotService.php`
```php
class ChatbotService
{
    /**
     * Proses pesan user, tentukan intent, dan berikan respons.
     * Intent detection: keyword matching + fuzzy search.
     */
    public function processMessage(User $user, string $message): array;

    /**
     * Intent: FAQ — Cari jawaban dari chatbot_faqs (full-text search).
     */
    public function handleFaqIntent(string $message): ?string;

    /**
     * Intent: Navigate — Deteksi nama fitur dan return URL target.
     * "buka absensi" → "/attendances", "lihat jadwal" → "/schedules/grid"
     */
    public function handleNavigateIntent(string $message): ?array;

    /**
     * Intent: Query Data — Parse pertanyaan dan jalankan predefined query.
     * "berapa siswa alpa hari ini" → Attendance::where(...)->count()
     * Menggunakan mapping keyword → Eloquent query (BUKAN raw SQL — aman).
     */
    public function handleDataQueryIntent(User $user, string $message): ?array;
}
```

**Contoh Predefined Data Queries (aman, tanpa raw SQL):**

| Pertanyaan Natural | Query | Template Respons |
| :--- | :--- | :--- |
| "berapa siswa alpa hari ini" | `Attendance::where('date', today())->where('status', 'A')->count()` | "Jumlah siswa alpa hari ini: **{result}** siswa." |
| "total tunggakan SPP" | `Billing::where('remaining_balance', '>', 0)->count()` | "Total tagihan belum lunas: **{result}** tagihan." |
| "berapa total siswa aktif" | `Student::where('status', 'active')->count()` | "Total siswa aktif: **{result}** siswa." |
| "tiket helpdesk yang open" | `Ticket::where('status', 'open')->count()` | "Tiket helpdesk yang masih open: **{result}** tiket." |
| "guru yang belum isi jurnal hari ini" | `TeachingJournal::...` | "Guru yang belum mengisi jurnal hari ini: **{result}** guru." |

#### [NEW] `app/Http/Controllers/ChatbotController.php`
* `send(Request $request)` — Endpoint AJAX: terima pesan, proses via ChatbotService, return respons JSON.
* `feedback(Request $request)` — User memberi feedback apakah jawaban membantu (thumbs up/down).
* `manageFaqs()` — Admin CRUD FAQ database.

#### [NEW] Views
* `resources/views/components/chatbot-widget.blade.php` — Floating chat bubble di kanan bawah. Klik → expand chat panel. AlpineJS-powered. Animasi smooth open/close. Auto-scroll ke pesan terbaru.
* `resources/views/admin/chatbot/faqs.blade.php` — DataTable FAQ: pertanyaan, jawaban, kategori, usage count. CRUD modal.

#### [MODIFY] `resources/views/layouts/app.blade.php`
* Include `<x-chatbot-widget />` di footer untuk semua halaman.

#### Routes Baru
```php
Route::middleware('auth')->prefix('chatbot')->name('chatbot.')->group(function () {
    Route::post('send', [ChatbotController::class, 'send'])->name('send');
    Route::post('feedback', [ChatbotController::class, 'feedback'])->name('feedback');
});

Route::middleware('role:admin,superadmin')->group(function () {
    Route::get('admin/chatbot/faqs', [ChatbotController::class, 'manageFaqs'])->name('admin.chatbot.faqs');
    Route::post('admin/chatbot/faqs', [ChatbotController::class, 'storeFaq'])->name('admin.chatbot.faqs.store');
    Route::put('admin/chatbot/faqs/{faq}', [ChatbotController::class, 'updateFaq'])->name('admin.chatbot.faqs.update');
    Route::delete('admin/chatbot/faqs/{faq}', [ChatbotController::class, 'destroyFaq'])->name('admin.chatbot.faqs.destroy');
});
```

---

### 🔗 Modul 2: Integrasi Dapodik & Sistem Pemerintah (✅ SELESAI)

**Tujuan:** Membangun connector untuk sinkronisasi data SMA ISA 1 dengan sistem pemerintah: **Dapodik** (Kemendikbud), **eSPP**, dan **EMIS** (Kemenag — karena SMA Islam di bawah naungan yayasan). Termasuk webhook untuk integrasi dengan sistem internal yayasan Sultan Agung.

#### Mengapa Penting?
SMA ISA 1 wajib melaporkan data ke Dapodik setiap semester. Saat ini admin TU harus input ulang data secara manual — memakan puluhan jam. Integrasi otomatis menghemat waktu dan mengurangi kesalahan data. Karena di bawah yayasan, integrasi EMIS (Kemenag) juga relevan.

---

#### [NEW] `app/Services/Integration/DapodikService.php`
```php
class DapodikService
{
    public function exportStudentData(): array;                  // Format data siswa sesuai schema Dapodik
    public function exportTeacherData(): array;                  // Format data PTK (Pendidik & Tenaga Kependidikan)
    public function exportClassroomData(): array;                // Format rombongan belajar
    public function exportAttendanceData(string $month): array;  // Rekapitulasi kehadiran
    public function generateDapodikExcel(): BinaryFileResponse;  // Export Excel format Dapodik
    public function validateAgainstDapodik(array $data): array;  // Validasi kelengkapan field wajib Dapodik
}
```

#### [NEW] `app/Services/Integration/EmisService.php`
```php
class EmisService
{
    public function exportToEmisFormat(): array;                  // Format EMIS Kemenag
    public function generateEmisExcel(): BinaryFileResponse;
}
```

#### [NEW] Migration: `create_integration_tables`
```php
Schema::create('integration_configs', function (Blueprint $table) {
    $table->id();
    $table->string('provider');                          // 'dapodik', 'emis', 'yayasan', 'midtrans'
    $table->string('name');
    $table->json('credentials')->nullable();             // Encrypted: API key, secret
    $table->json('settings')->nullable();
    $table->boolean('is_active')->default(false);
    $table->timestamp('last_sync_at')->nullable();
    $table->timestamps();
});

Schema::create('integration_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('config_id')->constrained('integration_configs');
    $table->enum('direction', ['export', 'import', 'sync']);
    $table->enum('status', ['success', 'partial', 'failed']);
    $table->integer('records_processed')->default(0);
    $table->integer('records_failed')->default(0);
    $table->json('error_details')->nullable();
    $table->text('summary')->nullable();
    $table->timestamps();
});

Schema::create('webhooks', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('name');
    $table->string('url');
    $table->string('secret')->nullable();
    $table->json('events');                               // ['student.created', 'billing.paid', 'attendance.recorded']
    $table->boolean('is_active')->default(true);
    $table->integer('failure_count')->default(0);
    $table->timestamp('last_triggered_at')->nullable();
    $table->timestamps();
});
```

#### [NEW] `app/Http/Controllers/IntegrationController.php`
* `index()` — Dashboard integrasi: status koneksi Dapodik/EMIS/Yayasan, last sync, log history.
* `configDapodik()` — Konfigurasi export Dapodik: mapping field, validasi, preview data.
* `exportDapodik()` — Trigger export dan download file format Dapodik.
* `configEmis()` — Konfigurasi export EMIS Kemenag.
* `webhooks()` — CRUD webhook untuk integrasi dengan sistem yayasan.
* `logs()` — Riwayat sinkronisasi.

#### [NEW] Views: `resources/views/integrations/`
* `index.blade.php` — Dashboard card per provider: ikon, status, last sync, tombol sync/configure.
* `dapodik.blade.php` — Konfigurasi + preview data + tombol export.
* `webhooks.blade.php` — DataTable webhook + modal CRUD + tombol "Test Ping".
* `logs.blade.php` — Timeline log sinkronisasi.

#### Routes Baru
```php
Route::middleware('role:admin,superadmin')->prefix('integrations')->name('integrations.')->group(function () {
    Route::get('/', [IntegrationController::class, 'index'])->name('index');
    Route::get('dapodik', [IntegrationController::class, 'configDapodik'])->name('dapodik');
    Route::post('dapodik/export', [IntegrationController::class, 'exportDapodik'])->name('dapodik.export');
    Route::get('emis', [IntegrationController::class, 'configEmis'])->name('emis');
    Route::post('emis/export', [IntegrationController::class, 'exportEmis'])->name('emis.export');
    Route::resource('webhooks', IntegrationController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('webhooks/{webhook}/test', [IntegrationController::class, 'testWebhook'])->name('webhooks.test');
    Route::get('logs', [IntegrationController::class, 'logs'])->name('logs');
});
```

---

### 💳 Modul 3: Payment Gateway Produksi (Midtrans) (✅ SELESAI)

**Tujuan:** Menggantikan payment gateway mock dengan integrasi produksi **Midtrans**. Orang tua siswa SMA ISA 1 bisa bayar SPP online via transfer bank (VA BCA/BNI/Mandiri/BRI), e-wallet (GoPay, ShopeePay), QRIS, atau kartu kredit/debit. Auto-reconciliation dan payment reminder otomatis.

#### Mengapa Penting?
Payment gateway mock hanya untuk demo/testing. Integrasi Midtrans produksi memungkinkan orang tua bayar SPP kapan saja, di mana saja — meningkatkan efisiensi penagihan dan cash flow sekolah secara signifikan.

---

#### [MODIFY] `app/Services/PaymentGatewayService.php`
Refactor total untuk produksi Midtrans:
```php
class PaymentGatewayService
{
    public function createTransaction(Billing $billing, string $paymentMethod): array;
    public function createVirtualAccount(Billing $billing, string $bank): array;    // BCA, BNI, Mandiri, BRI
    public function createQris(Billing $billing): array;
    public function createEwallet(Billing $billing, string $provider): array;       // gopay, shopeepay
    public function handleWebhookNotification(array $payload): void;                // Midtrans server callback
    public function checkTransactionStatus(string $orderId): array;
    public function cancelTransaction(string $orderId): void;
    public function getReconciliationReport(string $startDate, string $endDate): Collection;
}
```

#### [NEW] Migration: `create_payment_transactions_table`
```php
Schema::create('payment_transactions', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('order_id')->unique();                    // "SPP-2026-{billing_id}-{timestamp}"
    $table->foreignId('billing_id')->constrained('billings');
    $table->foreignId('student_id')->constrained('students');
    $table->decimal('gross_amount', 15, 2);
    $table->string('payment_type');                           // 'bank_transfer', 'qris', 'ewallet', 'credit_card'
    $table->string('payment_channel')->nullable();            // 'bca_va', 'gopay', 'shopeepay'
    $table->string('va_number')->nullable();
    $table->string('qr_code_url')->nullable();
    $table->enum('transaction_status', ['pending', 'settlement', 'capture', 'deny', 'cancel', 'expire', 'refund'])->default('pending');
    $table->string('fraud_status')->nullable();
    $table->json('gateway_response')->nullable();
    $table->timestamp('settlement_time')->nullable();
    $table->timestamp('expiry_time')->nullable();
    $table->timestamps();

    $table->index(['billing_id', 'transaction_status']);
    $table->index('order_id');
});
```

#### [MODIFY] `app/Http/Controllers/Api/MidtransWebhookController.php`
* Verifikasi Midtrans server key signature.
* Update status transaksi + billing + payment otomatis.
* Auto-kirim notifikasi WhatsApp ke orang tua: "Pembayaran SPP bulan Mei sebesar Rp X untuk [Nama Anak] telah berhasil diterima. Terima kasih."

#### [MODIFY] `resources/views/finance/` — Halaman Checkout
* Halaman checkout siswa/ortu: pilih metode pembayaran (VA bank, QRIS scan, e-wallet).
* Tampilkan instruksi pembayaran spesifik per metode.
* Status pembayaran real-time (via WebSocket: `PaymentSettled` event).

#### [NEW] `app/Console/Commands/CheckPendingPayments.php`
* Scheduler setiap jam: cek transaksi pending > 24 jam → auto-expire.
* Kirim reminder ke orang tua untuk transaksi pending > 6 jam.

---

### 🌍 Modul 4: Internationalization (i18n) & Aksesibilitas (✅ SELESAI)

**Tujuan:** Menambahkan dukungan multi-bahasa (**Bahasa Indonesia**, **English**, **العربية**) dan meningkatkan aksesibilitas sesuai standar WCAG 2.1 Level AA. Termasuk dark mode global yang konsisten di seluruh halaman.

#### Mengapa Penting?
SMA Islam Sultan Agung 1 mengajarkan bahasa Arab sebagai bagian kurikulum keagamaan — dukungan bahasa Arab sangat relevan. English support berguna untuk internalisasi dan jika ada tamu/stakeholder asing. Aksesibilitas memastikan platform dapat digunakan oleh semua pengguna termasuk yang memiliki keterbatasan.

---

#### Implementasi i18n

**Struktur file bahasa:**
```
resources/lang/
├── id/                              # Bahasa Indonesia (default)
│   ├── auth.php
│   ├── dashboard.php
│   ├── finance.php
│   ├── academic.php
│   ├── navigation.php
│   └── ... (per modul)
├── en/                              # English
│   └── ...
└── ar/                              # العربية
    └── ...
```

#### [NEW] `app/Http/Middleware/SetLocale.php`
```php
public function handle(Request $request, Closure $next)
{
    $locale = $request->user()?->preferred_locale ?? session('locale', config('app.locale'));
    app()->setLocale($locale);
    return $next($request);
}
```

#### [MODIFY] `app/Models/User.php`
* Tambah kolom `preferred_locale` (default: `'id'`).
* Tambah kolom `preferred_theme` (default: `'system'`, options: `'light'`, `'dark'`, `'system'`).

#### [MODIFY] `resources/views/layouts/app.blade.php`
* Language switcher di header: dropdown 🇮🇩 ID / 🇬🇧 EN / 🇸🇦 AR.
* Dark mode toggle: ikon matahari/bulan. `prefers-color-scheme` + manual override.
* `dir="rtl"` otomatis jika locale = `ar`.

#### [MODIFY] Semua View Templates
* Ganti hardcoded text → `{{ __('module.key') }}`.
* Contoh: `"Daftar Siswa"` → `{{ __('academic.student_list') }}`.

#### Aksesibilitas (WCAG 2.1 AA)
* **Semua elemen interaktif:** `aria-label`, `aria-describedby`, `role` attributes.
* **Keyboard Navigation:** Modal, dropdown, form sepenuhnya keyboard-accessible.
* **Color Contrast:** Audit semua warna — minimum ratio 4.5:1.
* **Focus Indicators:** Visible focus ring pada semua elemen interaktif.
* **Skip Navigation:** Link "Skip to main content" di awal halaman.
* **Form Labels:** Semua `<input>` terhubung ke `<label>`.
* **Alt Text:** Semua `<img>` memiliki `alt` deskriptif.

---

### 🏆 Modul 5: Gamifikasi & Engagement System Siswa (✅ SELESAI)

**Tujuan:** Membangun sistem gamifikasi untuk memotivasi siswa SMA ISA 1: achievement badges otomatis, leaderboard per kelas, XP (experience points), streak rewards (kehadiran berturut-turut), dan tantangan islami (hafalan, mutaba'ah).

#### Mengapa Penting?
Gamifikasi terbukti meningkatkan engagement siswa hingga 60%. Siswa SMA ISA 1 yang termotivasi lebih rajin hadir, tepat waktu mengerjakan tugas, aktif di forum, dan konsisten dalam mutaba'ah/tahfidz. Ini juga mendukung budaya kompetisi sehat antar kelas.

---

#### [NEW] Migration: `create_gamification_tables`

```php
Schema::create('achievement_definitions', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();                    // 'perfect_attendance_month', 'tahfidz_juz'
    $table->string('name');                               // "Absensi Sempurna (1 Bulan)"
    $table->text('description');
    $table->string('icon');                               // Emoji atau icon class
    $table->string('color')->default('#f59e0b');
    $table->enum('category', ['akademik', 'kehadiran', 'sosial', 'keislaman', 'khusus'])->default('akademik');
    $table->enum('rarity', ['common', 'rare', 'epic', 'legendary'])->default('common');
    $table->integer('xp_reward')->default(10);
    $table->json('criteria')->nullable();                 // {"type":"attendance_streak","value":30}
    $table->boolean('is_auto')->default(true);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('student_achievements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('students');
    $table->foreignId('achievement_id')->constrained('achievement_definitions');
    $table->date('earned_at');
    $table->text('context')->nullable();                  // "Hadir 30 hari berturut-turut di Mei 2026"
    $table->timestamps();

    $table->unique(['student_id', 'achievement_id', 'earned_at']);
});

Schema::create('student_xp_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('students');
    $table->integer('xp_amount');
    $table->string('source');                             // 'attendance', 'assignment', 'forum', 'achievement', 'tahfidz'
    $table->string('description');
    $table->timestamps();

    $table->index(['student_id', 'created_at']);
});
```

#### [MODIFY] `app/Models/Student.php`
* Tambah kolom `total_xp` (integer, default 0), `current_streak` (integer, default 0), `longest_streak` (integer, default 0).
* Relasi: `achievements()`, `xpLogs()`.
* Accessor: `getLevelAttribute()` — level dari XP (0-99 = Lv.1, 100-299 = Lv.2, dst).

#### [NEW] `app/Services/GamificationService.php`
```php
class GamificationService
{
    public function awardXp(Student $student, int $amount, string $source, string $description): void;
    public function checkAndAwardAchievements(Student $student): Collection;
    public function updateAttendanceStreak(Student $student, string $status): void;
    public function getLeaderboard(string $period = 'monthly', ?int $classroomId = null): Collection;
    public function getStudentProgress(Student $student): array;

    // Auto-triggered via Observer:
    public function onAttendanceRecorded(Student $student): void;
    public function onAssignmentSubmitted(Student $student): void;
    public function onForumReplyPosted(Student $student): void;
    public function onExamCompleted(Student $student, float $score): void;
    public function onTahfidzRecorded(Student $student): void;     // Khusus SMA ISA 1!
    public function onMutabaahCompleted(Student $student): void;   // Khusus SMA ISA 1!
}
```

**Achievement Definitions (Khusus SMA Islam Sultan Agung 1):**

| Code | Nama | Kriteria | Rarity | XP |
| :--- | :--- | :--- | :--- | :---: |
| `first_day` | Hari Pertama 🎒 | Login pertama kali | Common | 5 |
| `perfect_week` | Minggu Sempurna ⭐ | Hadir 5 hari penuh 1 minggu | Common | 15 |
| `perfect_month` | Bulan Sempurna 🌟 | Hadir full 1 bulan (0 alpa) | Rare | 50 |
| `streak_30` | Streak 30 Hari 🔥 | Hadir 30 hari berturut-turut | Epic | 100 |
| `top_scorer` | Juara Kelas 🏅 | Ranking 1 di kelas semester ini | Rare | 75 |
| `bookworm` | Kutu Buku 📚 | Pinjam 10+ buku perpustakaan | Rare | 40 |
| `forum_helper` | Penolong 🤝 | 20+ reply forum di-react "Helpful" | Rare | 60 |
| `tahfidz_juz` | Hafiz Juz 🕌 | Selesaikan hafalan 1 juz penuh | **Legendary** | 200 |
| `mutabaah_streak` | Istiqomah 🌙 | Mutaba'ah lengkap 30 hari berturut-turut | **Epic** | 120 |
| `quiz_ace` | Ace Ujian 💯 | Nilai 100 di ujian CBT | Epic | 80 |
| `early_bird` | Siswa Rajin ✅ | Submit tugas sebelum deadline 10 kali | Common | 20 |
| `sholat_champion` | Rajin Sholat 🕋 | Mutaba'ah sholat 5 waktu lengkap 30 hari | **Legendary** | 150 |

#### [NEW] `app/Http/Controllers/GamificationController.php`
* `leaderboard()` — Ranking XP per kelas/sekolah, filter bulanan/semua waktu. Podium top 3 dengan animasi.
* `myProgress()` — Progress siswa: XP bar, level, achievements, streak counter 🔥.
* `achievements()` — Katalog achievement: earned vs locked, progress bar.

#### [NEW] Views: `resources/views/gamification/`
* `leaderboard.blade.php` — Podium top 3 (🥇🥈🥉), tabel ranking, filter kelas/periode.
* `my-progress.blade.php` — XP progress bar, level badge, streak flame 🔥, achievement grid.
* `achievements.blade.php` — Katalog achievement card: ikon, nama, rarity badge warna (common→legendary).

#### [MODIFY] Dashboard Siswa
* Widget baru: XP bar + level, streak counter 🔥, 3 achievement terbaru.

#### Routes Baru
```php
Route::middleware('auth')->prefix('gamification')->name('gamification.')->group(function () {
    Route::get('leaderboard', [GamificationController::class, 'leaderboard'])->name('leaderboard');
    Route::get('my-progress', [GamificationController::class, 'myProgress'])->name('my-progress');
    Route::get('achievements', [GamificationController::class, 'achievements'])->name('achievements');
});
```

---

## 📦 FASE 10: Platform Maturity & Operational Excellence

---

### 🎨 Modul 6: Branding & Kustomisasi Tampilan Sekolah (✅ SELESAI)

**Tujuan:** Memungkinkan admin SMA ISA 1 mengubah tampilan platform (logo, warna tema, font, favicon, teks footer) langsung dari halaman Settings tanpa perlu coding. Berguna saat rebranding sekolah, pergantian tahun ajaran, atau event khusus.

#### Mengapa Penting?
Saat ini untuk mengubah logo atau warna harus edit file blade langsung. Admin sekolah perlu bisa mengkustomisasi tampilan secara mandiri — misalnya mengganti logo saat yayasan berganti identitas visual, atau mengubah warna tema saat bulan Ramadhan.

---

#### [NEW] Migration: `create_appearance_settings_table`
```php
Schema::create('appearance_settings', function (Blueprint $table) {
    $table->id();
    $table->string('app_name')->default('Sistem Terpadu SMA ISA 1');
    $table->string('logo_path')->nullable();
    $table->string('logo_dark_path')->nullable();         // Logo untuk dark mode
    $table->string('favicon_path')->nullable();
    $table->string('primary_color')->default('#3b82f6');
    $table->string('secondary_color')->default('#8b5cf6');
    $table->string('accent_color')->default('#06b6d4');
    $table->string('sidebar_color')->default('#0f172a');
    $table->string('font_family')->default('Inter');
    $table->text('custom_css')->nullable();               // CSS kustom tambahan
    $table->string('login_background_path')->nullable();
    $table->text('footer_text')->nullable();              // "© 2026 SMA Islam Sultan Agung 1 Semarang"
    $table->timestamps();
});
```

#### [NEW] `app/Services/AppearanceService.php`
```php
class AppearanceService
{
    public function getSettings(): AppearanceSetting;
    public function generateCssVariables(): string;        // Output CSS custom properties :root
    public function getAvailableFonts(): array;            // Inter, Poppins, Roboto, Outfit, dll
    public function saveSettings(array $data): void;
    public function resetToDefault(): void;
}
```

#### [MODIFY] `resources/views/layouts/app.blade.php`
* Inject CSS custom properties dari AppearanceService:
```html
<style>
    :root {
        --color-primary: {{ $appearance->primary_color }};
        --color-secondary: {{ $appearance->secondary_color }};
        --color-accent: {{ $appearance->accent_color }};
        --color-sidebar: {{ $appearance->sidebar_color }};
        --font-family: '{{ $appearance->font_family }}', sans-serif;
    }
</style>
```
* Logo dinamis: `<img src="{{ $appearance->logo_path ?? asset('images/logo.png') }}">`

#### [NEW] Views: `resources/views/admin/appearance/`
* `index.blade.php` — Visual editor: color picker per warna, font dropdown, logo/favicon upload, live preview panel di samping kanan. Tombol "Simpan" dan "Reset ke Default".

#### Routes Baru
```php
Route::middleware('role:admin,superadmin')->group(function () {
    Route::get('admin/appearance', [AppearanceController::class, 'index'])->name('admin.appearance.index');
    Route::put('admin/appearance', [AppearanceController::class, 'update'])->name('admin.appearance.update');
    Route::post('admin/appearance/reset', [AppearanceController::class, 'reset'])->name('admin.appearance.reset');
});
```

---

### 📥 Modul 7: Data Import/Export Hub Universal (✅ SELESAI)

**Tujuan:** Hub terpusat untuk import dan export data dari/ke semua modul. Import dari Excel/CSV dengan mapping kolom visual, validasi preview sebelum eksekusi, dan kemampuan rollback jika ada kesalahan.

#### Mengapa Penting?
Admin SMA ISA 1 rutin menerima data dari Dinas Pendidikan atau yayasan dalam format Excel. Saat ini data harus diinput ulang satu per satu. Hub import/export mempercepat proses dari berjam-jam menjadi beberapa menit, dengan validasi otomatis.

---

#### [NEW] `app/Services/ImportExportService.php`
```php
class ImportExportService
{
    public function getAvailableModules(): array;                    // students, employees, grades, billings, etc.
    public function getModuleColumns(string $module): array;        // Kolom yang bisa di-import
    public function parseUploadedFile(UploadedFile $file): array;   // Parse headers + preview 5 baris pertama
    public function validateMapping(string $module, array $mapping, array $data): array;
    public function executeImport(string $module, array $mapping, array $data): ImportResult;
    public function rollbackImport(int $importLogId): void;
    public function export(string $module, array $filters, string $format): BinaryFileResponse;
}
```

#### [NEW] Migration: `create_import_logs_table`
```php
Schema::create('import_logs', function (Blueprint $table) {
    $table->id();
    $table->string('module');                             // 'students', 'grades', 'billings'
    $table->foreignId('imported_by')->constrained('users');
    $table->string('file_name');
    $table->integer('total_rows');
    $table->integer('success_count');
    $table->integer('error_count');
    $table->json('error_details')->nullable();            // Per-row error descriptions
    $table->json('created_ids')->nullable();              // IDs yang dibuat (untuk rollback)
    $table->enum('status', ['processing', 'completed', 'failed', 'rolled_back'])->default('processing');
    $table->timestamps();
});
```

#### [NEW] `app/Http/Controllers/ImportExportController.php`
* `index()` — Hub: grid card per modul (Siswa, Guru, Nilai, SPP, dll.). Tombol Import / Export per card.
* `uploadFile(Request $request)` — Upload Excel/CSV, parse headers, return preview 5 rows.
* `mapColumns(Request $request)` — Mapping kolom: dropdown per kolom file → kolom database. Validasi real-time.
* `executeImport(Request $request)` — Jalankan import. Progress via WebSocket.
* `export(Request $request)` — Export data ke Excel/CSV/PDF.
* `importHistory()` — Riwayat import + tombol rollback.

#### [NEW] Views: `resources/views/import-export/`
* `index.blade.php` — Grid card per modul.
* `upload.blade.php` — Drag-drop upload area + preview tabel data.
* `mapping.blade.php` — Mapping visual: kolom file ↔ kolom database.
* `history.blade.php` — DataTable riwayat import: modul, tanggal, success/error count, tombol rollback.

#### Routes Baru
```php
Route::middleware('role:admin,superadmin,staf_tu')->prefix('import-export')->name('import-export.')->group(function () {
    Route::get('/', [ImportExportController::class, 'index'])->name('index');
    Route::post('upload', [ImportExportController::class, 'uploadFile'])->name('upload');
    Route::post('map', [ImportExportController::class, 'mapColumns'])->name('map');
    Route::post('execute', [ImportExportController::class, 'executeImport'])->name('execute');
    Route::post('export', [ImportExportController::class, 'export'])->name('export');
    Route::get('history', [ImportExportController::class, 'importHistory'])->name('history');
    Route::post('rollback/{log}', [ImportExportController::class, 'rollback'])->name('rollback');
});
```

---

### 📧 Modul 8: Scheduled Reporting & Email Digest (✅ SELESAI)

**Tujuan:** Sistem laporan otomatis terjadwal. Kepala Sekolah menerima ringkasan mingguan, admin menerima laporan harian, orang tua menerima rangkuman mingguan anak — semuanya otomatis via email dan/atau WhatsApp.

---

#### [NEW] Migration: `create_scheduled_reports_table`
```php
Schema::create('scheduled_reports', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('report_type');                       // 'daily_admin', 'weekly_kepsek', 'monthly_finance', 'weekly_parent'
    $table->string('cron_expression');                    // '0 7 * * *', '0 8 * * 1', '0 8 1 * *'
    $table->json('recipients');                           // [{"type":"role","value":"kepala_sekolah"}, {"type":"email","value":"kepsek@smaisa1.sch.id"}]
    $table->json('config')->nullable();
    $table->enum('delivery_method', ['email', 'whatsapp', 'both'])->default('email');
    $table->boolean('attach_pdf')->default(true);
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_sent_at')->nullable();
    $table->timestamps();
});
```

#### [NEW] `app/Services/DigestService.php`
```php
class DigestService
{
    public function generateDailyAdminDigest(): array;
        // Siswa alpa hari ini, tiket helpdesk baru, tunggakan baru, smart alerts
    public function generateWeeklyKepsekDigest(): array;
        // Tren kehadiran minggu ini, keuangan, kinerja guru (jurnal), compliance score
    public function generateMonthlyFinanceDigest(): array;
        // Rekap penerimaan, tunggakan, proyeksi bulan depan
    public function generateWeeklyParentDigest(Student $student): array;
        // Kehadiran anak minggu ini, nilai terbaru, poin BK, tugas pending
    public function renderDigestHtml(string $type, array $data): string;
    public function renderDigestPdf(string $type, array $data): PDF;
    public function sendDigest(ScheduledReport $report): void;
}
```

#### [NEW] `app/Console/Commands/SendScheduledReports.php`
* Scheduler: tiap menit cek `scheduled_reports` yang cron-nya match → generate + kirim.

#### [NEW] Views: `resources/views/admin/scheduled-reports/`
* `index.blade.php` — CRUD laporan terjadwal: nama, tipe, cron, penerima, metode kirim.

---

### 📊 Modul 9: Advanced Logging & Observability

**Tujuan:** Dashboard monitoring kesehatan sistem: CPU/RAM/disk usage, slow query detection, error rate, queue depth, dan active user count. Memastikan admin IT SMA ISA 1 bisa memantau performa platform secara proaktif.

---

#### [NEW] `app/Services/ObservabilityService.php`
```php
class ObservabilityService
{
    public function getSystemHealth(): array;                 // CPU, RAM, disk, DB connections
    public function getSlowQueries(int $thresholdMs = 500): Collection;
    public function getErrorRate(int $hours = 24): float;
    public function getResponseTimeP95(int $hours = 24): float;
    public function getActiveUserCount(): int;
    public function getQueueDepth(): int;
    public function getDiskUsage(): array;
    public function getDatabaseSize(): string;
}
```

#### [NEW] `app/Http/Middleware/PerformanceLogger.php`
* Log request > 1 detik sebagai slow request.
* Log memory usage > 128MB.
* Structured JSON log (timestamp, level, message, context, request_id, user_id, duration_ms).

#### [NEW] Views: `resources/views/admin/observability/`
* `dashboard.blade.php` — Gauge CPU/RAM/Disk, chart response time, error rate, queue depth, tabel slow queries.

#### Routes Baru
```php
Route::middleware('role:superadmin')->prefix('admin/observability')->name('admin.observability.')->group(function () {
    Route::get('/', [ObservabilityController::class, 'dashboard'])->name('dashboard');
    Route::get('slow-queries', [ObservabilityController::class, 'slowQueries'])->name('slow-queries');
    Route::get('errors', [ObservabilityController::class, 'errors'])->name('errors');
});
```

---

### 📖 Modul 10: Dokumentasi Teknis Lengkap (✅ SELESAI)

**Tujuan:** Dokumentasi teknis komprehensif agar developer baru (termasuk kamu sendiri di masa depan) bisa memahami dan melanjutkan pengembangan dengan cepat.

---

#### [NEW] `docs/` Directory Structure
```
docs/
├── README.md                            # Ringkasan proyek & quick start
├── ARCHITECTURE.md                      # Arsitektur sistem (diagram Mermaid)
│   ├── Service Pattern
│   ├── Database Schema Overview
│   ├── Event Broadcasting Flow
│   └── API Architecture
├── INSTALLATION.md                      # Panduan instalasi lengkap
│   ├── Persyaratan (PHP 8.3, Node 20, MySQL/SQLite)
│   ├── Setup Development
│   ├── Setup Production (VPS/Shared Hosting)
│   └── Setup dengan Docker
├── DEPLOYMENT.md                        # Panduan deployment
│   ├── VPS (DigitalOcean/Contabo)
│   ├── Shared Hosting (cPanel)
│   ├── Docker Compose Production
│   └── CI/CD via GitHub Actions
├── API.md                               # Panduan REST API v1
│   ├── Authentication (Sanctum Token)
│   ├── Endpoint Reference
│   └── Error Handling
├── TESTING.md                           # Panduan testing
│   ├── Running Tests
│   ├── Writing New Tests
│   └── Test Coverage
├── SECURITY.md                          # Security policy
├── CHANGELOG.md                         # Riwayat perubahan per versi
├── CONTRIBUTING.md                      # Panduan kontribusi
└── modules/                             # Dokumentasi per modul
    ├── academic.md                      # Absensi, Nilai, E-Rapor, Jadwal
    ├── finance.md                       # SPP, Payment Gateway, Accounting
    ├── hrd.md                           # Kepegawaian, Payroll, Cuti
    ├── elearning.md                     # E-Learning, CBT
    ├── communication.md                 # WhatsApp, Forum, Chat
    └── ...
```

#### [MODIFY] Scribe API Docs
* Update grouping endpoint sesuai API v1 terbaru.
* Tambah example responses.
* Konfigurasi interactive "Try It" playground.

---

## 🧪 Rencana Verifikasi Menyeluruh

### Pengujian Otomatis (Pest PHP)

| Modul | File Test | Skenario Utama |
| :--- | :--- | :--- |
| AI Chatbot | `ChatbotTest.php` | FAQ search, navigate intent, data query, conversation logging, feedback |
| Integrasi | `IntegrationTest.php` | Dapodik export format, EMIS export, webhook dispatch, signature verification |
| Payment | `PaymentGatewayTest.php` | Transaction creation, webhook callback, reconciliation, auto-expire, VA/QRIS |
| i18n | `LocalizationTest.php` | Locale switching, translation keys exist, RTL rendering, dark mode |
| Gamifikasi | `GamificationTest.php` | XP award, achievement auto-check, streak, leaderboard accuracy, tahfidz/mutabaah triggers |
| Branding | `AppearanceTest.php` | CSS variable generation, settings CRUD, logo upload, reset default |
| Import/Export | `ImportExportTest.php` | File parsing, column mapping, validation, import execution, rollback |
| Email Digest | `DigestTest.php` | Digest generation per role, cron matching, email rendering, PDF attachment |
| Observability | `ObservabilityTest.php` | Health check, slow query detection, performance logging |
| Documentation | Manual | Semua docs akurat, API playground works, semua link valid |

---

## 📋 Urutan Prioritas Implementasi

| Prioritas | Modul | Estimasi | Dependensi | Status |
| :---: | :--- | :--- | :--- | :---: |
| 🔴 **P0** | Modul 3: Payment Gateway Midtrans | 3-4 hari | Midtrans account | ✅ **SELESAI** |
| 🔴 **P0** | Modul 10: Dokumentasi Lengkap | 2-3 hari | Tidak ada | ✅ **SELESAI** |
| 🟠 **P1** | Modul 7: Import/Export Hub | 3-4 hari | Tidak ada | ✅ **SELESAI** |
| 🟠 **P1** | Modul 4: i18n & Aksesibilitas | 4-5 hari | Semua view template | ✅ **SELESAI** |
| 🟡 **P2** | Modul 1: AI Chatbot | 3-4 hari | Tidak ada | ✅ **SELESAI** |
| 🟡 **P2** | Modul 5: Gamifikasi | 3-4 hari | Tidak ada | ✅ **SELESAI** |
| 🟢 **P3** | Modul 8: Scheduled Reporting | 2-3 hari | Tidak ada | ✅ **SELESAI** |
| 🟢 **P3** | Modul 2: Integrasi Dapodik | 3-4 hari | Tidak ada | ✅ **SELESAI** |
| 🔵 **P4** | Modul 6: Branding Sekolah | 2-3 hari | Tidak ada | ✅ **SELESAI** |
| 🔵 **P4** | Modul 9: Observability | 2-3 hari | Tidak ada | ⏳ *Belum Mulai* |

**Total Estimasi:** 28-37 hari kerja (6-8 minggu)

---

## 🛠️ Dependencies Baru

### Composer
```json
{
    "midtrans/midtrans-php": "^2.x"
}
```

### Infrastructure
* **Midtrans Account** — Server key & client key untuk payment gateway produksi.
* **SMTP Server** — Untuk email digest (Mailtrap dev, Mailgun/Amazon SES produksi).

---

> [!IMPORTANT]
> Dengan selesainya **Fase 9 & 10**, Sistem Terpadu SMA Islam Sultan Agung 1 Semarang akan memiliki **~115 model, 80+ controller, 90+ migration, dan 170+ fitur** — menjadikannya platform sistem informasi sekolah paling lengkap dan canggih yang pernah dibangun khusus untuk satu sekolah. Semua fitur dirancang spesifik untuk kebutuhan SMA Islam Sultan Agung 1 Semarang, termasuk integrasi keislaman (tahfidz, mutaba'ah, gamifikasi islami).

> [!TIP]
> **Setelah Fase 10, platform sudah FEATURE-COMPLETE.** Langkah selanjutnya bukan menambah fitur baru, melainkan:
> 1. **🧪 Hardening & Load Testing** — Pastikan platform handle seluruh civitas SMA ISA 1 (800+ user concurrent).
> 2. **👥 User Acceptance Testing (UAT)** — Libatkan guru, orang tua, dan siswa nyata untuk feedback.
> 3. **🚀 Go-Live Deployment** — Deploy ke server produksi sekolah + pelatihan pengguna.
> 4. **📱 Mobile App** — Bangun aplikasi mobile (Flutter/React Native) yang konsumsi REST API v1 yang sudah tersedia.
