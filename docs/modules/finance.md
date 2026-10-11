# 💰 Modul Keuangan & SPP SISTA

Modul Keuangan mengelola seluruh transaksi pembayaran sekolah, penagihan SPP siswa, serta pembukuan jurnal akuntansi keuangan di **SISTA**.

---

## 💸 1. Tagihan Keuangan Siswa (SPP & Uang Pangkal)

### A. Mass Billing Generator (`FinanceController`)
*   Staf keuangan dapat membuat tagihan bulanan (SPP) secara massal untuk seluruh siswa di tingkat kelas tertentu dengan satu klik.
*   Tagihan mencatat jenis pembayaran, nominal dasar, batas jatuh tempo pengerjaan, dan status pembayaran (`pending`, `paid`, atau `partially_paid`).

### Uang Pangkal siswa baru (SPMB)
*   Saat pendaftar SPMB dikonversi menjadi siswa, tagihan uang pangkal diterbitkan dari kategori pembayaran non-bulanan bernama **"Uang Pangkal…"** dengan nominal yang diatur Keuangan.
*   Bila kategori itu belum ada atau nominalnya 0, tagihan **tidak** dibuat dan admin diberi tahu. Tidak ada nominal bawaan (dulu Rp 5.000.000 karangan).

### B. Dasbor Keuangan Siswa (`StudentFinanceController`)
*   Siswa dan orang tua dapat melihat daftar riwayat pembayaran masa lalu serta tagihan aktif yang harus segera diselesaikan.

---

## 💳 2. Integrasi Payment Gateway Midtrans & Akuntansi Ledger

### A. Otomasi Pembayaran Online (Midtrans)
*   Siswa/orang tua dapat melakukan pembayaran tagihan SPP online secara aman menggunakan Virtual Account bank mandiri, BNI, BRI, Permata, atau kode QRIS.
*   Sistem menangani webhook callback dari Midtrans secara real-time untuk memproses transaksi sukses atau membatalkan tagihan kedaluwarsa secara otomatis.

### B. Sistem Akuntansi Double-Entry (Buku Besar Otomatis)
Setiap kali pembayaran diverifikasi lunas (baik secara tunai di kasir sekolah maupun via online Midtrans), sistem secara otomatis mencatat entri jurnal akuntansi berpasangan:
*   **Debit:** Akun Kas Sekolah (`101-Kas`).
*   **Kredit:** Akun Pendapatan SPP (`401-Pendapatan-SPP`).

Laporan neraca saldo dan buku besar pembukuan sekolah dapat diunduh secara landscape oleh staf keuangan untuk keperluan kepatuhan audit.
---

## 📊 3. RKAS / RAPBS (`BudgetPlanningController`)

Menu **Keuangan → RKAS / Anggaran**.
*   **Alur:** TU atau bendahara menyusun RKAS dan pos anggarannya (kategori, kode akun, nominal rencana), lalu mengajukannya. Kepala Sekolah menyetujui. Penyusun tidak bisa menyetujui RKAS buatannya sendiri, dan pos tidak bisa diubah setelah diajukan.
*   **Tahun anggaran:** `2026/2027` berarti tahun ajaran (1 Juli – 30 Juni), `2026` berarti tahun kalender.
*   **Realisasi:** pos yang kode akunnya ada di bagan akun dihitung otomatis dari buku besar dalam tahun anggaran itu (pendapatan = kredit − debit, belanja = debit − kredit). Pos lain dicatat manual oleh TU. Selama belum ada data, realisasi dan persentasenya tampil "–", bukan 0%.
*   Tidak ada proyeksi tahun depan: setiap proyeksi butuh asumsi pertumbuhan, dan sebelumnya angka 5% dipakai tanpa dasar.
