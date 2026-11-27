# Dokumentasi REST API v1 SISTA

SISTA menyediakan endpoint REST API (`/api/v1`) untuk melayani aplikasi Android **SISTA (`sista-android`)** bagi siswa, orang tua, guru, konselor BK, petugas UKS, dan pimpinan.

---

## 1. Autentikasi dan Header

### A. Token Laravel Sanctum
Permintaan ke endpoint terproteksi wajib menyertakan header berikut:
```http
Authorization: Bearer {ACCESS_TOKEN}
Accept: application/json
Accept-Language: id
```

### B. Rotasi Token Perangkat
Saat login melalui `POST /api/v1/login` dengan parameter `device_name`, token lama pada perangkat yang sama dicabut secara otomatis sebelum token baru diterbitkan.

### C. Pembatasan Permintaan (Rate Limiting)
- Endpoint autentikasi (`/api/v1/login`): maksimal 5 percobaan per menit.
- Endpoint API terautentikasi: maksimal 60 permintaan per menit per akun/IP.

---

## 2. Format Respons JSON

Seluruh endpoint utama menggunakan format amplop JSON standar (`ApiResponseTrait`):

### Respons Berhasil (HTTP 200 / 201)
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
            "classroom": "X-1"
        }
    ]
}
```

### Respons Gagal Validasi (HTTP 422)
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

Catatan konvensi data:
- Nilai yang belum tersedia dikirimkan sebagai `null`, bukan angka karangan.
- Waktu dikirimkan dalam format ISO-8601 (`Asia/Jakarta`).
- Nilai status atau kategori dilengkapi kode stabil (`status_code`, `category_code`) di samping label terjemahan.

---

## 3. Ringkasan Kelompok Endpoint (`/api/v1`)

### A. Autentikasi, Profil & Konfigurasi Sesi
- `POST /api/v1/login`: Autentikasi pengguna dan penerbitan token Sanctum.
- `POST /api/v1/logout`: Pencabutan token sesi aktif.
- `GET /api/v1/me`: Ringkasan profil pengguna yang sedang login.
- `PUT /api/v1/me/locale`: Pembaruan bahasa pilihan pengguna (`id`, `en`, `ar`).
- `GET /api/v1/mobile/config`: Konfigurasi aplikasi dan daftar kapabilitas fitur (`capabilities`) sesuai peran pengguna.

### B. Sesi Kelas & Presensi QR per Mata Pelajaran
Kontrak lengkap tersedia di [`docs/modules/class_sessions.md`](modules/class_sessions.md).
- `GET /api/v1/teacher/class-sessions/today`: Daftar sesi mengajar guru hari ini.
- `POST /api/v1/teacher/class-sessions/{scheduleId}/start` & `POST .../end`: Memulai dan mengakhiri sesi kelas.
- `GET /api/v1/teacher/class-sessions/{sessionId}/qr`: Token QR berputar setiap 30 detik.
- `GET /api/v1/teacher/class-sessions/{sessionId}/students` & `POST .../attendance/manual`: Daftar hadir dan koreksi manual guru.
- `POST /api/v1/student/class-session/scan-qr` & `GET /api/v1/student/class-session/active`: Pemindaian QR oleh siswa dan sesi aktif.
- `GET /api/v1/student/class-session/attendance` & `GET /api/v1/parent/child/{uuid}/class-session-attendance`: Ringkasan kehadiran per mata pelajaran hari ini untuk siswa, orang tua, dan widget Android.

### C. Fitur Siswa (`/api/v1/student/*`)
- `GET /api/v1/student/profile`: Profil akademik siswa.
- `GET /api/v1/student/schedule`: Jadwal pelajaran pada tahun ajaran aktif.
- `GET /api/v1/student/grades`: Daftar nilai akademik.
- `GET /api/v1/student/remedials`: Daftar jadwal dan nilai remedial siswa.
- `GET /api/v1/student/billings`: Daftar tagihan SPP/biaya pendidikan dan riwayat pembayaran.
- `GET /api/v1/student/points`: Catatan poin kedisiplinan, prestasi, surat peringatan resmi (`warning_letters`), dan ambang batas dinamis sekolah (`thresholds`).

### D. Fitur Orang Tua (`/api/v1/parent/*`)
Seluruh endpoint memvalidasi kepemilikan relasi orang tua-anak (`StudentDataAccess`):
- `GET /api/v1/parent/children`: Daftar anak yang terhubung dengan akun orang tua.
- `GET /api/v1/parent/child/{studentUuid}/summary`: Ringkasan perkembangan akademik, kehadiran, dan tagihan anak.
- `GET /api/v1/parent/child/{studentUuid}/points`: Riwayat poin kedisiplinan anak, daftar SP resmi, dan ambang dinamis sekolah (`thresholds`).
- `GET /api/v1/parent/leave-requests` & `POST /api/v1/parent/leave-requests`: Pengajuan dan pemantauan izin/sakit anak (`C3`), termasuk persetujuan otomatis sakit 1 hari dan pembatalan (`POST .../cancel`).
- `GET /api/v1/parent/messages` & `POST /api/v1/parent/messages`: Obrolan dengan wali kelas.

### E. Fitur Guru & Wali Kelas (`/api/v1/teacher/*`)
- `GET /api/v1/teacher/schedule` & `GET /api/v1/teacher/classes`: Jadwal mengajar dan daftar kelas yang diampu.
- `GET /api/v1/teacher/journals` & `POST /api/v1/teacher/journals`: Jurnal mengajar harian.
- `GET /api/v1/teacher/daily-assessments` & `POST .../scores`: Penilaian harian dan input nilai.
- `GET /api/v1/teacher/cbt/exams`: Pengawasan ujian CBT dan pembukaan kunci peserta.
- `GET /api/v1/teacher/leave-requests` & `POST .../decide`: Tinjauan izin/sakit siswa oleh wali kelas atau eskalasi Waka Kesiswaan (`approve`, `reject`, `revoke`).
- `GET /api/v1/student-leave-requests/{leaveRequest}/attachment`: Unduhan lampiran surat izin/dokter privat terproteksi Anti-IDOR.

### F. Rapor Resmi, Projek P5, Perubahan Identitas, Sarpras & Kotak Masuk Persetujuan
- `GET /api/v1/rapor/official` & `GET /api/v1/rapor/official/{publication}/download`: Daftar dan unduhan PDF rapor semester resmi (`D5`) yang telah diterbitkan.
- `GET /api/v1/p5/report`: Rapor Projek Penguatan Profil Pelajar Pancasila (`D4`) beserta fase (`E`/`F`), catatan proses, dan predikat dimensi (`MB`, `SB`, `BSH`, `SAB`, atau `null` bila belum dinilai).
- `GET /api/v1/profile-change-requests`, `POST /api/v1/profile-change-requests`, `POST .../cancel`, & `GET .../evidence`: Pengajuan perubahan data identitas terverifikasi dengan bukti dokumen privat (`K4`).
- `GET /api/v1/assets/scan/{asset}`, `POST /api/v1/assets/scan/{asset}/report`, & `GET /api/v1/assets/damage-reports/my`: Pemindaian stiker QR inventaris aset dan pelaporan foto kerusakan terpadu (`I3`).
- `GET /api/v1/approvals/inbox` & `POST /api/v1/approvals/{module}/{id}/{action}`: Kotak masuk persetujuan terpadu lintas modul (`approve`, `reject`, `revision`) bagi pimpinan dan pejabat aktif.

### G. Bimbingan Konseling, UKS & Keislaman
- `POST /api/v1/counseling/bullying-reports`: Pelaporan perundungan (termasuk opsi anonim).
- `GET /api/v1/counseling/sessions` & `POST /api/v1/counseling/sessions`: Sesi konseling BK.
- `GET /api/v1/uks/visits` & `POST /api/v1/uks/visits`: Riwayat dan pencatatan kunjungan UKS.
- `GET /api/v1/mutabaah/*` & `GET /api/v1/tahsin/*`: Pencatatan ibadah harian dan setoran Tahsin.
