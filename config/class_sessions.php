<?php

/*
|--------------------------------------------------------------------------
| FASE 117 — Sesi Kelas Hidup & Presensi Per-Mapel
|--------------------------------------------------------------------------
|
| Time rules for starting/closing a class session and for the rotating QR
| students scan. All values are in the application timezone (Asia/Jakarta).
|
*/

return [

    // A teacher may press "Mulai Kelas" from this many minutes before the
    // scheduled start until the scheduled end.
    'start_early_minutes' => (int) env('CLASS_SESSION_START_EARLY_MINUTES', 10),

    // An active session is auto-closed this many minutes after its scheduled end.
    'auto_close_grace_minutes' => (int) env('CLASS_SESSION_AUTO_CLOSE_GRACE_MINUTES', 5),

    // A QR scan later than scheduled start + this many minutes is recorded as "telat".
    'late_after_minutes' => (int) env('CLASS_SESSION_LATE_AFTER_MINUTES', 15),

    // The QR token changes every this many seconds.
    'qr_rotation_seconds' => (int) env('CLASS_SESSION_QR_ROTATION_SECONDS', 30),

    // How many previous QR windows are still accepted, so a scan that is in
    // flight while the teacher's screen rotates does not fail.
    'qr_grace_windows' => (int) env('CLASS_SESSION_QR_GRACE_WINDOWS', 1),

    // When a session ends with a topic, create a draft TeachingJournal for it.
    'create_teaching_journal_draft' => (bool) env('CLASS_SESSION_JOURNAL_DRAFT', true),

    // Teachers get a push this many minutes before a lesson starts and before
    // an active lesson ends (class-sessions:remind, every minute).
    'reminder_lead_minutes' => (int) env('CLASS_SESSION_REMINDER_LEAD_MINUTES', 5),

    // Daily per-child attendance summary pushed to parents (class-sessions:parent-digest).
    'parent_digest_time' => env('CLASS_SESSION_PARENT_DIGEST_TIME', '17:00'),

];
