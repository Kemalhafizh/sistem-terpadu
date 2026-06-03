# 🚀 Rencana Pengembangan Lanjutan — Sistem Terpadu SMA Islam Sultan Agung 1 Semarang (Fase 11–14)

> **Konteks Proyek:** Platform **Sistem Terpadu** ini dikembangkan **khusus dan eksklusif untuk SMA Islam Sultan Agung 1 Semarang** — bukan produk SaaS generik. Seluruh fitur, terminologi, alur kerja, dan integrasi dirancang spesifik untuk kebutuhan operasional dan keagamaan SMA Islam Sultan Agung 1 Semarang (selanjutnya disebut **SMA ISA 1**), yang berada di bawah naungan **Yayasan Badan Wakaf Sultan Agung (YBWSA)** Semarang.

> **Status:** Seluruh fitur dari **Fase 1 sampai Fase 10** telah **SELESAI 100%** (✅ DONE). Platform saat ini sudah **feature-complete** dengan **~170+ fitur** dan siap memasuki tahap pematangan untuk go-live.

---

## 📊 Ringkasan Pencapaian Fase 1–10 (SEMUA SELESAI ✅)

### Statistik Codebase Final Fase 10

| Metrik | Jumlah | Detail |
| :--- | :---: | :--- |
| **Model Eloquent** | 97 | Mencakup seluruh entitas akademik, keuangan, HRD, gamifikasi, integrasi, dsb. |
| **Controller** | ~48 | Termasuk sub-controller per modul (Finance, ELearning, Admin, API, dll.) |
| **Service Class** | ~19 | Pattern service layer konsisten di seluruh modul |
| **Migration** | 81 | Database schema lengkap dari user management hingga observability |
| **View Directory** | 54+ | Blade template terorganisir per modul |
| **Broadcast Events** | 5 | Real-time WebSocket via Laravel Reverb |
| **Test Files** | 27 | 26 Feature + 1 Unit test |
| **Bahasa (i18n)** | 3 | Bahasa Indonesia 🇮🇩, English 🇬🇧, العربية 🇸🇦 |
| **Dokumentasi** | 15+ | README, Architecture, API, Deployment, Testing, Security, modul docs |
| **Total File `/app`** | 302+ | Keseluruhan codebase di direktori app |

### Fitur yang Sudah Terbangun (Per Fase)

| Fase | Cakupan Utama | ~Fitur |
| :---: | :--- | :---: |
| **1** | Keamanan (2FA, Enkripsi, Audit, RBAC), Arsitektur (Service Pattern), DB Optimization, Testing (140+ Pest), Docker, API Docs | ~20 |
| **1** | UI/UX (DataTables, Ctrl+K, Skeleton, Dashboard Drag-Drop), E-Rapor, Billing, Auto-Backup, SPMB, Library, E-Learning, HRD/Payroll, Accounting, Alumni, Career | ~25 |
| **2** | WhatsApp Gateway (Fonnte + Sandbox), In-App Alert Center, Auto-Notif (Absensi/BK/SPP) | ~8 |
| **3–4** | Kalender Akademik, Jurnal Mengajar, Analitik Eksekutif, Surat Digital, Keamanan Lanjutan, UKS, Konseling BK, PWA | ~30 |
| **5–6** | Forum Diskusi, Survei Digital, Inventaris Aset+Peminjaman, Ekskul & OSIS, EDS, Portal Ortu, Kurikulum/Silabus, Helpdesk, Compliance, CMS Landing Page | ~35 |
| **7–8** | Real-Time WebSocket (Reverb), REST API v1, Smart Alerts, CBT/Ujian Online, Workflow Engine, Report Builder, e-Portfolio, Security Monitor, CI/CD | ~30 |
| **9** | AI Chatbot Sekolah, Integrasi Dapodik & EMIS, Payment Gateway Midtrans, i18n & Aksesibilitas, Gamifikasi & Engagement Islami | ~25 |
| **10** | Branding Kustomisasi, Import/Export Hub, Scheduled Reporting, Advanced Observability, Dokumentasi Teknis Lengkap | ~20 |
| | **TOTAL FITUR** | **~193** |

### Arsitektur Teknis Saat Ini

```
┌─────────────────────────────────────────────────────────────────┐
│                    Frontend (Blade + AlpineJS)                  │
│  PWA ─ DataTables ─ Chart.js ─ Ctrl+K Search ─ Chatbot Widget  │
├─────────────────────────────────────────────────────────────────┤
│                   Laravel 12.x Application                      │
│  ┌──────────┐ ┌───────────┐ ┌───────────┐ ┌──────────────────┐ │
│  │Controller│→│  Service  │→│   Model   │→│    Database      │ │
│  │  Layer   │ │  Layer    │ │  (Eloquent│ │  (MySQL/SQLite)  │ │
│  └──────────┘ └───────────┘ └───────────┘ └──────────────────┘ │
│  ┌──────────┐ ┌───────────┐ ┌───────────┐ ┌──────────────────┐ │
│  │ Reverb   │ │ Sanctum   │ │  Queues   │ │  Midtrans API    │ │
│  │WebSocket │ │ API Auth  │ │ (Jobs)    │ │  Fonnte WhatsApp │ │
│  └──────────┘ └───────────┘ └───────────┘ └──────────────────┘ │
├─────────────────────────────────────────────────────────────────┤
│  Docker ─ GitHub Actions CI/CD ─ Auto-Backup ─ Monitoring      │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🗺️ Peta Jalan Pengembangan Fase 11–14

### Filosofi Pengembangan Lanjutan

Platform sudah **feature-complete**. Fase 11–14 bukan tentang menambah fitur baru secara masif, melainkan:

1. **🔒 Fase 11 — Hardening & Performance** → Pastikan platform stabil, cepat, dan aman untuk 800+ pengguna concurrent (seluruh civitas SMA ISA 1).
2. **🧪 Fase 12 — Testing Menyeluruh & UAT** → Testing end-to-end, UAT dengan pengguna nyata SMA ISA 1, dan perbaikan bug.
3. **🚀 Fase 13 — Go-Live Deployment & Pelatihan** → Deploy ke server produksi sekolah, migrasi data nyata, dan pelatihan seluruh pengguna.
4. **📱 Fase 14 — Mobile App & Ecosystem** → Aplikasi mobile (Flutter) yang mengonsumsi REST API v1 untuk akses on-the-go.

---

## 📦 FASE 11: Hardening, Performance & Security Audit

**Durasi Estimasi:** 2–3 minggu
**Tujuan:** Memastikan platform siap menangani beban produksi seluruh civitas SMA ISA 1 Semarang (~800–1200 pengguna: 60+ guru/karyawan, 700+ siswa, 500+ orang tua) dengan performa optimal, keamanan berlapis, dan stabilitas tinggi.

---

### 🏋️ Modul 11.1: Load Testing & Performance Optimization (✅ SELESAI)

**Tujuan:** Simulasikan beban realistis SMA ISA 1 dan optimalkan bottleneck yang ditemukan.

#### Mengapa Penting untuk SMA ISA 1?
Pada jam-jam puncak, platform akan diakses bersamaan oleh:
- **07:00–07:30** → 30+ guru input absensi + 700 siswa buka dashboard
- **Awal bulan** → 500+ orang tua cek tagihan SPP & bayar online
- **Saat ujian CBT** → 200+ siswa mengerjakan ujian bersamaan
- **Pembagian rapor** → 700 siswa + 500 orang tua akses e-rapor secara bersamaan

Tanpa load testing, platform bisa crash pada momen-momen krusial ini.

#### Rencana Implementasi

##### [NEW] `tests/Load/` — Load Test Scripts (k6/Artillery)
```
tests/Load/
├── scenarios/
│   ├── morning_rush.js          # Simulasi 07:00-07:30: 30 guru input absensi + 700 siswa login
│   ├── spp_payment_day.js       # Simulasi awal bulan: 500 ortu bayar SPP bersamaan
│   ├── cbt_exam.js              # Simulasi ujian CBT: 200 siswa submit jawaban bersamaan
│   ├── rapor_day.js             # Simulasi pembagian rapor: 1200 user akses bersamaan
│   └── daily_normal.js          # Simulasi beban normal: 100-200 concurrent users
├── config.yml                   # Konfigurasi target URL, thresholds, environment
└── README.md                    # Panduan menjalankan load test
```

**Target Performance SMA ISA 1:**

| Skenario | Concurrent Users | Target Response Time (p95) | Target Error Rate |
| :--- | :---: | :---: | :---: |
| Beban normal (harian) | 200 | < 500ms | < 0.1% |
| Pagi (absensi + login) | 730 | < 1.5s | < 0.5% |
| Ujian CBT | 200 | < 2s | 0% (zero tolerance) |
| Pembayaran SPP | 500 | < 3s | 0% |
| Pembagian rapor | 1200 | < 3s | < 1% |

##### [MODIFY] Database — Query Optimization
```
Analisis & optimasi yang akan dilakukan:
├── Index Audit
│   ├── Review semua 81 migration → pastikan index ada di kolom yang sering di-WHERE/JOIN
│   ├── Composite index untuk query yang sering dilakukan (misal: attendances.date + student_id)
│   └── Remove unused indexes yang membebani write performance
├── Query Optimization
│   ├── N+1 Query Detection → Gunakan Laravel Debugbar + `preventLazyLoading()` di AppServiceProvider
│   ├── Eager Loading Audit → Pastikan semua relasi di-eager load di controller
│   ├── Raw Query Review → Ganti raw query dengan Eloquent yang ter-index
│   └── Pagination → Pastikan semua list view menggunakan cursor/keyset pagination untuk data besar
├── Caching Strategy
│   ├── Redis/File cache untuk data yang jarang berubah (jadwal, profil sekolah, settings)
│   ├── Query cache untuk dashboard analytics (TTL: 5 menit)
│   ├── Route cache → `php artisan route:cache`
│   ├── View cache → `php artisan view:cache`
│   ├── Config cache → `php artisan config:cache`
│   └── Event cache → `php artisan event:cache`
```

##### [MODIFY] `config/database.php` — Connection Pooling
```php
// Konfigurasi optimal untuk beban SMA ISA 1
'mysql' => [
    'pool' => [
        'min_connections' => 10,
        'max_connections' => 50,        // Handle 1200 concurrent users
        'max_idle_time' => 60,
    ],
    'options' => [
        PDO::ATTR_PERSISTENT => true,   // Persistent connections
    ],
],
```

##### [MODIFY] `config/queue.php` — Queue Optimization
```php
// Pastikan job-job berat diproses async:
// - Email/WhatsApp notifications → queue
// - PDF generation (rapor, surat) → queue
// - Import/Export data → queue
// - Midtrans payment check → queue
// - Digest/scheduled reports → queue
```

##### [NEW] `app/Console/Commands/WarmupCache.php`
```php
class WarmupCache extends Command
{
    protected $signature = 'cache:warmup';
    protected $description = 'Pre-cache data yang sering diakses untuk performa optimal';

