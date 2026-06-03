# ADR 001: Penerapan Service Pattern untuk Logika Bisnis Kompleks

*   **Status:** Disetujui (Approved)
*   **Tanggal:** 2026-05-18

---

## 📋 Konteks & Masalah
Di dalam sistem pengelolaan sekolah terpadu SISTA, banyak ditemui logika bisnis lintas-tabel yang sangat kompleks (misalnya: pembuatan lembar nilai rapor, kalkulasi bruto penggajian guru BK, dan penyusunan kriteria kelulusan). Menuliskan logika ini langsung di dalam Controller akan membuat file controller menjadi terlalu besar (*Fat Controller*), sulit dibaca, dan menghalangi pemanfaatan kembali kode (*code reusability*). Menuliskannya di model Eloquent juga melanggar prinsip *Single Responsibility Principle* (SRP).

---

## 💡 Keputusan yang Diambil
Kami memutuskan untuk menerapkan **Service Pattern** sebagai lapisan perantara (*Service Layer*) di antara Controller dan Eloquent Model:
1.  Semua logika bisnis kelas berat diletakkan di dalam kelas layanan khusus di folder `app/Services/`.
2.  Controller hanya bertanggung jawab menerima request HTTP, melakukan validasi dasar, memanggil Service Class yang sesuai, dan mengembalikan respon HTML/JSON.
3.  Setiap Service Class dirancang agar mandiri, teruji unit, dan dapat disuntikkan secara dinamis (*dependency injection*).

---

## 📊 Dampak & Konsekuensi
### Dampak Positif:
*   **Code Reusability:** Logika yang sama (seperti pembuatan alert deteksi dini BK) dapat dipanggil dari Web Controller, API Controller, maupun Scheduler Task secara konsisten.
*   **Ease of Testing:** Pengujian unit (*unit testing*) menjadi sangat mudah dilakukan karena logika bisnis murni diisolasi dari siklus request-response HTTP.
*   **Maintainability:** Mempermudah developer baru memahami alur bisnis aplikasi karena terorganisasi dengan rapi.

### Dampak Negatif:
*   Meningkatkan jumlah berkas kelas PHP baru di dalam struktur aplikasi.