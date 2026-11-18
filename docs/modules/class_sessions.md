# Sesi Kelas Hidup & Presensi Per-Mapel

Backend untuk ** Android**. Setiap jam pelajaran punya sesi hidup: guru memulai kelas, siswa memindai QR yang berganti setiap 30 detik dari layar guru, dan siswa yang tidak absen otomatis tercatat **alpha** saat sesi ditutup.

Semua endpoint berada di bawah `/api/v1`, memakai `Authorization: Bearer <token>`, dan **selalu** membalas dengan envelope `{ success, message, data }`. Ini termasuk error validasi, yang dibalas 422 dengan tambahan `errors: { field: [pesan] }`. Semua key memakai **snake_case**, jadi di Android pakai `@SerializedName`.

---

## 1. Siklus Sesi

```
scheduled ──Mulai Kelas──▶ active ──Akhiri Kelas──▶ completed
                              └──── jam selesai + 5 menit (scheduler) ──▶ auto_closed
```

| Aturan | Nilai default | Config / env |
|---|---|---|
| Kelas bisa dimulai | 10 menit sebelum jam mulai s/d jam selesai | `CLASS_SESSION_START_EARLY_MINUTES` |
| Auto-close | jam selesai + 5 menit (dicek setiap menit) | `CLASS_SESSION_AUTO_CLOSE_GRACE_MINUTES` |
| Scan dianggap `telat` | lebih dari 15 menit setelah jam mulai | `CLASS_SESSION_LATE_AFTER_MINUTES` |
| Rotasi QR | 30 detik; QR 1 jendela sebelumnya masih diterima | `CLASS_SESSION_QR_ROTATION_SECONDS`, `CLASS_SESSION_QR_GRACE_WINDOWS` |
| Draf Jurnal Mengajar otomatis | ya, bila sesi diakhiri dengan topik | `CLASS_SESSION_JOURNAL_DRAFT` |

* Jam mulai dan jam selesai diambil dari **preset jam pelajaran aktif** (`schedule_presets` + `schedule_times`). Jadwal "jam ke-1 s/d 2" berarti mulai di jam ke-1 dan selesai di akhir jam ke-2. Jadwal yang jamnya belum dikonfigurasi tidak ikut muncul di daftar hari ini.
* Satu jadwal hanya punya **satu sesi per tanggal**.
* Saat kelas dimulai, seluruh siswa aktif di kelas tersebut dicatat `alpha` / `auto_alpha`.
* Scheduler dijalankan lewat `php artisan schedule:work` (atau cron `schedule:run`). Command manualnya `php artisan class-sessions:auto-close`.

---

## 2. Endpoint

### Guru — `role: guru, bk` (admin/superadmin juga lolos)

| Method | Path | Keterangan |
|---|---|---|
| GET | `teacher/class-sessions/today` | Sesi hari ini. Aplikasi polling setiap 30 detik; aman dipanggil berulang. |
| POST | `teacher/class-sessions/{scheduleId}/start` | Body `{ topic? }`. Balasan **201**. |
| POST | `teacher/class-sessions/{sessionId}/end` | Body `{ notes?, topic? }` |
| GET | `teacher/class-sessions/{sessionId}/qr` | QR saat ini |
| GET | `teacher/class-sessions/{sessionId}/students` | Daftar hadir real-time (urut nama). Aplikasi polling setiap 10 detik. |
| POST | `teacher/class-sessions/{sessionId}/attendance/manual` | Body `{ student_id, status, notes? }` |
| POST | `teacher/class-sessions/{sessionId}/attendance/bulk` | Body `{ attendances: [{ student_id, status, notes? }] }`, maksimal 100 item |

### Siswa — `role: student, siswa`

| Method | Path | Keterangan |
|---|---|---|
| POST | `student/class-session/scan-qr` | Body `{ qr_token }`. Dibatasi 20 request/menit. |
| GET | `student/class-session/active` | Info untuk banner. `data: null` bila tidak ada sesi aktif. Aplikasi polling setiap 60 detik. |
| GET | `student/class-session/attendance?date=Y-m-d` | Pelajaran siswa pada tanggal itu (bawaan hari ini) dan status presensinya per sesi. Tanggal di masa depan ditolak (422). Sumber widget presensi Android. |

### Orang tua — `role: parent, orang_tua, orangtua, wali_murid`

| Method | Path | Keterangan |
|---|---|---|
| GET | `parent/child/{uuid}/class-session-attendance?date=Y-m-d` | Bentuk sama dengan milik siswa, hanya untuk anak sendiri (403 untuk anak orang lain, 404 bila uuid tidak ada). |

### Waka Kurikulum / TU / Admin — `role: admin, superadmin, waka_kurikulum, staf_tu`

