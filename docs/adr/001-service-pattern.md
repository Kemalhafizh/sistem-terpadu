# ADR 001: Penerapan Service Pattern untuk Logika Bisnis Kompleks

- **Status:** Disetujui (Approved)
- **Tanggal:** 2026-05-18

---

## Konteks dan Masalah

Banyak alur bisnis di SISTA melibatkan beberapa tabel sekaligus, misalnya pembuatan lembar nilai rapor, kalkulasi gaji, dan evaluasi kelulusan. Menempatkan logika tersebut di Controller atau model Eloquent membuat file membengkak, sulit diuji terpisah, dan menyulitkan pemakaian ulang antar endpoint Web, API, dan Scheduler.

## Keputusan

Seluruh logika bisnis lintas model ditempatkan pada kelas layanan di `app/Services/`:
1. Service class menangani perhitungan, transaksi database, dan aturan domain.
2. Controller hanya menangani validasi input, otorisasi, pemanggilan service, serta respons HTML/JSON.
3. Setiap service diinjeksi melalui constructor agar mudah diuji unit.

## Dampak dan Konsekuensi

### Positif
- **Pemakaian ulang:** logika yang sama dapat dipanggil dari Web Controller, API Controller, maupun Scheduler tanpa duplikasi.
- **Kemudahan pengujian:** alur bisnis dapat diuji terisolasi tanpa overhead siklus request HTTP penuh.
- **Keterbacaan:** batas tanggung jawab antara lapisan HTTP dan logika domain menjadi jelas.

### Negatif
- Jumlah file kelas PHP di direktori `app/Services/` bertambah seiring penambahan modul.