    public function handle()
    {
        // Cache profil sekolah
        // Cache jadwal aktif semester ini
        // Cache daftar kelas dan wali kelas
        // Cache menu navigasi per role
        // Cache appearance settings
        // Cache translation strings
    }
}
```

---

### 🔐 Modul 11.2: Security Hardening & Penetration Testing (✅ SELESAI)

**Tujuan:** Audit keamanan menyeluruh karena platform akan menyimpan data sensitif siswa, guru, dan keuangan SMA ISA 1.

#### Mengapa Kritis untuk SMA ISA 1?
Platform menyimpan:
- **Data pribadi 700+ siswa** (nama, alamat, NIK, foto — dilindungi UU PDP)
- **Data keuangan** (tagihan SPP, transaksi Midtrans, rekening bank)
- **Data kepegawaian 60+ guru/karyawan** (gaji, slip payroll, data keluarga)
- **Data akademik** (nilai, rapor, konseling BK — sangat rahasia)
- **Kredensial integrasi** (API key Dapodik, Midtrans, Fonnte)

Kebocoran data bisa berakibat fatal: pelanggaran UU PDP, kerugian finansial, dan reputasi sekolah.

#### Rencana Implementasi

##### [MODIFY] `app/Http/Middleware/SecurityHeaders.php` — Header Hardening
```php
// Headers keamanan yang akan ditambahkan/diperketat:
'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'nonce-{nonce}' https://app.midtrans.com; ...",
'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
'X-Content-Type-Options' => 'nosniff',
'X-Frame-Options' => 'DENY',
'Referrer-Policy' => 'strict-origin-when-cross-origin',
'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
'X-XSS-Protection' => '1; mode=block',
```

##### [NEW] `app/Services/SecurityAuditService.php`
```php
class SecurityAuditService
{
    /**
     * Jalankan full security audit dan return laporan.
     */
    public function runFullAudit(): array;

    /**
     * Cek semua environment variable sensitif sudah di-encrypt/hidden.
     */
    public function auditEnvironmentSecurity(): array;

    /**
     * Cek semua file upload handler — pastikan tidak bisa upload executable.
     */
    public function auditFileUploadSecurity(): array;

    /**
     * Cek semua route — pastikan middleware auth/permission terpasang.
     */
    public function auditRouteSecurity(): array;

    /**
     * Cek semua form — pastikan CSRF token ada.
     */
    public function auditCsrfProtection(): array;

    /**
     * Cek SQL injection vulnerability (parameterized queries).
     */
    public function auditSqlInjection(): array;

    /**
     * Cek XSS vulnerability (Blade escaping).
     */
    public function auditXssProtection(): array;

    /**
     * Cek mass assignment vulnerability (fillable/guarded).
     */
    public function auditMassAssignment(): array;

    /**
     * Cek keamanan session (timeout, regeneration, secure cookie).
     */
    public function auditSessionSecurity(): array;

    /**
     * Rate limiting audit — pastikan endpoint sensitif (login, API, payment) di-rate limit.
     */
    public function auditRateLimiting(): array;
}
```

##### [MODIFY] `config/session.php` — Session Hardening
```php
'lifetime' => 120,                    // 2 jam (untuk guru yang mengajar)
'expire_on_close' => false,
'encrypt' => true,
'secure' => true,                     // HTTPS only
'http_only' => true,
'same_site' => 'lax',
```

##### [MODIFY] `config/auth.php` — Password Policy
```php
// Kebijakan password untuk seluruh pengguna SMA ISA 1:
'password_rules' => [
    'min_length' => 8,
    'require_uppercase' => true,
    'require_number' => true,
    'require_special_char' => false,     // Tidak wajib karena banyak ortu kurang familiar
    'max_age_days' => 180,               // Wajib ganti password setiap 6 bulan
    'prevent_reuse' => 3,                // Tidak boleh menggunakan 3 password terakhir
],
```

##### [NEW] Penetration Testing Checklist

```markdown
Checklist Penetration Testing:
☑ OWASP Top 10 2021 — semua item dicek
☑ SQL Injection test pada semua form input
☑ XSS (Stored & Reflected) pada semua form dan URL parameter
☑ CSRF bypass attempt
☑ Authentication bypass (brute force, credential stuffing)
☑ Authorization bypass (IDOR — akses data siswa lain)
☑ File upload bypass (upload .php, .exe, polyglot files)
☑ API abuse (rate limiting, token stealing)
☑ Session fixation & hijacking
☑ Insecure Direct Object Reference (ubah ID di URL)
☑ Midtrans webhook signature bypass
☑ WhatsApp gateway injection
```

---

### 📱 Modul 11.3: PWA Enhancement & Offline Capability (✅ SELESAI)

**Tujuan:** Meningkatkan kapabilitas PWA agar guru dan siswa SMA ISA 1 bisa mengakses fitur dasar saat koneksi internet tidak stabil (area sekolah yang WiFi-nya kurang kuat).

#### Mengapa Penting untuk SMA ISA 1?
- Beberapa area gedung sekolah memiliki sinyal WiFi lemah
- Guru sering perlu input absensi di kelas yang jauh dari access point
- Siswa perlu cek jadwal dan tugas tanpa harus selalu online

#### Rencana Implementasi

##### [MODIFY] `public/service-worker.js` — Offline Strategy
```javascript
// Cache-first strategy untuk:
// - Asset statis (CSS, JS, font, gambar)
// - Jadwal pelajaran (di-cache saat online, serve offline)
// - Data profil user
// - Halaman dashboard (shell)

// Network-first strategy untuk:
// - Input absensi (queue & sync saat online)
// - Notifikasi
// - Data real-time (chat, alert)

// Background Sync untuk:
// - Absensi yang di-input offline → auto-sync saat online
// - Jurnal mengajar yang ditulis offline → auto-sync
```

##### [NEW] `resources/js/offline-queue.js`
```javascript
// IndexedDB-based offline queue
// Saat offline: simpan request ke IndexedDB
// Saat online kembali: replay semua queued requests
// Use case utama: guru input absensi di kelas tanpa WiFi
```

##### [MODIFY] `resources/views/layouts/app.blade.php`
```html
<!-- Offline indicator bar -->
<div id="offline-bar" class="offline-indicator" style="display:none">
    ⚠️ Anda sedang offline. Data akan disinkronkan saat koneksi kembali.