`kepala_sekolah` bisa **membaca** ketiga endpoint GET (daftar, detail, rekap), tetapi tidak bisa mengoreksi (PUT override → 403).

| Method | Path | Keterangan |
|---|---|---|
| GET | `admin/class-sessions` | Query opsional: `date`, `teacher_id`, `classroom_id`, `subject_id`, `status`, `page`, `per_page` (≤100) |
| GET | `admin/class-sessions/{sessionId}/attendances` | Detail presensi satu sesi |
| PUT | `admin/class-sessions/attendances/{attendanceId}/override` | Body `{ status: hadir\|sakit\|izin, reason (≥10 karakter) }` |
| GET | `admin/class-sessions/reports` | Query: `start_date`, `end_date` (wajib, maksimal 366 hari), `classroom_id`, `subject_id`, `group_by` = `student`\|`class`\|`subject` |

---

## 3. Bentuk Data

### `ClassSession`
```json
{
  "id": 45, "uuid": "9b1f0c1e-…", "schedule_id": 12,
  "subject_id": 5, "subject_name": "Matematika Peminatan",
  "classroom_id": 3, "classroom_name": "X-1 (IPA)",
  "teacher_id": 8, "teacher_name": "Pak Hadi",
  "session_date": "2026-10-05", "jam_ke": 1,
  "scheduled_start": "07:00", "scheduled_end": "08:30",
  "startable_from": "06:50", "auto_close_at": "2026-10-05T08:35:00+07:00",
  "actual_start": "2026-10-05T07:05:00+07:00", "actual_end": null,
  "status": "active", "can_start": false,
  "topic": "Limit fungsi aljabar", "notes": null,
  "total_students": 32, "present_count": 25, "absent_count": 6,
  "is_auto_closed": false, "teaching_journal_id": null
}
```
* `present_count` = `hadir` + `telat`. `absent_count` = `alpha`. Siswa berstatus sakit/izin tidak masuk ke kedua hitungan itu.
* `can_start` = status `scheduled` **dan** waktu sekarang berada di jendela mulai. Nilai ini bisa langsung dipakai untuk mengaktifkan tombol "Mulai Kelas".

### `QrToken` (`GET …/qr`)
```json
{ "qr_token": "9b1f0c1e-….4f3c2b1a0e9d8c7b6a5f4e3d2c1b0a99",
  "expires_at": "2026-10-05T07:10:30+07:00", "session_uuid": "9b1f0c1e-…",
  "remaining_seconds": 12, "rotation_seconds": 30 }
```
Isi `qr_token` di-encode **apa adanya** ke QR. Token sama untuk semua perangkat guru selama jendela 30 detik yang sama, lalu diambil ulang saat `remaining_seconds` habis.

### `SessionAttendance`
```json
{ "id": 9001, "class_session_id": 45, "student_id": 301,
  "student_name": "Andi", "student_nis": "NIS-0012",
  "status": "telat", "check_in_method": "qr_scan",
  "checked_in_at": "2026-10-05T07:16:00+07:00", "notes": null,
  "is_override": false, "override_by": null, "override_at": null, "override_reason": null }
```
`status`: `hadir | alpha | sakit | izin | telat` · `check_in_method`: `qr_scan | manual_teacher | auto_alpha`

### Hasil scan (`POST student/class-session/scan-qr`)
```json
{ "success": true, "message": "Presensi berhasil dicatat",
  "status": "hadir", "session_id": 45,
  "session_subject": "Matematika Peminatan", "session_class": "X-1 (IPA)",
  "checked_in_at": "2026-10-05T07:05:00+07:00" }
```

### Sesi aktif siswa (`GET student/class-session/active`)
```json
{ "session_id": 45, "session_uuid": "…", "subject_name": "Matematika Peminatan",
  "classroom_name": "X-1 (IPA)", "teacher_name": "Pak Hadi",
  "scheduled_start": "07:00", "scheduled_end": "08:30",
  "attendance_status": "alpha", "can_scan_qr": true }
```

### Presensi per mapel satu hari (`GET student/class-session/attendance`, `GET parent/child/{uuid}/class-session-attendance`)
```json
{ "date": "2026-10-05",
  "sessions": [
    { "session_id": 45, "subject_name": "Matematika Peminatan", "teacher_name": "Pak Hadi", "jam_ke": 1,
      "scheduled_start": "07:00", "scheduled_end": "08:30", "session_status": "active",
      "status_code": "alpha", "status": "Alpha", "check_in_method": "auto_alpha",
      "checked_in_at": null, "awaiting_scan": true } ],
  "summary": { "sessions": 1, "recorded": 0, "hadir": 0, "telat": 0, "sakit": 0, "izin": 0, "alpha": 0 } }
```
- Isinya sesi kelas siswa pada tanggal itu (kecuali yang `cancelled`), ditambah sesi lain yang mencatat siswa itu (misalnya pindah kelas).
- `status_code` `null` berarti belum tercatat, misalnya sesi masih `scheduled`.
- `awaiting_scan: true` berarti kelas sedang berjalan dan status masih `alpha` otomatis, jadi siswa masih bisa scan untuk jadi `hadir`. Tampilkan sebagai "belum absen", bukan "alpha".
- `summary` hanya menghitung yang sudah tercatat (`recorded`). Hari tanpa sesi berisi `sessions: []` dan semua angka 0.

