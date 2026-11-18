# Kebijakan Keamanan Sistem SISTA

Dokumen ini menjelaskan mekanisme autentikasi, otorisasi, perlindungan data sensitif, dan pencatatan audit pada **SISTA**.

---

## 1. Autentikasi dan Manajemen Sesi

### A. Web & Two-Factor Authentication (2FA)
- Autentikasi sesi web dilengkapi perlindungan pembatasan percobaan login (*rate limiting*) dan pemeriksaan risiko login (`AdaptiveRiskEngine`).
- Pengguna dapat mengaktifkan autentikasi dua faktor (2FA) berbasis OTP untuk mengamankan akses akun.

### B. REST API (Laravel Sanctum)
- Aplikasi Android berkomunikasi menggunakan Bearer Token Laravel Sanctum (`/api/v1/login`).
- Saat pengguna login ulang menggunakan `device_name` yang sama, token lama untuk perangkat tersebut dicabut agar tidak terjadi penumpukan token aktif.

### C. Pelacakan Sesi Aktif (`ActiveSession`)
- Setiap sesi perangkat dicatat pada tabel `active_sessions` dan diperbarui melalui middleware `UpdateActiveSession`.
- Administrator dapat mencabut sesi perangkat yang mencurigakan sehingga pengguna pada sesi tersebut langsung dikeluarkan pada permintaan berikutnya.

---

## 2. Otorisasi dan Isolasi Data (Anti-IDOR)

- **Fail-Closed Route Access:** Setiap route web yang memerlukan login wajib memiliki aturan `role:` atau `can:`. Route tanpa aturan yang tidak terdaftar di `config/route_access.php` ditolak secara bawaan untuk seluruh peran selain `superadmin`.
- **Isolasi Data Siswa & Keluarga:** Melalui `Student::visibleTo($user)` dan `StudentDataAccess::authorizeStudentRecord()`, orang tua hanya dapat mengakses data anaknya sendiri, siswa hanya datanya sendiri, dan guru hanya siswa pada kelas yang diajar atau diwalikannya pada tahun ajaran berjalan.

---

## 3. Enkripsi Data Sensitif dan Audit Log

- **Enkripsi Catatan Konseling BK:** Catatan permasalahan dan hasil konseling siswa serta rincian laporan perundungan disimpan dalam keadaan terenkripsi menggunakan AES-256 (`Crypt::encryptString` / cast `encrypted`) di database.
- **Audit Log & Rantai Integritas:** Perubahan data penting, aktivitas login (`login_attempts`), serta mutasi nilai dan transaksi keuangan dicatat pada tabel `audit_logs` dan `immutable_audit_chains` (`AuditLogChainService`).
- **Content Security Policy (CSP):** Header keamanan HTTP dan kebijakan CSP diterapkan melalui `SecurityHeadersMiddleware`.