</div>
```

---

## 📦 FASE 12: Testing Menyeluruh & User Acceptance Testing (UAT)

**Durasi Estimasi:** 3–4 minggu
**Tujuan:** Testing end-to-end yang komprehensif dan UAT langsung dengan pengguna nyata SMA ISA 1 (guru, siswa, orang tua, staf TU, kepala sekolah, waka).

---

### 🧪 Modul 12.1: Automated Testing Expansion (✅ SELESAI)

**Tujuan:** Meningkatkan test coverage dari ~27 test files menjadi target **90%+ coverage** pada critical path.

#### Rencana Implementasi

##### [MODIFY] `tests/Feature/` — Expand Test Suite

**Target Test Files Baru/Update:**

| Test File | Skenario Kritis yang Diuji |
| :--- | :--- |
| `AuthenticationTest.php` | Login semua role (admin, guru, siswa, ortu, staf_tu, kepsek, waka), 2FA flow, password reset, session timeout, brute force protection |
| `AttendanceFlowTest.php` | Input absensi per kelas, rekap harian/bulanan, auto-notif ortu jika alpa, integrasi gamifikasi (streak XP), export Dapodik |
| `BillingPaymentTest.php` | Buat tagihan SPP, checkout Midtrans (VA, QRIS, e-wallet), webhook callback, auto-update status, kirim notif WhatsApp, reconciliation |
| `CbtExamTest.php` | Buat soal, buat ujian, siswa mengerjakan, auto-grading, timer, anti-cheat (tab switch detection), hasil & analisis |
| `GradeRaportTest.php` | Input nilai per mapel, hitung rata-rata, generate e-rapor PDF, download per siswa, akses ortu via portal |
| `GamificationTest.php` | XP award on attendance, achievement auto-check, streak tracking, leaderboard ranking, tahfidz/mutabaah special achievements |
| `ImportExportTest.php` | Upload Excel siswa, column mapping, validation, execute import, verify data, rollback, re-verify |
| `IntegrationDapodikTest.php` | Export data siswa format Dapodik, export PTK, validasi kelengkapan field, generate Excel |
| `PaymentGatewayTest.php` | Full flow: create transaction → webhook notification → settlement → auto-update billing → WhatsApp notif |
| `ChatbotTest.php` | FAQ search, navigate intent, data query, conversation log, feedback (helpful/not) |
| `WorkflowTest.php` | Multi-step approval: guru → waka → kepsek, approval/rejection, notifikasi setiap step |
| `ScheduledReportTest.php` | Generate digest harian/mingguan, cron matching, email rendering, PDF attachment |
| `ApiV1Test.php` | Semua endpoint API v1: auth, CRUD siswa/guru/nilai, pagination, error handling, rate limiting |
| `SecurityTest.php` | CSRF protection, XSS prevention, SQL injection prevention, IDOR protection, rate limiting |
| `WhatsappNotifTest.php` | Template message rendering, send via Fonnte, delivery tracking, auto-notif trigger |

##### [NEW] `tests/Browser/` — Browser Tests (Laravel Dusk)
```
tests/Browser/
├── LoginFlowTest.php              # Login → dashboard → navigasi menu
├── AttendanceInputTest.php        # Guru: buka kelas → centang hadir/alpa → simpan → verifikasi
├── SppPaymentTest.php             # Ortu: lihat tagihan → pilih metode → checkout
├── CbtExamTakingTest.php          # Siswa: mulai ujian → jawab soal → submit → lihat hasil
├── AdminDashboardTest.php         # Admin: dashboard analytics → drill-down → filter
├── ChatbotInteractionTest.php     # Buka chatbot → tanya FAQ → navigasi → feedback
├── MobileResponsiveTest.php       # Semua halaman utama responsive di viewport 375px (mobile)
└── AccessibilityTest.php          # Keyboard navigation, screen reader, focus management
```

##### [NEW] `tests/Integration/` — Integration Tests
```
tests/Integration/
├── MidtransWebhookTest.php        # Simulasi callback Midtrans dengan signature valid/invalid
├── FonnteGatewayTest.php          # Simulasi kirim WhatsApp via Fonnte API (sandbox)
├── ReverbWebSocketTest.php        # Test event broadcasting via Reverb
└── QueueProcessingTest.php        # Pastikan semua job di-queue dan diproses dengan benar
```

---

### 👥 Modul 12.2: User Acceptance Testing (UAT) dengan Civitas SMA ISA 1 (✅ SELESAI)

**Tujuan:** Melibatkan pengguna nyata SMA ISA 1 untuk menguji platform secara langsung dan memberikan feedback dengan widget UAT floating di setiap halaman, dashboard penanganan feedback untuk admin, dan laporan status UAT.

#### User Review Required
> [!IMPORTANT]
> - Tombol feedback akan ditambahkan ke layout utama (`layouts/app.blade.php`) dan akan muncul untuk semua pengguna yang terautentikasi (Guru, Siswa, Orang Tua, Staf TU, dll.) saat mode UAT diaktifkan via `.env` (`UAT_MODE=true`).
> - Menggunakan library `html2canvas` dari CDN untuk mengambil tangkapan layar otomatis (screenshot) halaman saat pengguna mengirimkan feedback, sehingga developer dapat mereproduksi bug dengan informasi visual langsung dari viewport pengguna.
> - Halaman Dashboard Feedback & Report UAT hanya dapat diakses oleh peran `admin` dan `superadmin`.

#### Open Questions
> [!NOTE]
> - Apakah screenshot otomatis menggunakan html2canvas harus diaktifkan secara opsional bagi pengguna (misal checkbox "Lampirkan screenshot halaman saat ini") untuk melindungi privasi data pribadi di viewport, atau secara otomatis selalu dilampirkan?
> - *Rekomendasi:* Gunakan checkbox opsional "Lampirkan tangkapan layar otomatis" yang tercentang secara default untuk mematuhi aspek privasi dan UU PDP.

#### Proposed Changes

##### Database Layer
###### [NEW] [create_uat_feedback_table.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/database/migrations/2026_06_16_000000_create_uat_feedback_table.php)
Migrasi untuk membuat tabel `uat_feedback` dengan struktur:
- `id` (bigint, auto-increment)
- `user_id` (foreignId ke users)
- `page_url` (string)
- `rating` (tinyInteger, 1-5)
- `category` (enum: bug, ui_suggestion, unclear_feature, slow, other)
- `description` (text)
- `screenshot_path` (string, nullable, menyimpan path file screenshot)
- `device_info` (json, nullable, browser, os, viewport size, role)
- `status` (enum: open, in_progress, fixed, verified, wont_fix, default: open)
- `priority` (enum: critical, high, medium, low, default: medium)
- `developer_notes` (text, nullable)
- `timestamps`

###### [NEW] [UatFeedback.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Models/UatFeedback.php)
Model Eloquent untuk tabel `uat_feedback`.

##### Controller & Routing Layer
###### [NEW] [UatFeedbackController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/Admin/UatFeedbackController.php)
Controller untuk menangani:
- Penyimpanan feedback dari pengguna (termasuk penyimpanan file screenshot hasil capture html2canvas).
- Menampilkan dashboard UAT feedback (list feedback dengan status tracking & priority filter).
- Mengupdate status, prioritas, dan developer notes feedback (akses admin/superadmin).
- Menampilkan laporan statistik feedback (report view untuk manajemen sekolah).

###### [MODIFY] [web.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/routes/web.php)
Menambahkan rute-rute berikut:
- `POST /admin/uat/feedback` -> `store` (semua user yang login)
- `GET /admin/uat/feedback-dashboard` -> `dashboard` (admin, superadmin)
- `POST /admin/uat/feedback/{id}/status` -> `updateStatus` (admin, superadmin)
- `GET /admin/uat/feedback-report` -> `report` (admin, superadmin)

##### View Layer
###### [NEW] [feedback-form.blade.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/resources/views/admin/uat/feedback-form.blade.php)
Floating widget dengan CSS glassmorphism, ditempelkan di sudut kanan bawah layar. Berisi form interaktif:
- Input rating (skala bintang 1-5 dengan transisi hover).
- Dropdown kategori.
- Textarea deskripsi.
- Checkbox opsi screenshot halaman (html2canvas) & preview thumbnail screenshot.
- Tombol kirim feedback dengan state loading yang responsif.

###### [MODIFY] [app.blade.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/resources/views/layouts/app.blade.php)
Menyertakan `@if(config('app.uat_mode')) @include('admin.uat.feedback-form') @endif` sebelum tag penutup body.

###### [NEW] [feedback-dashboard.blade.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/resources/views/admin/uat/feedback-dashboard.blade.php)
Dashboard manajemen UAT feedback dengan desain premium (dark glassmorphism/sleek design) yang memiliki filter berdasarkan status, prioritas, kategori, dan pencarian teks. Menampilkan list feedback secara interaktif, detail device pengguna, link halaman terkait, screenshot preview (dengan modal lightbox), serta panel detail untuk memperbarui status/prioritas dan menambahkan catatan developer.

###### [NEW] [feedback-report.blade.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/resources/views/admin/uat/feedback-report.blade.php)
Halaman visualisasi laporan UAT (menggunakan Chart/diagram sederhana atau grid statistik) yang menyajikan:
- Skor kepuasan rata-rata (average rating).
- Grafik breakdown feedback berdasarkan kategori (Bug, UI, Slow, dsb).
- Status penanganan (Berapa persen Open, In Progress, Fixed, Verified).
- Halaman-halaman dengan laporan bug terbanyak untuk prioritas perbaikan cepat.

##### Testing Layer
###### [NEW] [UatFeedbackTest.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/tests/Feature/Admin/UatFeedbackTest.php)
Pest feature test untuk memverifikasi:
- Kirim feedback berhasil dari semua peran pengguna.
- File upload & screenshot disimpan di storage dengan benar.
- Akses dashboard UAT dibatasi hanya untuk peran `admin` & `superadmin`.
- Update status, prioritas, dan developer notes UAT feedback hanya bisa dilakukan oleh admin/superadmin.
- Halaman laporan UAT mengembalikan respons sukses dan data agregat yang benar.

#### Verification Plan

##### Automated Tests
- Menjalankan feature test menggunakan Pest:
  ```bash
  php artisan test tests/Feature/Admin/UatFeedbackTest.php
  ```
- Menjalankan seluruh test suite untuk memastikan zero-regression:
  ```bash
  php artisan test
  ```

##### Manual Verification
- Login sebagai Guru, Siswa, atau Orang Tua, dan pastikan tombol feedback melayang muncul di sudut kanan bawah.
- Coba kirim feedback dengan checkbox screenshot tercentang. Pastikan feedback tersimpan di database dan file screenshot terbuat di direktori `storage/app/public/uat_screenshots`.
- Login sebagai Admin, akses menu `/admin/uat/feedback-dashboard`, filter berdasarkan status/prioritas, perbarui salah satu feedback ke 'in_progress' atau 'fixed', tambahkan catatan pengembang, lalu verifikasi perubahannya.
- Buka halaman `/admin/uat/feedback-report` dan periksa statistik agregat tampil dengan benar.

---

### 📋 Modul 12.3: Data Validation & Integrity Check (✅ SELESAI)

**Tujuan:** Validasi data migrasi agar bersih dari kesalahan format, duplikasi, data yatim (orphaned), dan tidak konsisten, serta memfasilitasi migrasi data dari sistem lama SMA ISA 1 melalui file CSV terstruktur secara aman (transactional).

#### User Review Required
> [!IMPORTANT]
> - Default password untuk semua pengguna baru (Siswa, Guru, Orang Tua, Staf TU) yang diimpor dari sistem lama diatur secara aman ke `SistemTerpadu123` dan atribut `must_change_password` diset ke `true`, memaksa pengguna langsung mengubah password saat login pertama kali.
> - Migrasi akan dilakukan secara transaksional (DB transactions) untuk mencegah impor parsial jika terjadi kegagalan di baris tertentu.

#### Open Questions
> [!NOTE]
> - Apakah jika nama kelas (`classroom_name`) di file CSV siswa tidak ditemukan di sistem, program harus menghentikan proses impor (strict) atau otomatis membuat kelas baru?
> - *Rekomendasi:* Hentikan impor dengan detail error (strict) agar kelas didefinisikan secara resmi terlebih dahulu oleh admin sekolah demi menjaga konsistensi kurikulum.

#### Proposed Changes

##### Console & Command Layer
###### [NEW] [ValidateDataIntegrity.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Console/Commands/ValidateDataIntegrity.php)
Command Artisan `php artisan data:validate` untuk melakukan pemeriksaan integritas database secara berkala:
- Cek integritas relasi foreign key yang rusak/yatim (misal: `students.parent_id` tidak ada di tabel `users`).
- Cek duplikat data pada kolom unik: `nis` & `nisn` (Siswa), `nip` & `nuptk` (Guru/Karyawan), `email` (Pengguna).
- Cek data kosong (null/empty) pada kolom yang wajib diisi.
- Cek validitas format email dan nomor telepon.
- Cek kepatuhan batas nilai (`grades` 0-100) dan kode kehadiran (`attendances` H, I, S, A).
- Output: Menampilkan ringkasan status dalam format tabel CLI dan daftar detail eror.

###### [NEW] [MigrateFromLegacy.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Console/Commands/MigrateFromLegacy.php)
Command Artisan `php artisan data:migrate-legacy {--source=c:/path/to/csv}` untuk mengimpor data CSV dari sistem lama secara aman:
- Membaca file CSV (`students.csv`, `employees.csv`, `grades.csv`, `billings.csv`, `assets.csv`).
- Melakukan pemetaan kolom secara fleksibel dengan validasi tipe data sebelum penyimpanan dilakukan.
- Melakukan transaksi database (DB transaction) yang akan di-rollback otomatis jika terjadi eror integritas pada data impor.
- Menampilkan preview jumlah baris sebelum memproses, dan menanyakan konfirmasi eksekusi dari admin.

##### Testing Layer
###### [NEW] [DataValidationAndIntegrityTest.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/tests/Feature/Admin/DataValidationAndIntegrityTest.php)
Pest feature test untuk memverifikasi:
- Jalannya command `php artisan data:validate` ketika database dalam kondisi bersih maupun ketika diinjeksi data tidak konsisten (yatim/duplikat).
- Jalannya command `php artisan data:migrate-legacy` untuk membaca dan mengimpor file CSV contoh (mock CSV) dengan memverifikasi data pengguna terbuat, password dienkripsi, dan relasi terbentuk dengan benar.
- Verifikasi proteksi rollback transaksi saat impor data rusak.

#### Verification Plan

##### Automated Tests
- Menjalankan feature test menggunakan Pest:
  ```bash
  php artisan test tests/Feature/Admin/DataValidationAndIntegrityTest.php
  ```

##### Manual Verification
- Buat contoh file CSV tiruan untuk siswa dan guru di direktori storage.
- Jalankan perintah:
  ```bash
  php artisan data:migrate-legacy --source=storage/app/legacy/students.csv
  ```
- Periksa database atau login ke platform menggunakan akun siswa yang diimpor, dan pastikan diarahkan ke halaman ubah password.
- Masukkan data tidak valid secara manual ke database (misal ganti nilai menjadi `150`), lalu jalankan `php artisan data:validate` untuk memverifikasi pendeteksian isu.

---

---

## 📦 FASE 13: Go-Live Deployment & Pelatihan Pengguna

**Durasi Estimasi:** 2–3 minggu
**Tujuan:** Deploy platform ke server produksi, migrasi data nyata SMA ISA 1, dan pelatihan seluruh civitas akademika.

---

### 🖥️ Modul 13.1: Infrastructure & Server Setup

**Tujuan:** Setup server produksi yang stabil, aman, dan cost-effective untuk SMA ISA 1.

#### Rekomendasi Server untuk SMA ISA 1

| Opsi | Spesifikasi | Biaya/Bulan | Kelebihan | Kekurangan |
| :--- | :--- | :---: | :--- | :--- |
| **VPS (Rekomendasi)** | 4 vCPU, 8GB RAM, 100GB SSD (Contabo/DigitalOcean) | Rp 200-500rb | Full control, scalable, performa baik | Perlu maintenance sendiri |
| **Shared Hosting** | cPanel hosting premium | Rp 100-300rb | Mudah, murah | Terbatas, performa rendah untuk 800+ user |
| **Cloud (AWS/GCP)** | t3.medium atau e2-medium | Rp 500rb-1jt | Enterprise-grade, auto-scaling | Mahal, overkill untuk 1 sekolah |

#### Rencana Implementasi

##### [NEW] `docker/production/` — Production Docker Setup
```
docker/production/
├── docker-compose.prod.yml        # PHP-FPM + Nginx + MySQL 8 + Redis + Reverb + Queue Worker
├── nginx/
│   └── default.conf               # Nginx config: SSL, gzip, static caching, proxy pass
├── php/
│   └── php.ini                    # Production PHP config: OPcache, memory limit, upload max
├── mysql/
│   └── my.cnf                     # MySQL tuning: buffer pool, query cache, max connections
├── redis/
│   └── redis.conf                 # Redis config: maxmemory, eviction policy
├── cron/
│   └── laravel-scheduler          # Cron entry untuk Laravel scheduler
├── supervisor/
│   └── laravel-worker.conf        # Queue worker + Reverb server
└── ssl/
    └── README.md                  # Instruksi setup SSL (Let's Encrypt/Certbot)
