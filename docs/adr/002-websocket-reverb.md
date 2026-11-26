# ADR 002: Pemilihan Laravel Reverb sebagai Server WebSocket Bawaan

- **Status:** Disetujui (Approved)
- **Tanggal:** 2026-05-20

---

## Konteks dan Masalah

SISTA membutuhkan komunikasi waktu nyata dua arah untuk obrolan wali murid, siaran alarm absensi, notifikasi in-app, serta pengawasan CBT. Layanan pihak ketiga (seperti Pusher atau Ably) dibatasi kuota request dan biaya berlangganan, sedangkan server Node.js terpisah menambah komponen runtime yang harus dikelola di VPS sekolah.

## Keputusan

Digunakan **Laravel Reverb** sebagai server WebSocket utama aplikasi SISTA:
1. Berjalan langsung di ekosistem Laravel tanpa runtime Node.js tambahan untuk proses WebSocket.
2. Terintegrasi langsung dengan Laravel Event Broadcasting dan Echo client di frontend.
3. Dijalankan secara self-hosted pada port `8080`.

## Dampak dan Konsekuensi

### Positif
- **Tanpa biaya pihak ketiga:** sepenuhnya *self-hosted* dan open-source.
- **Konfigurasi terintegrasi:** menggunakan driver `reverb` bawaan Laravel pada konfigurasi broadcasting.
- **Kapasitas koneksi memadai:** mendukung banyak koneksi simultan melalui loop asinkron ReactPHP.

### Negatif
- Server produksi perlu menjalankan daemon systemd atau Supervisor agar proses `reverb:start` tetap aktif.