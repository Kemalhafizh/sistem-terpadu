<?php

/*
|--------------------------------------------------------------------------
| Mobile feature registry
|--------------------------------------------------------------------------
|
| Every feature the Android app can show, keyed by a stable id the app maps
| to a screen. A feature carries NO role list of its own: `endpoint` names
| the API call the feature cannot work without, and whoever that route's
| middleware lets through has the feature. Menus therefore can never offer
| something the server will refuse, and changing who may use a feature is a
| change to routes/api.php only.
|
| `endpoint` is "METHOD uri" relative to api/v1. `hardware` lists the phone
| hardware the feature needs, so the app can explain a missing sensor or
| permission instead of failing.
|
*/

return [
    'groups' => [
        'akademik' => 'Akademik',
        'presensi' => 'Presensi',
        'ibadah' => 'Ibadah',
        'keuangan' => 'Keuangan',
        'kesiswaan' => 'Kesiswaan',
        'mengajar' => 'Mengajar',
        'keluarga' => 'Anak Saya',
        'manajemen' => 'Manajemen',
        'layanan' => 'Layanan',
    ],

    'features' => [
        // ── Akademik (siswa) ─────────────────────────────────────────────
        'student.schedule' => ['title' => 'Jadwal Pelajaran', 'group' => 'akademik', 'endpoint' => 'GET student/schedule'],
        'student.grades' => ['title' => 'Nilai', 'group' => 'akademik', 'endpoint' => 'GET student/grades'],
        'student.rapor' => ['title' => 'E-Rapor', 'group' => 'akademik', 'endpoint' => 'GET rapor/current'],
        'student.remedials' => ['title' => 'Remedial', 'group' => 'akademik', 'endpoint' => 'GET assessments/student/remedials'],
        'student.cbt' => ['title' => 'Ujian CBT', 'group' => 'akademik', 'endpoint' => 'GET student/cbt/exams'],
        'student.elearning' => ['title' => 'Kelas Online', 'group' => 'akademik', 'endpoint' => 'GET elearning/classes/student'],
        'student.analytics' => ['title' => 'Analitik Belajar', 'group' => 'akademik', 'endpoint' => 'GET analytics/student/summary'],
        'student.utbk' => ['title' => 'Tryout UTBK', 'group' => 'akademik', 'endpoint' => 'GET utbk/tryouts'],
        'library' => ['title' => 'Perpustakaan', 'group' => 'akademik', 'endpoint' => 'GET library/books', 'hardware' => ['camera']],
        'calendar' => ['title' => 'Kalender Akademik', 'group' => 'akademik', 'endpoint' => 'GET academic-calendar'],

        // ── Presensi ─────────────────────────────────────────────────────
        'attendance.class_scan' => ['title' => 'Scan QR Kelas', 'group' => 'presensi', 'endpoint' => 'POST student/class-session/scan-qr', 'hardware' => ['camera']],
        'attendance.history' => ['title' => 'Riwayat Kehadiran', 'group' => 'presensi', 'endpoint' => 'GET student/attendance'],
        'scanner.event' => ['title' => 'Pindai Tiket & Tamu', 'group' => 'presensi', 'endpoint' => 'POST mobile/scanner/event-ticket', 'hardware' => ['camera']],

        // ── Ibadah ───────────────────────────────────────────────────────
        'student.mutabaah' => ['title' => "Mutaba'ah", 'group' => 'ibadah', 'endpoint' => 'GET student/mutabaah'],
        'student.tahfidz' => ['title' => 'Tahfidz', 'group' => 'ibadah', 'endpoint' => 'GET student/tahfidz'],
        'tahsin.record' => ['title' => 'Setor Tahsin', 'group' => 'ibadah', 'endpoint' => 'POST tahsin/submissions', 'hardware' => ['microphone']],
        'tahsin.review' => ['title' => 'Koreksi Tahsin', 'group' => 'ibadah', 'endpoint' => 'GET teacher/tahsin/submissions', 'hardware' => ['audio_output']],

        // ── Keuangan ─────────────────────────────────────────────────────
        'student.billing' => ['title' => 'Tagihan', 'group' => 'keuangan', 'endpoint' => 'GET student/billings'],

        // ── Kesiswaan ────────────────────────────────────────────────────
        'discipline' => ['title' => 'Kedisiplinan', 'group' => 'kesiswaan', 'endpoint' => 'GET discipline/summary'],
        'counseling.student' => ['title' => 'Konseling BK', 'group' => 'kesiswaan', 'endpoint' => 'GET counseling/appointments/my'],
        'counseling.bullying_report' => ['title' => 'Lapor Perundungan', 'group' => 'layanan', 'endpoint' => 'POST counseling/bullying-reports'],
        'health.history' => ['title' => 'Riwayat Kesehatan', 'group' => 'kesiswaan', 'endpoint' => 'GET uks/visits/my'],
        'student.profile360' => ['title' => 'Profil 360°', 'group' => 'kesiswaan', 'endpoint' => 'GET profile/student/me'],
        'extracurricular' => ['title' => 'Ekstrakurikuler', 'group' => 'kesiswaan', 'endpoint' => 'GET extracurricular/list'],
        'achievements' => ['title' => 'Prestasi', 'group' => 'kesiswaan', 'endpoint' => 'GET achievements/list', 'hardware' => ['camera']],
        'student.evaluations' => ['title' => 'Evaluasi & E-Voting', 'group' => 'kesiswaan', 'endpoint' => 'GET evaluations/teachers'],

        // ── Mengajar (guru) ──────────────────────────────────────────────
        'teacher.sessions' => ['title' => 'Sesi Kelas Hari Ini', 'group' => 'mengajar', 'endpoint' => 'GET teacher/class-sessions/today', 'hardware' => ['camera']],
        'teacher.schedule' => ['title' => 'Jadwal Mengajar', 'group' => 'mengajar', 'endpoint' => 'GET teacher/schedule'],
        'teacher.classes' => ['title' => 'Kelas & Presensi', 'group' => 'mengajar', 'endpoint' => 'GET teacher/classes'],
        'teacher.journal' => ['title' => 'Jurnal Mengajar', 'group' => 'mengajar', 'endpoint' => 'GET teacher/journals'],
        'teacher.assessments' => ['title' => 'Penilaian Harian', 'group' => 'mengajar', 'endpoint' => 'GET assessments/teacher'],
        'teacher.elearning' => ['title' => 'Kelas Online', 'group' => 'mengajar', 'endpoint' => 'GET elearning/classes/teacher'],
        'teacher.cbt' => ['title' => 'Ujian & Pengawasan', 'group' => 'mengajar', 'endpoint' => 'GET teacher/cbt/exams'],
        'question_bank' => ['title' => 'Bank Soal', 'group' => 'akademik', 'endpoint' => 'GET question-bank/items'],
        'class_analytics' => ['title' => 'Analitik Kelas', 'group' => 'akademik', 'endpoint' => 'GET analytics/teacher/class-performance'],
        'teacher.messages' => ['title' => 'Pesan Orang Tua', 'group' => 'mengajar', 'endpoint' => 'GET teacher/messages'],
        'counseling.staff' => ['title' => 'Layanan BK', 'group' => 'mengajar', 'endpoint' => 'GET counseling/dashboard'],
        'counseling.bullying_reports' => ['title' => 'Laporan Perundungan', 'group' => 'mengajar', 'endpoint' => 'GET counseling/bullying-reports'],
        'uks.record' => ['title' => 'Catat Kunjungan UKS', 'group' => 'mengajar', 'endpoint' => 'POST uks/visits'],

        // ── Anak saya (orang tua) ────────────────────────────────────────
        'parent.children' => ['title' => 'Ringkasan Anak', 'group' => 'keluarga', 'endpoint' => 'GET parent/children'],
        'parent.progress' => ['title' => 'Perkembangan Anak', 'group' => 'keluarga', 'endpoint' => 'GET analytics/parent/child-progress'],
        'activity.feed' => ['title' => 'Linimasa Aktivitas', 'group' => 'kesiswaan', 'endpoint' => 'GET parent/feed'],
        'parent.messages' => ['title' => 'Pesan Guru', 'group' => 'keluarga', 'endpoint' => 'GET parent/messages'],

        // ── Manajemen (operator & pimpinan) ──────────────────────────────
        'admin.dashboard' => ['title' => 'Dasbor Sekolah', 'group' => 'manajemen', 'endpoint' => 'GET mobile/admin/dashboard'],
        'admin.approvals' => ['title' => 'Persetujuan', 'group' => 'manajemen', 'endpoint' => 'GET mobile/admin/approvals'],
        'admin.broadcast' => ['title' => 'Siaran Darurat', 'group' => 'manajemen', 'endpoint' => 'POST mobile/admin/emergency-broadcast'],
        'admin.class_sessions' => ['title' => 'Monitor Sesi Kelas', 'group' => 'manajemen', 'endpoint' => 'GET admin/class-sessions'],

        // ── Layanan (semua pengguna) ─────────────────────────────────────
        'announcements' => ['title' => 'Pengumuman', 'group' => 'layanan', 'endpoint' => 'GET announcements'],
        'notifications' => ['title' => 'Notifikasi', 'group' => 'layanan', 'endpoint' => 'GET notifications'],
    ],
];