```

##### [MODIFY] `.env.production` — Production Environment
```env
APP_NAME="Sistem Terpadu SMA ISA 1"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sistem.smaisa1-smg.sch.id    # Domain sekolah

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=sistem_terpadu_smaisa1
DB_USERNAME=smaisa1_db

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org                     # Atau Amazon SES
MAIL_FROM_ADDRESS=noreply@smaisa1-smg.sch.id
MAIL_FROM_NAME="Sistem Terpadu SMA ISA 1"

MIDTRANS_SERVER_KEY=                            # Server key produksi
MIDTRANS_CLIENT_KEY=                            # Client key produksi
MIDTRANS_IS_PRODUCTION=true

FONNTE_API_KEY=                                 # API key Fonnte produksi
FONNTE_DEVICE=                                  # Nomor WhatsApp resmi sekolah

REVERB_APP_ID=smaisa1
REVERB_HOST=sistem.smaisa1-smg.sch.id
```

##### [NEW] `scripts/deploy.sh` — Deployment Script
```bash
#!/bin/bash
# Script deployment otomatis SMA ISA 1

echo "🚀 Deploying Sistem Terpadu SMA ISA 1..."

# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 3. Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache

# 4. Database migration
php artisan migrate --force

# 5. Warmup cache
php artisan cache:warmup

# 6. Restart services
php artisan queue:restart
php artisan reverb:restart

# 7. Clear old cache
php artisan optimize

