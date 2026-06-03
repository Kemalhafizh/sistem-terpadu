# ADR 002: Pemilihan Laravel Reverb sebagai Server WebSocket Bawaan

*   **Status:** Disetujui (Approved)
*   **Tanggal:** 2026-05-20

---

## 📋 Konteks & Masalah
SISTA memerlukan komunikasi dua arah waktu nyata (*real-time bidirectional*) untuk mendukung fitur obrolan pesan wali murid, alarm kedatangan absen GPS, notifikasi in-app instan, dan proctoring ujian online. Sebelumnya, opsi yang umum digunakan adalah layanan pihak ketiga seperti Pusher/Ably yang berbayar dan dibatasi limit kuota request, atau memasang server Node.js terpisah (Socket.io/Soketi) yang menambah kompleksitas deployment server VPS sekolah.

---

## 💡 Keputusan yang Diambil
Kami memutuskan untuk menggunakan **Laravel Reverb** sebagai server WebSocket utama aplikasi SISTA:
1.  Laravel Reverb adalah WebSocket server bawaan ekosistem Laravel modern yang ditulis menggunakan PHP (sangat optimal dan hemat memori).
2.  Komunikasi terintegrasi mulus dengan Laravel Event Broadcasting menggunakan Echo Client di frontend.
3.  Semua komunikasi dihosting secara lokal pada port `8080` sehingga sekolah tidak perlu membayar biaya berlangganan pihak ketiga apa pun.

---

## 📊 Dampak & Konsekuensi
### Dampak Positif:
*   **Nol Biaya Operasional:** Sepenuhnya open-source dan di-host mandiri (*self-hosted*).
*   **Integrasi Native:** Konfigurasi broadcasting langsung menggunakan driver `reverb` bawaan file `.env` Laravel tanpa package adaptor pihak ketiga yang berat.
*   **Performa Tinggi:** Mendukung ribuan koneksi simultan menggunakan arsitektur non-blocking berbasis ReactPHP.

### Dampak Negatif:
*   Server produksi wajib menjalankan *background service* Systemd daemon atau supervisor konstan untuk memastikan proses Reverb tetap hidup.