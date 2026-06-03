# 🏢 Implementasi Arsitektur Multi-Tenancy di SISTA

SISTA dirancang sebagai sistem **SaaS Multi-Tenant** kelas industri menggunakan paket `stancl/tenancy`. Dokumen ini merangkum detail implementasi, mekanisme pemisahan data, serta solusi testing bridge.

---

## 📂 1. Struktur File & Migrasi

Sistem membedakan secara tegas migrasi central (Landlord) yang mengelola administrasi SaaS dari migrasi operasional sekolah (Tenant):

*   **Migrasi Landlord (`database/migrations/`):**
    *   `2019_09_15_000010_create_tenants_table.php` : Skema tabel sekolah terdaftar.
    *   `2019_09_15_000020_create_domains_table.php` : Skema tabel domain/subdomain sekolah.
*   **Migrasi Tenant (`database/migrations/tenant/`):**
    *   Menampung **81 tabel** operasional sekolah (Siswa, Nilai, Pegawai, Kas, Buku, Aset, dll).
    *   Tabel-tabel ini akan otomatis di-migrate setiap kali tenant baru dibuat melalui Onboarding Wizard.

---

## 💾 2. Model Pemisahan Database (Multi-Database SQLite)

Setiap tenant/sekolah memiliki database fisik terisolasi bertipe SQLite. Konfigurasi dinamis ini diatur melalui:

### A. Connection Template (`config/database.php`)
Kami mendefinisikan template koneksi `tenant_template` bertipe sqlite dengan database kosong:
```php
'tenant_template' => [
    'driver' => 'sqlite',
    'database' => ':memory:', // Diganti secara dinamis saat runtime
    'prefix' => '',
    'foreign_key_constraints' => true,
],
```

### B. Konfigurasi Path Database (`config/tenancy.php`)
Ketika tenancy diinisialisasi oleh domain/subdomain, tenancy engine secara dinamis membuat/membuka berkas database sekolah di direktori khusus:
```php
'database' => [
    'central_connection' => 'sqlite',
    'template_tenant_connection' => 'tenant_template',
    'prefix' => 'tenants/tenant_',
    'suffix' => '.sqlite',
],
```
*Hasil berkas:* `database/tenants/tenant_[tenant-id].sqlite`

---

## 🔌 3. Proses Dynamic Provisioning & Seeder Hook

Ketika sekolah mendaftar melalui Onboarding Wizard, proses penyediaan database berjalan di latar belakang:
1.  **Migrate Run:** Tenancy engine menjalankan `php artisan tenants:migrate` untuk membangun 81 tabel operasional di DB tenant yang baru.
2.  **Seeder Hook:** Konfigurasi `seeder_parameters` di `config/tenancy.php` memanggil `TenantSystemSeeder.php` secara otomatis. Seeder ini mengisi data bawaan sekolah seperti:
    *   Kategori Poin BK (Terlambat, Berkelahi, Tahfidz Reward, dll).
    *   Definisi workflow digital approval (Leave request, letter approval).
    *   Lencana prestasi e-portfolio (Juara Kelas 🏆, Pelopor Sains 🔬, dll).
3.  **Tenant Context Callback:** Setelah seeder selesai, sistem menjalankan fungsi callback untuk menginstansiasi akun pengguna Administrator sekolah pertama, kelas pertama, dan tahun akademik aktif pertama di database tenant tersebut.

---

## 🧪 4. Testing Bridge (Pencegahan Kerusakan Uji Legacy)

SISTA memiliki 80+ file pengujian fungsional legacy yang dirancang untuk basis data tunggal. Mengaktifkan multi-tenancy di lingkungan testing biasanya merusak test tersebut karena database terisolasi tidak terbungkus transaksi testing default.

Kami merancang solusi **Testing Bridge** yang cerdas:

### A. Pengaktifan Migrasi Dinamis di `AppServiceProvider::boot()`
Jika aplikasi berjalan di lingkungan testing, kami memuat migrasi tenant ke database pusat pengujian secara dinamis agar test legacy dapat berjalan lancar tanpa sadar adanya tenancy:
```php
if ($this->app->environment('testing')) {
    $this->loadMigrationsFrom(database_path('migrations/tenant'));
}
```

### B. Modifikasi Wrapper Transaksi di `tests/TestCase.php`
Kami memodifikasi method `connectionsToTransact()` pada kelas induk pengujian. Jika test yang berjalan adalah `TenancyTest` (yang menguji proses dynamic switching koneksi fisik database), sistem menonaktifkan transaksi wrap SQLite agar pembuatan file database tenant fisik dapat diuji secara nyata:
```php
protected function connectionsToTransact()
{
    // Jika menguji fungsionalitas multi-tenant, matikan transaksi wrap agar koneksi fisik SQLite bekerja
    if (get_class($this) === \Tests\Feature\DevOps\TenancyTest::class) {
        return [];
    }

    return [config('database.default')];
}
```