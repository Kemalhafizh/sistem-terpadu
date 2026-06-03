# 🤝 Panduan Kontribusi SISTA

Kami sangat senang Anda tertarik untuk berkontribusi pada pengembangan **SISTA**! Demi menjaga kualitas basis kode (*codebase*) yang bersih, aman, dan berkinerja tinggi, harap patuhi panduan berikut saat mengirimkan kontribusi.

---

## 📝 1. Standar Penulisan Kode (Coding Standards)

SISTA mengikuti standar penulisan kode PHP modern yang ketat:
*   **Spesifikasi Gaya:** Wajib mengikuti standar **PSR-12** secara menyeluruh.
*   **Laravel Pint:** Kami menggunakan Laravel Pint untuk memformat kode secara otomatis. Sebelum melakukan commit, selalu jalankan pembersih kode:
    ```bash
    ./vendor/bin/pint
    ```
*   **Strict Types:** Sangat disarankan menyertakan deklarasi `declare(strict_types=1);` di bagian atas berkas PHP baru, terutama untuk Service Class dan API Transformers.

---

## 🌿 2. Workflow Pencabangan (Branching Workflow)

Kami menggunakan pola git-flow yang dimodifikasi untuk pengembangan modular:

*   **`main` / `master`:** Cabang produksi yang stabil. Hanya boleh di-merge dari cabang `release/*` atau perbaikan darurat `hotfix/*`.
*   **`develop`:** Cabang utama pengembangan harian. Seluruh fitur baru digabungkan ke sini.
*   **`feature/[nama-modul]`:** Cabang pengerjaan fitur baru. Dibuat dari `develop` dan digabungkan kembali ke `develop` melalui Pull Request (PR).
    *   *Contoh:* `feature/smart-alerts`, `feature/cbt-secure-mode`.
*   **`hotfix/[nama-bug]`:** Cabang perbaikan bug mendesak di lingkungan produksi. Dibuat langsung dari `main` dan setelah selesai digabungkan kembali ke `main` dan `develop`.

---

## 💬 3. Panduan Pesan Commit (Commit Message Guidelines)

Pesan commit harus informatif dan berformat rapi untuk mempermudah pelacakan *changelog*:

**Format:** `<tipe>(<modul/area>): <deskripsi singkat>`

### Tipe Commit yang Diizinkan:
*   `feat`: Menambahkan fitur baru (misal: `feat(cbt): add fullscreen visibility check trigger`).
*   `fix`: Memperbaiki kesalahan/bug (misal: `fix(tenancy): fix auto-increment conversion on string key`).
*   `docs`: Pembaruan dokumentasi saja (misal: `docs(api): update example response for teacher schedules`).
*   `style`: Perubahan visual, spasi, atau formatting tanpa mempengaruhi logika kode.
*   `refactor`: Pengorganisasian ulang kode tanpa mengubah perilakunya.
*   `test`: Menambahkan atau memodifikasi file tes (misal: `test(portfolio): add public link validation features`).

---

## 📥 4. Proses Mengirimkan Pull Request (PR)

1.  Lakukan *fork* repositori ini dan buat cabang baru dari `develop`.
2.  Tulis kode Anda secara bersih dan tambahkan unit/feature tests di bawah folder `tests/Feature/`.
3.  Jalankan seluruh test suite Pest lokal untuk memastikan tidak ada pengujian yang gagal:
    ```bash
    vendor/bin/pest
    ```
4.  Jalankan `pint` untuk merapikan kode.
5.  Kirimkan Pull Request (PR) Anda ke cabang `develop` dengan deskripsi jelas mengenai:
    *   Apa masalah yang diselesaikan?
    *   Bagaimana pendekatan solusinya?
    *   Tangkapan layar/bukti pengujian yang sukses.
6.  Tunggu ulasan teknis (*code review*) dari tim core developer sebelum di-merge.