# 🏆 Modul Student e-Portfolio SISTA

Modul e-Portfolio Siswa adalah showcase prestasi digital interaktif berdesain glassmorphism premium yang mengumpulkan pencapaian akademis, keikutsertaan organisasi/ekstrakurikuler, sertifikat kompetensi, serta portofolio karya mandiri siswa SMA Islam Sultan Agung 1 Semarang.

---

## 📚 1. Komponen Utama & Database Schema

Penyusunan e-Portfolio ditopang oleh relasi database terverifikasi berikut:

### A. Item Portofolio (`PortfolioItem`)
*   Menyimpan entri karya, organisasi, atau sertifikat siswa.
*   `student_id`: Relasi ke tabel `students`.
*   `title`: Nama pencapaian atau kegiatan (contoh: "Juara 1 Lomba Debat Bahasa Inggris").
*   `category`: Kategori entri (`akademik`, `non_akademik`, `organisasi`, `sertifikasi`, `karya`).
*   `description`: Narasi deskripsi detail kegiatan.
*   `date_achieved`: Tanggal perolehan atau durasi aktif organisasi.
*   `attachment_path`: Bukti fisik (PDF sertifikat/foto trofi) yang diunggah.
*   `is_verified`: Flag boolean penanda keaslian yang hanya bisa diubah oleh guru (`true`/`false`).
*   `verified_by`: ID Guru yang memverifikasi.

### B. Lencana Prestasi (`PortfolioBadge`)
*   Lencana digital penghargaan resmi dari sekolah kepada siswa.
*   `badge_name`: Judul lencana (contoh: "Siswa Berprestasi Akademik", "Hafiz Cilik", "Teladan Kehadiran").
*   `badge_icon`: Ikon lencana visual (ikon SVG atau file PNG lencana khusus).
*   `description`: Alasan penganugerahan lencana.

---

## 🔒 2. Fitur Unggulan & Verifikasi Sekolah

e-Portfolio dirancang sebagai kredensial akademik terpercaya yang aman untuk disebarkan secara eksternal:

```mermaid
graph TD
    A[Siswa: Tambah Sertifikat/Prestasi] -->|Upload Bukti & Simpan| B[PortfolioItem: Status Unverified]
    B -->|Tampil di Panel Review Guru| C[Guru BK / Wali Kelas]
    C -->|Periksa Bukti Fisik & Approve| D[PortfolioItem: Status Verified]

    E[Kepala Sekolah / Guru BK] -->|Berikan Lencana Khusus| F[PortfolioBadge Awarded]
    D & F -->|Rerender di Halaman Glassmorphism| G[Public Portfolio Showcase Page]
    G -->|Generate Link| H[Shareable Public UUID Link: /portfolio/shared/{uuid}]
```

### A. Fitur Utama Showcase:
1.  **Glassmorphic Public Pages:** Halaman profil publik yang modern dengan efek blur kaca transparan, transisi dinamis, serta layout responsif yang dioptimasi untuk perangkat seluler.
2.  **Verified School Badges:** Setiap item yang telah divalidasi oleh pihak sekolah akan memunculkan lencana centang emas terverifikasi (*verified check*) sebagai segel keaslian.
3.  **Shareable Public UUID Links:** Menggunakan UUID acak alih-alih ID berurutan dalam route URL (contoh: `/portfolio/shared/2c1b1836-de6a-493e-af4c-fa7093259e89`). Ini memungkinkan siswa menyematkan link portofolio resmi mereka pada CV lamaran magang atau pendaftaran Perguruan Tinggi Negeri (PTN) tanpa perlu hak login akun ke sistem SISTA.

### B. Alur Verifikasi Guru BK & Wali Kelas:
*   Setiap kali siswa menambahkan entri baru, entri tersebut masuk ke daftar peninjauan (*review list*) di portal Guru BK/Wali Kelas masing-masing.
*   Guru meninjau dokumen bukti fisik yang diunggah. Jika sesuai, guru memberikan status **Verified** yang secara instan akan mengunci entri agar tidak dapat diedit lagi oleh siswa.

---

## 📝 3. Pest Feature Test Scenarios

Validasi keamanan akses publik dan logika verifikasi diuji oleh Pest PHP pada [PortfolioTest.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/tests/Feature/Portfolio/PortfolioTest.php):

*   **`siswa dapat menambahkan entri portofolio baru`**
    *   Memastikan siswa dapat mengunggah bukti PDF sertifikat dan membuat record `PortfolioItem` dengan status default `is_verified` bernilai `false`.
*   **`siswa tidak dapat memverifikasi portofolionya sendiri`**
    *   Menguji pengiriman payload manipulasi `is_verified => true` oleh akun siswa dan memastikan sistem mengabaikannya atau melempar validasi error.
*   **`wali kelas atau guru BK dapat memverifikasi portofolio siswa`**
    *   Melakukan autentikasi sebagai wali kelas dan memverifikasi endpoint update status `is_verified` berubah menjadi `true` serta merekam `verified_by`.
*   **`halaman portofolio publik dapat diakses via UUID tanpa login`**
    *   Membuat request tamu (*guest request*) ke URL shared portofolio menggunakan `uuid` siswa dan menegaskan respons `200 OK` merender nama siswa serta daftar sertifikat yang telah terverifikasi.
*   **`halaman portofolio publik tidak menampilkan data sensitif`**
    *   Memastikan informasi login user, password hash, alamat detail rumah, atau nomor HP tidak terender dalam response HTML publik demi privasi siswa.