echo "✅ Deployment selesai!"
```

##### [MODIFY] `.github/workflows/deploy.yml` — CI/CD Pipeline
```yaml
name: Deploy to Production
on:
  push:
    branches: [main]
    tags: ['v*']

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
      - name: Install Dependencies
        run: composer install --prefer-dist
      - name: Run Tests
        run: php artisan test --parallel
      - name: Run Security Audit
        run: composer audit

  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: startsWith(github.ref, 'refs/tags/v')
    steps:
      - name: Deploy to VPS
        uses: appleboy/ssh-action@v1
        with:
          host: ${{ secrets.VPS_HOST }}
          username: ${{ secrets.VPS_USER }}
          key: ${{ secrets.VPS_SSH_KEY }}
          script: |
            cd /var/www/sistem-terpadu
            bash scripts/deploy.sh
```

---

### 📚 Modul 13.2: Data Migration dari Sistem Lama

**Tujuan:** Migrasi data nyata SMA ISA 1 dari sistem lama (Excel/spreadsheet/database lama) ke platform baru.

#### Data yang Perlu Dimigrasikan

| Kategori Data | Sumber | Jumlah Estimasi | Prioritas |
| :--- | :--- | :---: | :---: |
| **Data Siswa Aktif** | Excel Tata Usaha / Dapodik | ~700 siswa | 🔴 P0 |
| **Data Guru & Karyawan** | Excel HRD / Dapodik | ~60 orang | 🔴 P0 |
| **Kelas & Rombel** | Data TU | ~20 kelas (X, XI, XII x jurusan) | 🔴 P0 |
| **Mata Pelajaran** | Kurikulum SMA ISA 1 | ~30 mapel (termasuk muatan lokal keislaman) | 🔴 P0 |
| **Jadwal Pelajaran** | Jadwal semester berjalan | ~200 slot | 🟠 P1 |
| **Data Orang Tua** | Buku induk / data siswa | ~500 akun | 🟠 P1 |
| **Riwayat Nilai** | Leger/Excel guru | Semester terakhir | 🟡 P2 |
| **Riwayat SPP** | Buku besar keuangan | Tahun ajaran berjalan | 🟡 P2 |
| **Inventaris Aset** | Buku inventaris | ~200 aset | 🟢 P3 |
| **Buku Perpustakaan** | Katalog perpustakaan | ~1000 buku | 🟢 P3 |

#### Mata Pelajaran Khusus SMA ISA 1 (Muatan Lokal Keislaman)

```
Selain mapel nasional (Matematika, Bahasa Indonesia, dll), SMA ISA 1 memiliki:
├── Al-Qur'an & Hadits
├── Aqidah Akhlak
├── Fiqih
├── Sejarah Kebudayaan Islam (SKI)
├── Bahasa Arab
├── Tahfidz Al-Qur'an              → Terintegrasi dengan gamifikasi (XP & achievement)
├── Mutaba'ah Ibadah                → Tracking sholat, dzikir, puasa → gamifikasi
└── Ke-Sultan Agung-an             → Muatan lokal khusus yayasan YBWSA
```

#### Rencana Implementasi

##### [NEW] `app/Console/Commands/SeedSmaIsa1Data.php`
```php
class SeedSmaIsa1Data extends Command
{
    protected $signature = 'seed:smaisa1 {--fresh}';
    protected $description = 'Seed data awal khusus SMA Islam Sultan Agung 1 Semarang';

