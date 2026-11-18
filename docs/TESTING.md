# Panduan Pengujian Otomatis SISTA

Basis kode SISTA dilengkapi dengan pengujian otomatis menggunakan **Pest PHP** dan **PHPUnit**, serta analisis statis menggunakan **PHPStan**.

---

## 1. Menjalankan Pengujian

Pastikan dependensi Composer telah terpasang, lalu jalankan perintah berikut dari direktori root repositori:

```bash
# Menjalankan seluruh rangkaian pengujian
php artisan test

# Menjalankan satu berkas pengujian tertentu
php artisan test tests/Feature/Academic/ClassSessionFlowTest.php

# Menjalankan pengujian berdasarkan filter nama
php artisan test --filter=RouteGuardTest
```

Untuk pemeriksaan gaya kode dan analisis statis:

```bash
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

---

## 2. Pengujian Guard Arsitektur (`tests/Feature/Architecture/`)

Selain pengujian fitur per modul, terdapat pengujian arsitektur yang menjaga aturan rekayasa sistem secara otomatis di CI:

| Berkas Test | Fungsi Pemeriksaan |
| :--- | :--- |
| `NoFabricatedRandomnessTest.php` | Memastikan tidak ada pemanggilan `rand()`, `mt_rand()`, `Faker`, atau `->random()` baru di dalam `app/` yang menghasilkan angka karangan. |
| `RouteGuardTest.php` | Memastikan setiap route web ber-`auth` memiliki middleware `role:` atau `can:`, kecuali yang terdaftar eksplisit pada `config/route_access.php`. |
| `RemovedModulesStayRemovedTest.php` | Memastikan prefix route modul yang telah dipensiunkan tidak ditambahkan kembali ke dalam daftar route aplikasi. |
| `ApiControllersRoutedTest.php` | Memastikan setiap controller API di bawah `app/Http/Controllers/Api/` benar-benar terhubung ke route aktif di `routes/api.php`. |
| `NoEmojiGovernanceTest.php` | Memastikan tampilan antarmuka baru tidak menggunakan karakter emoji sebagai pengganti ikon SVG. |
| `DeployWorkflowTest.php` | Memverifikasi konfigurasi workflow deployment GitHub Actions. |

---

## 3. Pengujian Fitur Utama (`tests/Feature/`)

Beberapa cakupan pengujian fitur utama di dalam repositori:
- **Akademik & Sesi Kelas:** `tests/Feature/Academic/ClassSessionFlowTest.php`, `DailyAssessmentTest.php`, `CurriculumTest.php`.
- **Keuangan & Tagihan:** `tests/Feature/Finance/PaymentSimulatorOffTest.php`, `RkasBudgetPlanningTest.php`, `CharitySavingIsolationTest.php`, `StudentBillingAccessTest.php`.
- **Kepegawaian (HRD):** `tests/Feature/HRD/FingerprintImportTest.php`, `PayrollRecapAndLeaveWorkflowTest.php`, `EvaluationHonestyTest.php`.
- **Kesiswaan & BK:** `tests/Feature/Counseling/BullyingReportMobileApiTest.php`, `CounselingFlowTest.php`, `UksFlowTest.php`.
- **Keamanan & Akses:** `tests/Feature/Security/Fase118AccessControlTest.php`, `CspAssetGuardTest.php`, `AuditLogChainTest.php`.
- **Integrasi Pemerintah:** `tests/Feature/Integration/DapodikEmisSpmbHonestyTest.php`.

---

## 4. Lingkungan Database Pengujian

Sesuai konfigurasi `phpunit.xml`, pengujian berjalan menggunakan koneksi database SQLite in-memory (`:memory:`) dengan trait `RefreshDatabase` sehingga eksekusi terisolasi dan tidak mengubah berkas database lokal.