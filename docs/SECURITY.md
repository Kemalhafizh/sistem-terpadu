# 🛡️ Kebijakan Keamanan & SIEM SISTA

Keamanan data adalah prioritas utama di **SISTA**. Dokumen ini merangkum lapisan proteksi enkripsi, otentikasi multi-faktor, kebijakan isolasi multi-tenant, serta sistem deteksi dini keamanan terintegrasi (SIEM).

---

## 🔒 1. Otentikasi & Otorisasi Ketat

### A. Two-Factor Authentication (2FA)
SISTA mendukung pengamanan autentikasi ganda menggunakan One-Time Password (OTP) yang dikirim ke email terdaftar sesaat setelah pengguna memasukkan kata sandi utama. Kode OTP hanya valid selama 5 menit.

### B. Enkripsi Berkas BK & Konseling
Konseling BK bersifat sensitif dan dilindungi undang-undang privasi. 
*   **Implementasi:** Setiap deskripsi permasalahan siswa dan solusi konseling yang diunggah oleh guru BK disimpan di database menggunakan enkripsi **AES-256-CBC** (`Crypt::encryptString`).
*   **Keamanan:** Data mentah di database tidak dapat dibaca secara langsung oleh Administrator Database (DBA) pusat sekalipun tanpa kunci dekripsi `APP_KEY` aplikasi.

---

## 🚨 2. SIEM Ringan (Sistem Keamanan & Audit Terintegrasi)

SISTA dilengkapi dengan sistem monitoring keamanan mandiri untuk mendeteksi ancaman secara proaktif:

### A. Pencatatan Audit Trail Login
Setiap aktivitas login sukses, kegagalan login (salah password), dan tindakan logout dicatat di tabel `login_attempts` beserta data alamat IP, tipe peramban (*user agent*), dan alasan kegagalan.

### B. Deteksi Serangan Brute-Force
*   **Metode:** Service `SecurityMonitorService::detectBruteForce()` memantau log kegagalan login.
*   **Threshold:** Jika terdeteksi lebih dari atau sama dengan **5 kegagalan login** dari satu alamat IP yang sama dalam kurun waktu **10 menit**, sistem akan langsung menerbitkan tanda bahaya (*Critical Security Alert*) di dashboard Super-Admin.

### C. Pelacakan & Terminasi Sesi Aktif (Remote Force Logout)
Sistem melacak setiap sesi aktif perangkat pengguna di tabel `active_sessions`. 
*   **Remote Termination:** Admin memiliki kuasa penuh untuk menekan tombol **"Putuskan Sesi"** dari dashboard keamanan untuk memutuskan koneksi perangkat mencurigakan.
*   **Middleware Action:** Melalui middleware `UpdateActiveSession`, setiap request halaman web divalidasi keberadaannya di tabel sesi aktif. Jika sesi telah dihapus oleh admin, pengguna akan langsung didepak keluar (*forced logout*), sesi dihancurkan, dan diarahkan kembali ke layar login dengan pesan peringatan keamanan.