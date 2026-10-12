# Kerangka Materi Presentasi SISTA ke Sekolah

> **Untuk:** pemilik produk. Bagian dari Milestone Demo D7.
> **Durasi:** ±10 menit slide + 15 menit demo (`SKRIP_DEMO.md`) + tanya jawab.
> **Pendengar:** Waka Kurikulum, Kepala TU, dan **IT sekolah** dulu (obrolan santai lewat jalur alumni), lalu Kepala Sekolah (presentasi resmi).
> **Soal IT sekolah:** beliau lulusan Informatika dan S2 Teknik Komputer, dan dialah yang akan merawat SISTA. Temui **sebelum** presentasi resmi. Posisikan beliau sebagai **calon pemilik sistem**, bukan orang yang pekerjaannya digantikan: tanyakan sistem apa yang sedang beliau jalankan, apa yang merepotkan, dan minta beliau menilai kode. Bila IT sekolah yakin, Kepala Sekolah akan bertanya kepadanya dan jawabannya menjadi penentu.
> **Prinsip:** mulai dari **masalah sehari-hari mereka**, bukan dari daftar fitur. Jujur soal mana yang sudah jadi dan mana yang masih rancangan.
> **Slide final** dibuat setelah server demo jalan, supaya bisa memakai tangkapan layar asli dari data fiktif.

---

| # | Judul slide | Isi | Catatan pembicara |
|---|---|---|---|
| 1 | **SISTA: Sistem Terpadu SMA Islam Sultan Agung 1** | Logo sekolah (minta izin), nama pemilik produk, angkatan alumni | "Saya alumni angkatan … Ini usulan dari saya untuk almamater" |
| 2 | **Satu hari di Sula 1 hari ini** | 4 kartu masalah: ① izin siswa lewat WA pribadi wali kelas; ② nilai diinput lagi untuk e-Rapor; ③ kalimat rapor rusak ("sangat baik dalam **FALSE**", contoh **dianonimkan**); ④ banyak hal menunggu tanda tangan Kepala Sekolah | Tanyakan: "Apakah ini terasa familiar, Bapak/Ibu?" Biarkan mereka menambahkan |
| 3 | **SISTA dalam satu kalimat** | ERP **khusus untuk satu sekolah**: web untuk pekerjaan meja (TU, waka, Kepala Sekolah), aplikasi Android untuk kelas dan rumah (guru, siswa, orang tua) | Tekankan: bukan aplikasi generik, dirancang mengikuti alur Sula 1 |
| 4 | **Demo langsung** | (Pindah ke HP dan laptop, ikuti `SKRIP_DEMO.md` babak 1–4) | Biarkan mereka memegang HP demo sendiri |
| 5 | **Peta alur kerja sekolah** | Tangkapan peta alur: siklus satu tahun ajaran + jumlah alur, dengan tanda mana yang sudah ada dan mana yang dalam pengerjaan | Tunjukkan bahwa ini dipikirkan menyeluruh, bukan tambal sulam |
| 6 | **Yang membuat SISTA berbeda** | ① **Tidak ada data karangan**: kosong ditulis "–", bukan angka palsu; ② **Anti-macet**: persetujuan massal, tenggat, cadangan saat pimpinan berhalangan; ③ **Bisa diatur sendiri**: jabatan, kalender akademik, dan batas persetujuan diatur Admin sekolah | Satu kalimat per poin, jangan teknis |
| 7 | **Bekerja bersama e-Rapor & Dapodik** | Diagram: Dapodik → (impor awal) → SISTA → (ekspor nilai) → e-Rapor → PDF rapor resmi → orang tua di HP | Hilangkan kekhawatiran "nanti kerja dua kali" |
| 8 | **Keamanan data anak** | Data milik sekolah (UU PDP), akses per peran, catatan BK terenkripsi, backup harian terenkripsi, server atas nama sekolah | Untuk sekolah Islam dengan banyak siswa di bawah umur, poin ini sangat penting |
| 9 | **Rencana bertahap** | Demo (sekarang) → **pilot 1–2 kelas** di semester genap → penyempurnaan dari masukan → **pemakaian penuh di awal tahun ajaran baru** | Jangan menjanjikan tanggal pasti sebelum sekolah setuju |
| 10 | **Kerja sama & serah terima** | Bentuk kerja sama (T9), serah terima ke IT sekolah (panduan, pelatihan, BAST), masa garansi | Isi setelah pemilik produk memutuskan T9 |
| 11 | **Langkah berikutnya** | Ajakan: tunjuk satu **PIC** di sekolah + pilih 1–2 kelas pilot | Akhiri dengan pertanyaan: "Bagian mana yang paling sering bikin repot?" |
| L | **Lampiran teknis (untuk IT sekolah)** | Stack (Laravel 13, PHP 8.4, Kotlin + Compose), arsitektur singkat, CI (Pint, PHPStan, seluruh test), guard test "tanpa angka karangan" dan "satu aturan akses", cara deploy (Docker Compose, satu VPS), backup terenkripsi, dan **utang teknis yang diakui** (`docs/CATATAN_TEKNIS.md`) | Tidak ditayangkan ke pimpinan. Dibuka saat obrolan dengan IT sekolah, atau bila ditanya. Jujur soal utang teknis lebih meyakinkan seorang ahli daripada klaim sempurna |

---

## Yang Perlu Disiapkan Pemilik Produk

- [ ] Keputusan **T9** (jual putus atau dengan garansi/perawatan) dan kisaran harga, untuk slide 10.
- [ ] Izin memakai logo sekolah di slide, atau tanpa logo di versi pertama.
- [ ] Contoh rapor "FALSE" yang **sudah dianonimkan**: ketik ulang satu baris, jangan pakai pindaian rapor asli.
- [ ] Jalur kontak alumni: guru atau Waka yang dikenal, untuk obrolan awal sebelum presentasi resmi.
- [ ] Obrolan awal dengan **IT sekolah**: apakah beliau sendirian atau bertim, sudah terbiasa Laravel/Kotlin atau belum, dan sistem apa yang sedang berjalan di sekolah. Jawabannya menentukan kedalaman alih pengetahuan (FASE 123 D5).
