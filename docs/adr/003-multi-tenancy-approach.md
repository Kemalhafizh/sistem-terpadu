# ADR 003: Pendekatan Multi-Database SQLite per Sekolah

*   **Status:** Disetujui (Approved)
*   **Tanggal:** 2026-05-24

---

## 📋 Konteks & Masalah
Dalam merancang sistem SaaS SISTA agar dapat melayani ratusan sekolah secara simultan, kami perlu memilih pendekatan multi-tenancy yang tepat. Ada dua opsi utama:
1.  **Single-Database (Column Prefixing):** Semua sekolah berbagi satu database tunggal yang besar. Pemisahan data dilakukan dengan menyaring kolom `tenant_id` pada setiap query.
2.  **Multi-Database (Database Separation):** Setiap sekolah memiliki satu berkas database terpisah secara fisik.

---

## 💡 Keputusan yang Diambil
Kami memutuskan untuk menerapkan **Multi-Database per Tenant (Sekolah)** menggunakan **SQLite**:
1.  Setiap sekolah memiliki file database fisik mandiri (`tenant_[sekolah-slug].sqlite`).
2.  Pemisahan data bersifat mutlak di tingkat berkas sistem operasi, bukan sekadar logika filter query SQL.
3.  SQLite dipilih karena sangat hemat sumber daya, tidak memerlukan server database server-wide terpisah (seperti MySQL/Postgres), dan sangat cepat untuk operasi tulis/baca skala sekolah menengah.

---

## 📊 Dampak & Konsekuensi
### Dampak Positif:
*   **Keamanan Data Mutlak:** Risiko kebocoran data (*data leakage*) antar sekolah adalah **0%** karena data berada di file fisik terpisah.
*   **Kustomisasi Mudah:** Sekolah dapat dengan mudah melakukan ekspor/backup database mereka sendiri secara utuh tanpa mengganggu sekolah lain.
*   **Performa Terdistribusi:** Ukuran database per file tetap kecil, menjaga performa query tetap cepat.

### Dampak Negatif:
*   Proses update skema tabel database (migrations) di produksi harus di-looping ke seluruh berkas database tenant yang terdaftar. Hal ini diatasi menggunakan perintah otomatis bawaan package `php artisan tenants:migrate`.