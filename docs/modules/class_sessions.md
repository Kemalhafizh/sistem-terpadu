# 📚 Sesi Kelas Hidup & Presensi Per-Mapel (FASE 117)

Backend untuk **FASE 77 Android**. Setiap jam pelajaran punya sesi hidup: guru memulai kelas, siswa memindai QR yang berganti setiap 30 detik dari layar guru, dan siswa yang tidak absen otomatis tercatat **alpha** saat sesi ditutup.

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

## 4. Pesan Error yang Dicocokkan Aplikasi

| HTTP | `message` | Kapan |
|---|---|---|
| 422 | `QR token sudah kedaluwarsa` | Token lebih tua dari 1 jendela, atau tanda tangan token salah |
| 403 | `Anda tidak terdaftar di kelas ini` | Siswa bukan anggota kelas sesi |
| 409 | `Anda sudah tercatat hadir di sesi ini` | Scan kedua. `data = { status, checked_in_at }` |
| 409 | `Sesi kelas sudah berakhir` | Sesi completed/auto_closed/cancelled (tidak ada absen retroaktif) |
| 409 | `Sesi kelas belum dimulai` | QR dari sesi yang belum dimulai |
| 422 | `QR tidak valid. Pastikan Anda memindai QR sesi kelas dari layar guru.` | Isi QR bukan QR sesi |
| 422 | `Kelas baru bisa dimulai pukul HH:mm.` / `Waktu jadwal kelas ini sudah lewat.` | Mulai di luar jendela waktu |
| 403 | `Anda tidak dijadwalkan mengajar pada jadwal ini.` | Guru lain mencoba memulai kelas |
| 409 | `Sesi kelas ini sudah dimulai.` | Mulai dua kali. `data = { session_id }` |
| 409 | `Absensi manual hanya bisa dilakukan saat sesi kelas aktif. …` | Absensi manual setelah sesi berakhir |
| 422 | `Presensi berstatus 'hadir' tidak dapat dikoreksi. …` | Koreksi yang akan menghapus kehadiran |

**Aturan koreksi** (77.6.3): `alpha → hadir/sakit/izin`, `sakit/izin → hadir`, `telat → hadir`. Kehadiran yang sudah tercatat tidak bisa diubah menjadi alpha. Setiap koreksi dicatat di `activity_logs` (`session_attendance.override`) beserta alasannya.

---

## 5. Selisih dengan Draf FASE 77 (`android_implementation.md` 77.1.2)

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

Dikirim lewat `FcmNotificationService`, channel `channel_class_session`. Saat ini job FCM di repo masih berupa mock yang hanya menulis log.

| Kapan | Penerima | `data.type` |
|---|---|---|
| Kelas dimulai | Seluruh siswa kelas | `class_session_started` |
| Sesi ditutup (manual/otomatis) | Siswa yang alpha | `class_session_alpha` |
| Sesi auto-close | Guru pengampu | `class_session_auto_closed` |

Belum diimplementasikan: pengingat 5 menit sebelum mulai/selesai dan rangkuman harian orang tua (77.7.1).