### Hasil bulk
```json
{ "updated": 2, "failed": 1, "errors": ["Siswa #77: Siswa tidak terdaftar di kelas sesi ini."] }
```

### Paginasi admin (`GET admin/class-sessions`)
```json
{ "items": [ /* ClassSession */ ], "current_page": 1, "last_page": 3, "per_page": 20, "total": 57 }
```

### Rekap (`GET admin/class-sessions/reports`)
```json
{ "summary": { "total_sessions": 12, "total_records": 384, "average_presence_rate": 0.9115,
               "total_hadir": 330, "total_telat": 20, "total_alpha": 20, "total_sakit": 8, "total_izin": 6 },
  "details": [ { "group_id": 301, "student_nis": "NIS-0012", "student_name": "Andi",
                 "classroom_name": "X-1 (IPA)", "subject_name": null,
                 "hadir": 10, "telat": 1, "alpha": 1, "sakit": 0, "izin": 0, "total": 12, "presence_rate": 0.9167 } ] }
```
`presence_rate` = (hadir + telat) / total, dengan rentang 0.0–1.0. Hanya sesi yang benar-benar terlaksana (`active`/`completed`/`auto_closed`) yang dihitung.

---

## 4. Kode Error yang Dibaca Aplikasi

Setiap penolakan membawa `error_code` yang tetap. `message` ditulis dalam bahasa pengguna (`users.preferred_locale`, lalu `Accept-Language`), jadi aplikasi **tidak boleh** mencocokkan teksnya. Teks Indonesia di bawah hanya contoh.

| HTTP | `error_code` | Contoh `message` (id) | Kapan |
|---|---|---|---|
| 422 | `qr_expired` | `QR token sudah kedaluwarsa` | Token lebih tua dari 1 jendela, atau tanda tangan token salah |
| 422 | `qr_invalid` | `QR tidak valid. Pastikan Anda memindai QR sesi kelas dari layar guru.` | Isi QR bukan QR sesi |
| 403 | `not_enrolled` | `Anda tidak terdaftar di kelas ini` | Siswa bukan anggota kelas sesi |
| 409 | `already_checked_in` | `Anda sudah tercatat hadir di sesi ini` | Scan kedua. `data = { status, checked_in_at }` |
| 409 | `session_ended` | `Sesi kelas sudah berakhir.` | Sesi completed/auto_closed/cancelled (tidak ada absen retroaktif) |
| 409 | `session_not_started` | `Sesi kelas belum dimulai.` | QR dari sesi yang belum dimulai |
| 409 | `qr_not_active` | `QR hanya tersedia saat sesi kelas aktif.` | Meminta QR untuk sesi yang tidak aktif |
| 422 | `outside_schedule_window` | `Kelas baru bisa dimulai pukul 06:50.` | Mulai di luar jendela waktu atau di hari lain |
| 403 | `not_your_schedule` | `Anda tidak dijadwalkan mengajar pada jadwal ini.` | Guru lain mencoba memulai atau mengelola kelas |
| 409 | `session_already_started` | `Sesi kelas ini sudah dimulai.` | Mulai dua kali. `data = { session_id }` |
| 409 | `session_already_ended` | `Sesi kelas ini sudah berakhir hari ini.` | Mulai lagi setelah selesai. `data = { session_id }` |
| 409 | `manual_not_active` | `Absensi manual hanya bisa dilakukan saat sesi kelas aktif. …` | Absensi manual setelah sesi berakhir |
| 422 | `transition_not_allowed` | `Presensi berstatus Hadir tidak dapat dikoreksi. …` | Koreksi yang akan menghapus kehadiran |
| 422 | `status_unchanged` | `Status presensi sudah Hadir.` | Koreksi ke status yang sama |
| 404 | `schedule_not_found`, `session_not_found`, `attendance_not_found`, `student_not_found`, `student_not_in_session` | | Data tidak ada |
| 422 | `schedule_not_current_year`, `lesson_hours_missing`, `unknown_status` | | Data jadwal atau permintaan tidak lengkap |
| 409 | `session_not_held` | | Koreksi untuk sesi yang tidak dilaksanakan |

