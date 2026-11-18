# Panduan Kontribusi dan Standar Rekayasa SISTA

Dokumen ini menetapkan aturan rekayasa perangkat lunak untuk pengembangan backend dan web **SISTA (`sistem-terpadu`)** beserta integrasinya dengan aplikasi Android (`sista-android`). Beberapa aturan di bawah ini ditegakkan secara otomatis oleh pengujian arsitektur di pipeline CI.

---

## 1. Prinsip Utama Kode

### A. Tidak Ada Angka Karangan
Database adalah satu-satunya sumber kebenaran data. Apabila suatu metrik atau kolom belum memiliki data, kembalikan `null` (atau pesan galat yang jujur), bukan angka rekaan.
- Dilarang menggunakan `rand()`, Faker, `->random()`, atau nilai default fiktif di dalam direktori `app/` untuk mengisi tampilan maupun respons API (misalnya tingkat kehadiran 0% saat belum ada sesi, atau ukuran kelas hari ini untuk semester lampau).
- Kondisi "belum ada data" direpresentasikan sebagai `null` (ditampilkan sebagai `–` oleh klien). Angka `0` hanya digunakan untuk nilai nol yang nyata (misalnya belum ada pembayaran pada bulan berjalan).
- **Guard CI:** `tests/Feature/Architecture/NoFabricatedRandomnessTest.php` menggagalkan CI apabila ada pemanggilan fungsi acak baru di `app/`. Penggunaan acak yang sah (pengacakan soal ujian ber-seed pada `CbtService`, pembuatan rahasia CSPRNG pada `TotpSecurityService` dan `User`) didaftarkan secara eksplisit pada konstanta `ALLOWED`.

### B. Setiap Aksi Harus Tersimpan
Setiap tombol atau endpoint yang menyatakan perubahan status wajib benar-benar mengubah data di server.
- Dilarang mengembalikan respons sukses ("berhasil disimpan") untuk operasi yang belum diimplementasikan atau tidak menyimpan perubahan ke database.
- Apabila suatu fitur belum memiliki implementasi backend, kembalikan status HTTP `404` atau `501` dengan pesan yang jelas.

### C. Satu Aturan Akses untuk Web dan API
Setiap peran dan klien (web maupun Android) membaca aturan akses yang terpusat; jangan menyalin daftar peran secara literal di banyak controller.
- Gunakan konstanta peran pada `App\Support\Access\StudentDataAccess` (`STUDENT_ROLES`, `PARENT_ROLES`, `TEACHER_ROLES`, `STAFF_ROLES`) dan helper `App\Support\Access\Roles::only(...)`.
- Hak akses halaman web ditentukan dari middleware `role:`/`can:` pada route melalui `App\Support\Access\RouteAccess`, sedangkan kapabilitas menu aplikasi Android ditentukan melalui `App\Support\MobileCapabilities` (`config/mobile_features.php`).
- Hanya `superadmin` yang melewati seluruh pemeriksaan akses secara global; `admin` tetap mengikuti aturan route dan policy.
- **Batasi cakupan data ke pemiliknya (Anti-IDOR):** orang tua hanya dapat mengakses data anaknya sendiri, guru hanya kelas yang diajar atau diwalikannya pada tahun ajaran aktif, dan siswa hanya data dirinya sendiri (`Student::visibleTo($user)` dan `Classroom::isVisibleTo($user)`). Cari record berdasarkan `id` atau `uuid` beserta pemeriksaan kepemilikan, jangan hanya berdasarkan nama.

### D. Setiap Perubahan Perilaku Wajib Memiliki Test
- Setiap penambahan fitur atau perbaikan bug wajib disertai pengujian Pest/PHPUnit di `tests/` yang menguji skenario utama, kasus data kosong (`null`, bukan `0`), serta batasan akses (peran lain atau keluarga lain menerima `403`/`404`).
- Jangan menghapus atau melemahkan pengujian yang sudah ada hanya agar CI lolos.

---

## 2. Kontrak Mobile API (`/api/v1`)

- Pertahankan kompatibilitas field yang dibaca oleh aplikasi Android (`sista-android`). Jika mengubah arti field atau menghapus field, pastikan perubahan disinkronkan dengan repositori `sista-android`.
- Gunakan `ApiResponseTrait` (`success`, `message`, `data`) secara konsisten.
- Tulis pesan yang ditampilkan ke pengguna dalam Bahasa Indonesia menggunakan helper `__()` dan tambahkan terjemahan Bahasa Inggris serta Bahasa Arab di `lang/en.json` dan `lang/ar.json`. Middleware `SetLocale` memilih bahasa berdasarkan `users.preferred_locale`, session, lalu header `Accept-Language`.
- Untuk nilai status atau kategori yang menjadi dasar logika di aplikasi klien, sertakan kode stabil di samping label terjemahan (contoh: `status` + `status_code`, `category` + `category_code`).
- Kirimkan waktu dalam format ISO-8601 (`toIso8601String()`), jangan hanya `diffForHumans()`.
- Gunakan `Storage::disk('public')->url(...)` untuk tautan berkas pada disk publik.

---

## 3. Pemeriksaan Sebelum Push / Pull Request

Jalankan ketiga perintah berikut secara lokal sebelum mengirimkan perubahan:

```bash
vendor/bin/pint                      # Pemeriksaan gaya kode PSR-12 (CI menjalankan pint --test)
php artisan test                     # Menjalankan seluruh rangkaian pengujian Pest/PHPUnit
vendor/bin/phpstan analyse           # Analisis statis dengan phpstan-baseline.neon
```

Catatan:
- `reportUnmatchedIgnoredErrors` pada PHPStan berstatus aktif. Apabila perbaikan tipe data pada kode membuat entri di `phpstan-baseline.neon` tidak lagi relevan, perbarui atau hapus entri baseline tersebut dalam PR yang sama.

---

## 4. Konvensi Git dan Pull Request

- Tulis pesan commit dan deskripsi Pull Request dalam Bahasa Indonesia yang lugas: jelaskan masalah yang diperbaiki, penyebabnya, dan perubahan yang dilakukan.
- Buat perubahan secara terfokus dalam branch fitur/perbaikan lalu ajukan melalui Pull Request ke branch `main`.
- Jangan pernah melakukan commit file `.env` atau kredensial rahasia ke dalam repositori.