# 🔌 REST API v1 Mobile-Ready SISTA

SISTA menyediakan antarmuka pemrograman aplikasi (REST API v1) yang lengkap, aman, terstandardisasi, dan terdokumentasi untuk mendukung integrasi aplikasi mobile (Siswa, Orang Tua, dan Guru).

---

## 🔒 1. Mekanisme Otentikasi & Keamanan

### A. Laravel Sanctum Token
Seluruh permintaan API yang terproteksi wajib menyertakan token akses di dalam Header HTTP:
```http
Authorization: Bearer {YOUR_ACCESS_TOKEN}
Accept: application/json
```
*Catatan:* Header `Accept: application/json` wajib disertakan agar penolakan kesalahan (*validation errors* atau *unauthorized exceptions*) terlempar sebagai JSON (bukan HTML redirect).

### B. Rotasi Token
Setiap kali pengguna melakukan login dari perangkat mobile dengan parameter `device_name` yang sama, sistem akan menghapus token lama dengan nama perangkat tersebut dari database sebelum menerbitkan token baru. Ini mencegah penumpukan token sesi tidak aktif di database.

### C. Rate Limiting Ketat
Untuk mencegah penyalahgunaan API, pembatasan ketat diterapkan di tingkat middleware:
*   **Rute Otentikasi (`/login`):** Dibatasi maksimal 5 kali percobaan per menit.
*   **Rute API Umum:** Dibatasi maksimal 60 kali permintaan per menit per akun/IP.

---

## ✉️ 2. Standar Struktur Respon JSON

SISTA mengembalikan struktur data amplop (*envelope*) JSON yang konsisten:

### Respon Sukses (HTTP 200 OK)
```json
{
    "success": true,
    "message": "Data jadwal berhasil diambil",
    "data": [
        {
            "day": "Senin",
            "subject": "Fisika",
            "code": "FIS-X",
            "start_time": "07:30",
            "end_time": "09:00",
            "teacher": "Budi Santoso, S.Pd",
            "classroom": "X-MIPA-1"
        }
    ]
}
```

### Respon Gagal Validasi (HTTP 422 Unprocessable Entity)
```json
{
    "success": false,
    "message": "Validasi data gagal",
    "errors": {
        "device_name": [
            "Parameter nama perangkat wajib diisi."
        ]
    }
}
```

---

## 🛣️ 3. Daftar Endpoint Inti API v1

### A. Otentikasi Publik
*   `POST /api/v1/login`
    *   **Deskripsi:** Masuk ke sistem dan terbitkan token Sanctum.
    *   **Body:** `email` (string, email), `password` (string), `device_name` (string).
*   `POST /api/v1/logout` (Auth Required)
    *   **Deskripsi:** Cabut token akses aktif saat ini dari database.
*   `GET /api/v1/me` (Auth Required)
    *   **Deskripsi:** Mengambil ringkasan profil akun pengguna aktif beserta role.

### B. Fitur Khusus Siswa (`/api/v1/student/*`)
Semua endpoint ini dilindungi middleware `role:student`:
*   `GET /api/v1/student/profile` : Profil akademis siswa.
*   `GET /api/v1/student/schedule` : Jadwal pelajaran siswa pada tahun akademik aktif.
*   `GET /api/v1/student/grades` : Transkrip nilai tugas, PTS, dan PAS siswa.
*   `GET /api/v1/student/attendance` : Rekap absensi kehadiran harian (Hadir/Sakit/Izin/Alpa).
*   `GET /api/v1/student/billings` : Daftar tagihan SPP dan status keuangannya.
*   `GET /api/v1/student/points` : Catatan pelanggaran BK dan prestasi.
*   `GET /api/v1/student/notifications` : Daftar notifikasi masuk khusus siswa.

### C. Fitur Khusus Orang Tua (`/api/v1/parent/*`)
Semua endpoint ini dilindungi middleware `role:parent` dengan validasi otorisasi relasi anak-orangtua:
*   `GET /api/v1/parent/children` : Daftar seluruh anak (siswa) yang terikat dengan akun ortu.
*   `GET /api/v1/parent/child/{studentUuid}/summary` : Ringkasan performa anak (kehadiran, rata-rata nilai, tagihan aktif).
*   `GET /api/v1/parent/messages` : Riwayat pesan obrolan dengan wali kelas/guru.
*   `POST /api/v1/parent/messages` : Kirim pesan baru ke guru (memicu real-time websocket broadcast).

### D. Fitur Khusus Guru (`/api/v1/teacher/*`)
Semua endpoint ini dilindungi middleware `role:guru,bk`:
*   `GET /api/v1/teacher/schedule` : Jadwal mengajar guru.
*   `GET /api/v1/teacher/classes` : Daftar kelas yang diampu guru.
*   `POST /api/v1/teacher/attendance` : Input absensi harian kelas secara massal (*bulk*).
*   `POST /api/v1/teacher/journals` : Entri jurnal mengajar baru harian kelas.