`tests/Feature/Academic/ClassSessionTest.php` memastikan setiap `ClassSessionException` membawa kode.

**Aturan koreksi** (77.6.3): `alpha → hadir/sakit/izin`, `sakit/izin → hadir`, `telat → hadir`. Kehadiran yang sudah tercatat tidak bisa diubah menjadi alpha. Setiap koreksi dicatat di `activity_logs` (`session_attendance.override`) beserta alasannya.

---

## 5. Selisih dengan Draf (`android_implementation.md` 77.1.2)

| Draf Android | Backend | Tindakan di Android |
|---|---|---|
| Field camelCase tanpa anotasi | snake_case | Tambahkan `@SerializedName("subject_name")` dan seterusnya |
| `isAutoClose` | `is_auto_closed` | Sesuaikan nama field |
| `ActiveSessionInfo` | + `session_uuid`, `scheduled_start`, `attendance_status` | Opsional dipakai |
| `AttendanceSummary` / `AttendanceDetailRow` | + `total_telat`, `total_records`, `telat`, `total`, `group_id`, `student_nis` | Opsional dipakai |
| `PaginatedResponse<T>` | `{ items, current_page, last_page, per_page, total }` | Definisikan dengan bentuk ini |
| `EndSessionRequest(notes)` | Juga menerima `topic` | Kirim `topic` dari dialog "Akhiri Kelas" |
| QR: "hash disimpan di DB, rotasi lazy" | Token HMAC per jendela 30 detik, dengan secret per sesi | Tidak ada perubahan (tetap encode `qr_token`) |

## 6. Notifikasi Push

Dikirim lewat FCM HTTP v1 (`SendFcmNotificationJob` + `FcmHttpV1Client`) ke semua token aktif pengguna, yaitu token yang didaftarkan aplikasi lewat `POST mobile/devices`. Semua notifikasi sesi kelas memakai channel Android **`attendance_alerts`**.

**Isi pesan.** Pesan FCM berisi `notification { title, body }` dan `data`. `data` selalu memuat `title`, `body`, dan `channel`, ditambah key di tabel bawah. Semua nilai `data` berupa string.

| Kapan | Penerima | `data.type` | Data tambahan |
|---|---|---|---|
| 5 menit sebelum jadwal mulai | Guru pengampu | `class_session_start_reminder` | `session_id`, `schedule_id` |
| Kelas dimulai | Seluruh siswa kelas | `class_session_started` | `session_id` |
| 5 menit sebelum jadwal selesai (sesi aktif) | Guru pengampu | `class_session_end_reminder` | `session_id` |
| Sesi ditutup (manual/otomatis) | Siswa yang alpha | `class_session_alpha` | `session_id` |
| Sesi auto-close | Guru pengampu | `class_session_auto_closed` | `session_id` |
| Setiap hari pukul 17:00 | Orang tua (`students.parent_id`), satu pesan per anak | `class_session_daily_digest` | `student_id`, `date` |

**Contoh isi.**
- Pengingat mulai: "Sesi Matematika X-1 (IPA) dimulai 5 menit lagi" / "Tap untuk memulai kelas."
- Pengingat selesai: "… berakhir 5 menit lagi" / "6 siswa belum absen."
- Rangkuman orang tua: "Kehadiran Andi hari ini" / "6/7 sesi hadir. Alpha: Matematika (Jam ke-3). Terlambat: 1. Sakit/izin: 1."
- Rangkuman hanya menghitung sesi yang benar-benar terlaksana. Anak tanpa sesi hari itu tidak dikirimi.

**Jadwal & pengaturan.**
- `class-sessions:remind` berjalan setiap menit. Tiap pengingat terkirim sekali, dikunci lewat kolom `start_reminder_sent_at` / `end_reminder_sent_at`.
- `class-sessions:parent-digest` berjalan sekali sehari. Opsi `--date=Y-m-d` untuk tanggal lain, `--force` untuk kirim ulang.
- Env: `CLASS_SESSION_REMINDER_LEAD_MINUTES` (default 5) dan `CLASS_SESSION_PARENT_DIGEST_TIME` (default `17:00`).

**Pengaktifan FCM.**
- Isi `FCM_CREDENTIALS` dengan path file JSON service account dari Firebase Console → Project settings → Service accounts. Isi JSON-nya langsung juga bisa.
- `FCM_PROJECT_ID` opsional; default-nya diambil dari file tersebut.
- Tanpa kredensial, push hanya dicatat di log.
- Token yang ditolak FCM sebagai tidak terdaftar otomatis dinonaktifkan. Error 429/5xx diulang (maksimal 3 percobaan) hanya untuk perangkat yang gagal.