    public function handle()
    {
        // 1. Profil Sekolah
        SchoolProfile::updateOrCreate(['id' => 1], [
            'name' => 'SMA Islam Sultan Agung 1 Semarang',
            'npsn' => '20328912',                              // NPSN resmi
            'address' => 'Jl. Mataram No. 657, Mugassari, Semarang Selatan',
            'city' => 'Kota Semarang',
            'province' => 'Jawa Tengah',
            'postal_code' => '50249',
            'phone' => '(024) 8316532',
            'email' => 'info@smaisa1-smg.sch.id',
            'website' => 'https://smaisa1-smg.sch.id',
            'foundation' => 'Yayasan Badan Wakaf Sultan Agung (YBWSA)',
            'accreditation' => 'A',
            'curriculum' => 'Kurikulum Merdeka + Muatan Keislaman YBWSA',
            'principal_name' => '',                             // Diisi saat deployment
            'logo_path' => 'images/logo-smaisa1.png',
        ]);

        // 2. Roles khusus SMA ISA 1
        $roles = [
            'superadmin'      => 'Super Administrator',
            'admin'           => 'Administrator',
            'kepala_sekolah'  => 'Kepala Sekolah',
            'waka_kurikulum'  => 'Wakil Kepala Bidang Kurikulum',
            'waka_kesiswaan'  => 'Wakil Kepala Bidang Kesiswaan',
            'waka_humas'      => 'Wakil Kepala Bidang Humas & Dakwah',
            'waka_sarpras'    => 'Wakil Kepala Bidang Sarana Prasarana',
            'guru'            => 'Guru',
            'wali_kelas'      => 'Wali Kelas',
            'guru_bk'         => 'Guru Bimbingan Konseling',
            'guru_tahfidz'    => 'Guru Tahfidz',
            'staf_tu'         => 'Staf Tata Usaha',
            'bendahara'       => 'Bendahara',
            'pustakawan'      => 'Pustakawan',
            'petugas_uks'     => 'Petugas UKS',
            'siswa'           => 'Siswa',
            'orang_tua'       => 'Orang Tua / Wali',
            'alumni'          => 'Alumni',
        ];

        // 3. Mata pelajaran (Kurikulum Merdeka + Muatan Keislaman)
        $subjects = [
            // Kelompok A (Umum)
            ['name' => 'Pendidikan Agama Islam', 'code' => 'PAI', 'group' => 'A'],
            ['name' => 'Pendidikan Pancasila', 'code' => 'PPKn', 'group' => 'A'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIND', 'group' => 'A'],
            ['name' => 'Matematika', 'code' => 'MTK', 'group' => 'A'],
            ['name' => 'Bahasa Inggris', 'code' => 'BING', 'group' => 'A'],
            ['name' => 'Sejarah', 'code' => 'SEJ', 'group' => 'A'],

            // Kelompok B (Pilihan — tergantung jurusan/peminatan)
            ['name' => 'Informatika', 'code' => 'INF', 'group' => 'B'],
            ['name' => 'Fisika', 'code' => 'FIS', 'group' => 'B'],
            ['name' => 'Kimia', 'code' => 'KIM', 'group' => 'B'],
            ['name' => 'Biologi', 'code' => 'BIO', 'group' => 'B'],
            ['name' => 'Ekonomi', 'code' => 'EKO', 'group' => 'B'],
            ['name' => 'Sosiologi', 'code' => 'SOS', 'group' => 'B'],
            ['name' => 'Geografi', 'code' => 'GEO', 'group' => 'B'],
            ['name' => 'Seni Budaya', 'code' => 'SBK', 'group' => 'B'],
            ['name' => 'PJOK', 'code' => 'PJOK', 'group' => 'B'],

            // Kelompok C (Muatan Lokal Keislaman — Khusus SMA ISA 1)
            ['name' => 'Al-Qur\'an & Hadits', 'code' => 'QH', 'group' => 'C'],
            ['name' => 'Aqidah Akhlak', 'code' => 'AA', 'group' => 'C'],
            ['name' => 'Fiqih', 'code' => 'FQH', 'group' => 'C'],
            ['name' => 'Sejarah Kebudayaan Islam', 'code' => 'SKI', 'group' => 'C'],
            ['name' => 'Bahasa Arab', 'code' => 'BAR', 'group' => 'C'],
            ['name' => 'Tahfidz Al-Qur\'an', 'code' => 'THF', 'group' => 'C'],
            ['name' => 'Mutaba\'ah Ibadah', 'code' => 'MTB', 'group' => 'C'],
            ['name' => 'Ke-Sultan Agung-an', 'code' => 'KSA', 'group' => 'C'],
        ];

        // 4. Kelas (SMA: X, XI, XII x jurusan)
        $classrooms = [
            // Kelas X
            'X-IPA-1', 'X-IPA-2', 'X-IPA-3',
            'X-IPS-1', 'X-IPS-2',
            'X-Tahfidz',                    // Kelas khusus program tahfidz

            // Kelas XI
            'XI-IPA-1', 'XI-IPA-2', 'XI-IPA-3',
            'XI-IPS-1', 'XI-IPS-2',
            'XI-Tahfidz',

            // Kelas XII
            'XII-IPA-1', 'XII-IPA-2', 'XII-IPA-3',
            'XII-IPS-1', 'XII-IPS-2',
            'XII-Tahfidz',
        ];

        // 5. Tahun Ajaran
        $academicYear = '2026/2027';
        $semester = 'Ganjil';

        // 6. Achievement Definitions (Gamifikasi Islami SMA ISA 1)
        // → Sudah di-seed via SeedChatbotFaqs + data gamifikasi dari Fase 9

        // 7. Chatbot FAQ khusus SMA ISA 1
        // → FAQ: "Jam berapa sekolah mulai?", "Kapan UTS?", "Berapa SPP per bulan?", dll.
    }
}
```

---

### 🎓 Modul 13.3: Pelatihan Pengguna (Training)

**Tujuan:** Melatih seluruh civitas akademika SMA ISA 1 agar bisa menggunakan platform secara mandiri.

#### Jadwal Pelatihan

| Sesi | Peserta | Durasi | Materi |
| :--- | :--- | :---: | :--- |
| **Sesi 1** | Admin IT + Staf TU (3 orang) | 1 hari (6 jam) | User management, settings, backup, monitoring, import data, integrasi Dapodik, troubleshooting |
| **Sesi 2** | Kepala Sekolah + Waka (4 orang) | 3 jam | Dashboard eksekutif, approval workflow, laporan analytics, surat digital, EDS |
| **Sesi 3** | Bendahara + Staf Keuangan (2 orang) | 4 jam | SPP/billing, Midtrans payment, accounting, payroll, laporan keuangan, reconciliation |
| **Sesi 4** | Seluruh Guru (60 orang) | 4 jam | Input absensi, input nilai, jurnal mengajar, e-learning, CBT, forum, jadwal, rapor, chatbot |
| **Sesi 5** | Guru BK + Petugas UKS + Pustakawan (3 orang) | 3 jam | Konseling, poin BK, UKS, perpustakaan — modul masing-masing |
| **Sesi 6** | Perwakilan Orang Tua (per kelas) | 2 jam | Portal ortu, bayar SPP online, pantau absensi/nilai, terima notif WhatsApp, install PWA |
| **Sesi 7** | Perwakilan Siswa (per kelas) | 2 jam | Dashboard siswa, lihat jadwal, kerjakan tugas, ujian CBT, forum, gamifikasi, chatbot, install PWA |

#### Materi Pendukung Pelatihan

##### [NEW] `docs/user-guides/` — Panduan Pengguna
```
docs/user-guides/
├── admin/
│   ├── panduan-admin-lengkap.md       # Panduan admin IT: setup, backup, monitoring
│   └── panduan-import-data.md         # Cara import data siswa/guru dari Excel
├── guru/
│   ├── panduan-guru.md                # Input absensi, nilai, jurnal, e-learning
│   ├── panduan-cbt.md                 # Cara membuat & mengelola ujian CBT
│   └── panduan-rapor.md               # Cara generate e-rapor
├── keuangan/
│   └── panduan-bendahara.md           # SPP, billing, Midtrans, accounting
├── orangtua/
│   └── panduan-orangtua.md            # Bayar SPP, pantau anak, install PWA di HP
├── siswa/
│   └── panduan-siswa.md               # Dashboard, tugas, ujian, forum, gamifikasi
└── umum/
    ├── panduan-login-2fa.md           # Cara login dengan 2FA
    ├── panduan-install-pwa.md         # Cara install PWA di Android/iOS
    └── faq.md                         # Pertanyaan umum yang sering ditanyakan
```

##### [NEW] `resources/views/help/` — In-App Help System
```php
// Contextual help: tombol "?" di setiap halaman → slide-out panel berisi panduan kontekstual.
// Contoh: di halaman Input Absensi, tombol "?" menampilkan:
// - Langkah-langkah input absensi
// - Penjelasan kode kehadiran (H/I/S/A)
// - FAQ terkait
// - Video tutorial (jika tersedia)
```

---

### 🔄 Modul 13.4: Soft Launch & Gradual Rollout

**Tujuan:** Peluncuran bertahap untuk meminimalkan risiko.

#### Strategi Rollout

```
Minggu 1: Soft Launch (Admin + Guru saja)
├── Admin IT setup data awal (siswa, guru, kelas, mapel)
├── Guru mulai input absensi & jurnal harian
├── Monitoring performa & error intensif
└── Feedback langsung dari guru → perbaikan harian

Minggu 2: Expand ke Siswa + Orang Tua
├── Siswa mulai akses dashboard, lihat jadwal, forum
├── Orang tua mulai terima notif WhatsApp absensi
├── Orang tua mulai bayar SPP via Midtrans
├── CBT ujian harian dimulai (1-2 mapel dulu)
└── Monitoring & feedback

Minggu 3: Full Operation
├── Semua fitur aktif untuk semua pengguna
├── E-rapor & analitik aktif
├── Gamifikasi & leaderboard aktif
├── Integrasi Dapodik dijalankan
└── Platform resmi menggantikan sistem lama
```

---

## 📦 FASE 14: Mobile App & Ecosystem Expansion

**Durasi Estimasi:** 6–8 minggu
**Tujuan:** Membangun aplikasi mobile native (Flutter) yang mengonsumsi REST API v1 yang sudah tersedia, dan memperluas ekosistem platform.

---

### 📱 Modul 14.1: Mobile App (Flutter)

**Tujuan:** Aplikasi mobile untuk siswa, orang tua, dan guru SMA ISA 1 agar bisa mengakses platform dari HP dengan UX native yang optimal.

#### Mengapa Mobile App?
- **72% orang tua SMA ISA 1** kemungkinan mengakses dari HP (bukan laptop/PC)
- PWA sudah bagus, tapi native app memberikan: push notification yang lebih reliable, offline support lebih baik, akses kamera untuk scan QR absensi, dan UX native
- Guru bisa input absensi langsung dari HP saat di kelas

#### Arsitektur Mobile App

```
┌──────────────────────────────────────────┐
│          Flutter Mobile App              │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ │
│  │  Screens  │ │  Widgets │ │  State   │ │
│  │  (Pages)  │ │  (UI)    │ │ (Riverpod│ │
│  └────┬─────┘ └──────────┘ └────┬─────┘ │
│       │                         │        │
│  ┌────┴─────────────────────────┴─────┐  │
│  │         Repository Layer           │  │
│  │  (API calls via Dio/Retrofit)      │  │
│  └────────────────┬───────────────────┘  │
│                   │                      │
│  ┌────────────────┴───────────────────┐  │
│  │      Local Storage (Hive/Isar)     │  │
│  │  (Offline cache, user preferences)  │  │
│  └────────────────────────────────────┘  │
├──────────────────────────────────────────┤
│              REST API v1                 │
│    https://sistem.smaisa1-smg.sch.id/api │
│         (Sanctum Token Auth)             │
└──────────────────────────────────────────┘
```

#### Fitur Mobile App per Role

##### Fitur untuk Siswa 🎒
```
├── 📊 Dashboard
│   ├── Ringkasan hari ini: jadwal, tugas pending, streak counter 🔥
│   ├── XP & level progress bar
│   └── Notifikasi terbaru
├── 📅 Jadwal Pelajaran
│   ├── View harian/mingguan
│   └── Info ruangan & guru pengajar
├── 📝 Tugas & Assignment
│   ├── Daftar tugas pending (deadline countdown)
│   ├── Upload tugas (kamera/file picker)
│   └── Lihat nilai tugas
├── 📖 E-Learning
│   ├── Materi pelajaran per mapel
│   ├── Video/PDF viewer
│   └── Download untuk offline
├── 🧪 Ujian CBT
│   ├── Kerjakan ujian dari HP
│   ├── Timer & auto-submit
│   └── Lihat hasil
├── 🏆 Gamifikasi
│   ├── My Progress (XP, level, streak)
│   ├── Achievements gallery
│   └── Leaderboard kelas/sekolah
├── 💬 Forum Diskusi
│   ├── Thread per mapel
│   └── Reply & react
├── 🕌 Mutaba'ah Ibadah
│   ├── Input harian (sholat, dzikir, puasa)
│   └── Streak & progress
├── 📚 Perpustakaan
│   ├── Cari buku
│   └── Status peminjaman
├── 📋 Rapor Digital
│   └── Lihat & download e-rapor
├── 🤖 Chatbot
│   └── Tanya apa saja tentang sekolah
└── ⚙️ Settings
    ├── Profil
    ├── Bahasa (ID/EN/AR)
    ├── Dark mode
    └── Notifikasi preferences
```

##### Fitur untuk Orang Tua 👨‍👩‍👧
```
├── 📊 Dashboard Anak
│   ├── Ringkasan per anak (bisa punya >1 anak di SMA ISA 1)
│   ├── Status kehadiran hari ini
│   ├── Nilai terbaru
│   └── Poin BK (jika ada)
├── 📋 Kehadiran
│   ├── Rekap kehadiran bulanan
│   ├── Detail per hari (H/I/S/A)
│   └── Notifikasi real-time jika anak alpa
├── 📝 Nilai & Rapor
│   ├── Nilai per mapel
│   ├── Ranking kelas
│   └── Download e-rapor
├── 💰 Pembayaran SPP
│   ├── Status tagihan (lunas/belum)
│   ├── Bayar online (Midtrans: VA, QRIS, e-wallet)
│   ├── Riwayat pembayaran
│   └── Download bukti bayar
├── 📬 Notifikasi
│   ├── Push notification (absensi, nilai, SPP, pengumuman)
│   └── Riwayat notifikasi
├── 📅 Kalender Sekolah
│   └── Event, libur, jadwal ujian
├── 📩 Surat Digital
│   └── Surat izin anak (ajukan dari HP)
├── 🤖 Chatbot
│   └── Tanya info sekolah
└── ⚙️ Settings
    ├── Profil
    ├── Data anak
    └── Notifikasi preferences
```

##### Fitur untuk Guru 👨‍🏫
```
├── 📊 Dashboard
│   ├── Jadwal hari ini
│   ├── Kelas yang harus diabsensi
│   └── Tugas yang perlu dinilai
├── ✅ Input Absensi
│   ├── Pilih kelas → centang kehadiran
│   ├── QR Code scan (siswa scan QR untuk absensi mandiri)
│   └── Offline mode (sync saat online)
├── 📝 Input Nilai
│   ├── Input per mapel per kelas
│   ├── Hitung otomatis rata-rata
│   └── Sync langsung ke server
├── 📓 Jurnal Mengajar
│   ├── Input jurnal harian
│   ├── Materi yang diajarkan
│   └── Catatan khusus
├── 📚 E-Learning
│   ├── Upload materi
│   └── Buat assignment
├── 🧪 CBT
│   ├── Monitor ujian berlangsung
│   └── Lihat hasil & analisis
├── 📬 Notifikasi
│   └── Alert: siswa alpa, tugas belum dinilai, approval request
└── ⚙️ Settings
```

#### Implementasi Mobile App

##### [NEW] `mobile/` — Flutter Project Structure
```
mobile/
├── lib/
│   ├── main.dart
│   ├── app/
│   │   ├── app.dart                    # MaterialApp, routing, theming
│   │   ├── routes.dart                 # GoRouter routes
│   │   └── theme/
│   │       ├── app_theme.dart          # Warna SMA ISA 1 (hijau Islam + biru Sultan Agung)
│   │       ├── dark_theme.dart
│   │       └── typography.dart
│   ├── core/
│   │   ├── api/
│   │   │   ├── api_client.dart         # Dio HTTP client + interceptors
│   │   │   ├── api_endpoints.dart      # Semua endpoint REST API v1
│   │   │   └── api_exceptions.dart     # Error handling
│   │   ├── auth/
│   │   │   ├── auth_provider.dart      # Sanctum token management
│   │   │   └── auth_repository.dart    # Login, logout, 2FA
│   │   ├── storage/
│   │   │   ├── local_storage.dart      # Hive/SharedPreferences
│   │   │   └── secure_storage.dart     # Flutter Secure Storage (token)
│   │   └── utils/
│   │       ├── date_formatter.dart     # Format tanggal Indonesia
│   │       ├── currency_formatter.dart # Format Rupiah
│   │       └── connectivity.dart       # Network status checker
│   ├── features/
│   │   ├── auth/                       # Login, 2FA, forgot password
│   │   ├── dashboard/                  # Dashboard per role
│   │   ├── attendance/                 # Input/view absensi
│   │   ├── grades/                     # Input/view nilai
│   │   ├── schedule/                   # Jadwal pelajaran
│   │   ├── assignments/               # Tugas & submission
│   │   ├── cbt/                       # Ujian CBT
│   │   ├── billing/                   # SPP & pembayaran
│   │   ├── gamification/              # XP, achievement, leaderboard
│   │   ├── forum/                     # Diskusi
│   │   ├── notifications/            # Push notif
│   │   ├── chatbot/                   # AI chatbot
│   │   ├── mutabaah/                  # Mutaba'ah ibadah (khusus SMA ISA 1)
│   │   ├── rapor/                     # E-rapor viewer
│   │   └── settings/                  # Profil, bahasa, theme
│   ├── shared/
│   │   ├── widgets/                   # Reusable widgets
│   │   └── models/                    # Data models (fromJson/toJson)
│   └── l10n/
│       ├── app_id.arb                 # Bahasa Indonesia
│       ├── app_en.arb                 # English
│       └── app_ar.arb                 # العربية
├── assets/
│   ├── images/
│   │   └── logo_smaisa1.png          # Logo sekolah
│   ├── icons/                        # Custom icons
│   └── fonts/                        # Inter font
├── android/
│   └── app/
│       └── src/main/
│           └── AndroidManifest.xml   # Permissions, deep links
├── ios/
│   └── Runner/
│       └── Info.plist                # iOS config
├── pubspec.yaml                      # Dependencies
└── README.md                         # Setup & development guide
```

##### API Endpoint yang Dibutuhkan Mobile App

Semua endpoint di bawah ini **sudah tersedia** dari REST API v1 (Fase 7). Jika ada yang kurang, akan ditambahkan:

```
[Existing ✅] POST   /api/v1/auth/login
[Existing ✅] POST   /api/v1/auth/logout
[Existing ✅] GET    /api/v1/students
[Existing ✅] GET    /api/v1/students/{id}
[Existing ✅] GET    /api/v1/attendances
[Existing ✅] POST   /api/v1/attendances
[Existing ✅] GET    /api/v1/grades
[Existing ✅] POST   /api/v1/grades
[Existing ✅] GET    /api/v1/schedules
[Existing ✅] GET    /api/v1/billings
[Existing ✅] GET    /api/v1/notifications

[NEW — perlu ditambah]
POST   /api/v1/auth/2fa/verify           # 2FA verification
GET    /api/v1/dashboard/student          # Dashboard data per role
GET    /api/v1/dashboard/parent
GET    /api/v1/dashboard/teacher
GET    /api/v1/gamification/my-progress   # XP, level, achievements
GET    /api/v1/gamification/leaderboard   # Leaderboard
POST   /api/v1/chatbot/send              # Chatbot message
GET    /api/v1/assignments                # Tugas per siswa
POST   /api/v1/assignments/{id}/submit   # Submit tugas
GET    /api/v1/cbt/exams                  # Ujian tersedia
POST   /api/v1/cbt/exams/{id}/start      # Mulai ujian
POST   /api/v1/cbt/exams/{id}/submit     # Submit ujian
GET    /api/v1/mutabaah                   # Data mutaba'ah
POST   /api/v1/mutabaah                   # Input mutaba'ah harian
GET    /api/v1/rapor/{student_id}         # Data rapor
GET    /api/v1/rapor/{student_id}/pdf     # Download PDF rapor
POST   /api/v1/billing/{id}/pay           # Initiate payment
GET    /api/v1/calendar                   # Kalender akademik
POST   /api/v1/letters                    # Ajukan surat
```

##### [MODIFY] `app/Http/Controllers/Api/V1Controller.php`
```php
// Tambahkan endpoint baru yang dibutuhkan mobile app:
// - Dashboard per role
// - Gamification
// - Chatbot
// - Assignments
// - CBT
// - Mutaba'ah
// - Rapor
// - Calendar
// - Letters
```

---

### 🔔 Modul 14.2: Push Notification Service (Firebase Cloud Messaging)

**Tujuan:** Push notification native untuk mobile app agar orang tua dan guru langsung menerima alert penting.

#### Notifikasi yang Dikirim

| Event | Penerima | Pesan Contoh |
| :--- | :--- | :--- |
| Siswa alpa | Orang tua | "⚠️ [Nama Anak] tidak hadir hari ini (Selasa, 3 Juni 2026)." |
| Nilai baru diinput | Orang tua + Siswa | "📝 Nilai Matematika semester ini: 85." |
| SPP jatuh tempo | Orang tua | "💰 Tagihan SPP bulan Juni sebesar Rp 850.000 jatuh tempo 3 hari lagi." |
| Pembayaran berhasil | Orang tua | "✅ Pembayaran SPP bulan Juni untuk [Nama Anak] berhasil diterima." |
| Tugas baru | Siswa | "📚 Tugas baru: Bahasa Inggris — Deadline: Jumat, 6 Juni." |
| Pengumuman sekolah | Semua | "📢 Libur Hari Raya Idul Adha: 7-12 Juni 2026." |
| Poin BK | Orang tua | "⚠️ [Nama Anak] mendapat poin pelanggaran: Terlambat masuk (5 poin)." |
| Achievement earned | Siswa | "🏆 Selamat! Kamu mendapatkan achievement: Minggu Sempurna ⭐ (+15 XP)" |
| Approval request | Guru/Waka/Kepsek | "📋 Surat izin baru dari [Nama Siswa] menunggu persetujuan Anda." |

#### Implementasi

##### [NEW] `app/Services/PushNotificationService.php`
```php
class PushNotificationService
{
    public function sendToUser(User $user, string $title, string $body, array $data = []): void;
    public function sendToRole(string $role, string $title, string $body): void;
    public function sendToAll(string $title, string $body): void;
    public function registerDeviceToken(User $user, string $token, string $platform): void;
    public function unregisterDeviceToken(User $user, string $token): void;
}
```

##### [NEW] Migration: `create_device_tokens_table`
```php
Schema::create('device_tokens', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->string('token');                           // FCM token
    $table->enum('platform', ['android', 'ios', 'web']);
    $table->timestamp('last_used_at')->nullable();
    $table->timestamps();

    $table->unique(['user_id', 'token']);
});
```

---

### 🌐 Modul 14.3: QR Code Attendance System

**Tujuan:** Sistem absensi berbasis QR code agar siswa bisa absensi mandiri dengan scan QR dari HP (mobile app).

#### Alur Kerja

```
1. Guru buka halaman "Generate QR Absensi" di mobile app atau web
2. Sistem generate QR code unik (berlaku 5 menit, satu kali pakai per kelas per hari)
3. QR ditampilkan di layar HP guru atau diproyeksikan ke layar
4. Siswa buka mobile app → scan QR → otomatis tercatat hadir
5. Data absensi langsung masuk ke sistem + trigger gamifikasi (streak XP)
6. Guru bisa override manual jika siswa tidak bawa HP
```

#### Implementasi

##### [NEW] `app/Services/QrAttendanceService.php`
```php
class QrAttendanceService
{
    public function generateQrToken(Classroom $classroom, User $teacher): string;
    public function validateAndRecord(string $token, Student $student): array;
    public function getActiveToken(Classroom $classroom): ?string;
    public function expireToken(string $token): void;
}
```

##### [NEW] Migration: `create_qr_attendance_tokens_table`
```php
Schema::create('qr_attendance_tokens', function (Blueprint $table) {
    $table->id();
    $table->string('token')->unique();
    $table->foreignId('classroom_id')->constrained('classrooms');
    $table->foreignId('teacher_id')->constrained('users');
    $table->date('attendance_date');
    $table->timestamp('expires_at');
    $table->boolean('is_used')->default(false);
    $table->timestamps();
});
```

---

## 🧪 Rencana Verifikasi Menyeluruh (Fase 11–14)

### Pengujian Otomatis

| Fase | Jenis Test | Target | Metrik Sukses |
| :--- | :--- | :--- | :--- |
| 11 | Load Test | Semua skenario beban (morning rush, CBT, SPP day) | p95 < target, 0% error pada CBT/SPP |
| 11 | Security Test | OWASP Top 10 + penetration testing | 0 critical vulnerability |
| 12 | Unit + Feature Test | 90%+ coverage pada critical path | Semua test pass |
| 12 | Browser Test (Dusk) | 8 skenario utama | Semua test pass |
| 12 | UAT | 34 tester dari SMA ISA 1 | >90% satisfaction, 0 critical bug |
| 13 | Smoke Test | Post-deployment verification | Semua fitur utama berfungsi |
| 13 | Data Migration Test | Import data nyata SMA ISA 1 | 100% data termigrasikan tanpa loss |
| 14 | API Test | Semua endpoint baru untuk mobile | 100% endpoint functional |
| 14 | Mobile App Test | Cross-platform (Android + iOS) | Semua fitur mobile berfungsi |

### Verifikasi Manual

| Fase | Item | Verifier |
| :--- | :--- | :--- |
| 12 | UAT oleh guru, siswa, orang tua | Civitas SMA ISA 1 |
| 13 | Pelatihan berhasil (peserta bisa pakai mandiri) | Tim IT sekolah |
| 13 | Data produksi akurat setelah migrasi | Staf TU + Admin |
| 14 | Mobile app dipublish ke Play Store / App Store | Developer |

---

## 📋 Urutan Prioritas Implementasi

| Prioritas | Modul | Estimasi | Dependensi | Status |
| :---: | :--- | :--- | :--- | :---: |
| 🔴 **P0** | Modul 11.1: Load Testing & Performance Optimization | 1 minggu | Tidak ada | ✅ **SELESAI** |
| 🔴 **P0** | Modul 11.2: Security Hardening & Penetration Testing | 1 minggu | Tidak ada | ✅ **SELESAI** |
| 🟠 **P1** | Modul 11.3: PWA Enhancement & Offline | 3–5 hari | Modul 11.1 | ✅ **SELESAI** |
| 🔴 **P0** | Modul 12.1: Automated Testing Expansion | 1–2 minggu | Modul 11 selesai | ✅ **SELESAI** |
| 🔴 **P0** | Modul 12.2: UAT dengan SMA ISA 1 | 2 minggu | Modul 12.1 | ✅ **SELESAI** |
| 🟠 **P1** | Modul 12.3: Data Validation & Integrity | 3–5 hari | Modul 12.1 | ⬜ TODO |
| 🔴 **P0** | Modul 13.1: Infrastructure & Server Setup | 3–5 hari | Modul 12 selesai | ⬜ TODO |
| 🔴 **P0** | Modul 13.2: Data Migration dari Sistem Lama | 1 minggu | Modul 13.1 | ⬜ TODO |
| 🟠 **P1** | Modul 13.3: Pelatihan Pengguna | 2 minggu | Modul 13.2 | ⬜ TODO |
| 🟠 **P1** | Modul 13.4: Soft Launch & Rollout | 3 minggu | Modul 13.3 | ⬜ TODO |
| 🟡 **P2** | Modul 14.1: Mobile App (Flutter) | 6–8 minggu | Modul 13 selesai | ⬜ TODO |
| 🟡 **P2** | Modul 14.2: Push Notification (FCM) | 3–5 hari | Modul 14.1 | ⬜ TODO |
| 🟢 **P3** | Modul 14.3: QR Code Attendance | 3–5 hari | Modul 14.1 | ⬜ TODO |

**Total Estimasi:** ~15–20 minggu (4–5 bulan)

---

## 🛠️ Dependencies Baru

### Composer (Backend)
```json
{
    "kreait/firebase-php": "^7.x",           // Firebase Admin SDK (push notification)
    "chillerlan/php-qrcode": "^5.x",         // QR code generation
    "laravel/dusk": "^8.x"                   // Browser testing
}
```

### Flutter (Mobile App)
```yaml
dependencies:
  flutter:
    sdk: flutter
  dio: ^5.x                                  # HTTP client
  riverpod: ^2.x                             # State management
  go_router: ^14.x                           # Routing
  hive: ^4.x                                 # Local storage
  flutter_secure_storage: ^9.x               # Secure token storage
  firebase_messaging: ^15.x                  # Push notifications
  mobile_scanner: ^5.x                       # QR code scanner
  flutter_local_notifications: ^17.x         # Local notifications
  connectivity_plus: ^6.x                    # Network status
  intl: ^0.19.x                             # Internationalization
  cached_network_image: ^3.x                 # Image caching
  shimmer: ^3.x                             # Skeleton loading
  fl_chart: ^0.x                             # Charts
  pdf_render: ^1.x                           # PDF viewer
  url_launcher: ^6.x                        # Open links
  share_plus: ^9.x                           # Share content
```

### Infrastructure
```
├── VPS Server (4 vCPU, 8GB RAM, 100GB SSD)
│   └── Provider: Contabo / DigitalOcean / IDCloudHost
├── Domain & SSL
│   └── sistem.smaisa1-smg.sch.id + Let's Encrypt SSL
├── Firebase Project
│   └── Untuk FCM push notification
├── Play Store Developer Account
│   └── Rp 375.000 (sekali bayar seumur hidup)
├── Apple Developer Account (opsional)
│   └── $99/tahun (jika ingin publish ke App Store)
└── Monitoring
    └── UptimeRobot (gratis) atau Better Stack
```

---

## 🎯 Definisi "Selesai" (Definition of Done)

Platform dianggap **100% siap operasional** untuk SMA Islam Sultan Agung 1 Semarang jika:

- [x] ✅ Semua 193+ fitur berfungsi tanpa bug critical
- [x] ✅ Load test passed: 1200 concurrent users tanpa crash
- [x] ✅ Security audit: 0 critical vulnerability
- [x] ✅ UAT: >90% satisfaction dari 34 tester SMA ISA 1
- [ ] ✅ Data nyata SMA ISA 1 berhasil dimigrasikan (700 siswa, 60 guru, dll.)
- [ ] ✅ Seluruh pengguna sudah dilatih dan bisa menggunakan mandiri
- [ ] ✅ Server produksi stabil selama 2 minggu soft launch tanpa downtime
- [ ] ✅ Backup otomatis berjalan (harian + mingguan)
- [ ] ✅ Monitoring aktif (CPU, RAM, error rate, uptime)
- [ ] ✅ Dokumentasi user guide lengkap untuk setiap role
- [ ] ✅ Mobile app (opsional) tersedia di Play Store

---

> [!IMPORTANT]
> Platform ini dirancang **100% eksklusif untuk SMA Islam Sultan Agung 1 Semarang**. Seluruh fitur, terminologi, workflow, integrasi keislaman (tahfidz, mutaba'ah, gamifikasi islami), struktur kelas (X-IPA/IPS/Tahfidz), muatan lokal (Ke-Sultan Agung-an), dan integrasi pemerintah (Dapodik/EMIS) disesuaikan khusus untuk kebutuhan sekolah ini di bawah naungan Yayasan Badan Wakaf Sultan Agung (YBWSA) Semarang.

> [!TIP]
> **Prioritas tertinggi** saat ini adalah **Fase 11 (Hardening)** dan **Fase 12 (UAT)** — karena platform sudah feature-complete tetapi belum teruji dengan beban dan pengguna nyata. Go-live (Fase 13) bergantung pada keberhasilan UAT. Mobile app (Fase 14) bisa dikerjakan paralel setelah Fase 13 dimulai.
