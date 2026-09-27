<?php

$filePath = 'c:\project\portofolio\project-super-web\sistem-terpadu\resources\views\components\sidebar.blade.php';
if (! file_exists($filePath)) {
    exit("Error: File not found at $filePath\n");
}

$content = file_get_contents($filePath);

// Define translation mapping
$mappings = [
    // Headings
    ['whitespace-nowrap">Master Data</p>', 'whitespace-nowrap">{{ __(\'navigation.header_master_data\') }}</p>'],
    ['title="Master Data"', 'title="{{ __(\'navigation.header_master_data\') }}"'],
    ['whitespace-nowrap">Keislaman</p>', 'whitespace-nowrap">{{ __(\'navigation.header_islamic\') }}</p>'],
    ['title="Keislaman"', 'title="{{ __(\'navigation.header_islamic\') }}"'],
    ['whitespace-nowrap">Personalia</p>', 'whitespace-nowrap">{{ __(\'navigation.header_personnel\') }}</p>'],
    ['title="Personalia"', 'title="{{ __(\'navigation.header_personnel\') }}"'],
    ['whitespace-nowrap">Modul Akademik</p>', 'whitespace-nowrap">{{ __(\'navigation.header_academic\') }}</p>'],
    ['title="Modul Akademik"', 'title="{{ __(\'navigation.header_academic\') }}"'],
    ['whitespace-nowrap">Modul Kesiswaan</p>', 'whitespace-nowrap">{{ __(\'navigation.header_student\') }}</p>'],
    ['title="Modul Kesiswaan"', 'title="{{ __(\'navigation.header_student\') }}"'],
    ['whitespace-nowrap">Modul Sarpras</p>', 'whitespace-nowrap">{{ __(\'navigation.header_sarpras\') }}</p>'],
    ['title="Modul Sarpras"', 'title="{{ __(\'navigation.header_sarpras\') }}"'],
    ['whitespace-nowrap">Modul Humas</p>', 'whitespace-nowrap">{{ __(\'navigation.header_humas\') }}</p>'],
    ['title="Modul Humas"', 'title="{{ __(\'navigation.header_humas\') }}"'],
    ['whitespace-nowrap">Modul Administrasi</p>', 'whitespace-nowrap">{{ __(\'navigation.header_administration\') }}</p>'],
    ['title="Modul Administrasi"', 'title="{{ __(\'navigation.header_administration\') }}"'],
    ['whitespace-nowrap">Modul Kepegawaian</p>', 'whitespace-nowrap">{{ __(\'navigation.header_staff\') }}</p>'],
    ['title="Modul Kepegawaian"', 'title="{{ __(\'navigation.header_staff\') }}"'],
    ['whitespace-nowrap">Fitur Akademik Guru</p>', 'whitespace-nowrap">{{ __(\'navigation.header_teacher\') }}</p>'],
    ['title="Fitur Akademik Guru"', 'title="{{ __(\'navigation.header_teacher\') }}"'],
    ['whitespace-nowrap">Sumber Belajar</p>', 'whitespace-nowrap">{{ __(\'navigation.header_learning_resources\') }}</p>'],
    ['title="Sumber Belajar"', 'title="{{ __(\'navigation.header_learning_resources\') }}"'],

    // Links
    ['title="Dashboard"', 'title="{{ __(\'navigation.dashboard\') }}"'],
    ['>Dashboard</span>', '>{{ __(\'navigation.dashboard\') }}</span>'],
    ['>Dashboard</div>', '>{{ __(\'navigation.dashboard\') }}</div>'],

    ['title="Kalender Akademik"', 'title="{{ __(\'navigation.calendar\') }}"'],
    ['>Kalender Akademik</span>', '>{{ __(\'navigation.calendar\') }}</span>'],
    ['>Kalender Akademik</div>', '>{{ __(\'navigation.calendar\') }}</div>'],

    ['title="Jurnal Harian Guru"', 'title="{{ __(\'navigation.journal\') }}"'],
    ['>Jurnal Harian Guru</span>', '>{{ __(\'navigation.journal\') }}</span>'],
    ['>Jurnal Harian Guru</div>', '>{{ __(\'navigation.journal\') }}</div>'],

    ['title="Kurikulum & Silabus"', 'title="{{ __(\'navigation.curriculum\') }}"'],
    ['>Kurikulum & Silabus</span>', '>{{ __(\'navigation.curriculum\') }}</span>'],
    ['>Kurikulum & Silabus</div>', '>{{ __(\'navigation.curriculum\') }}</div>'],

    ['title="Raport Mutu (EDS)"', 'title="{{ __(\'navigation.eds\') }}"'],
    ['>Raport Mutu (EDS)</span>', '>{{ __(\'navigation.eds\') }}</span>'],
    ['>Raport Mutu (EDS)</div>', '>{{ __(\'navigation.eds\') }}</div>'],

    ['title="Portal HRD Saya"', 'title="{{ __(\'navigation.hrd_portal\') }}"'],
    ['>Portal HRD Saya</span>', '>{{ __(\'navigation.hrd_portal\') }}</span>'],
    ['>Portal HRD Saya</div>', '>{{ __(\'navigation.hrd_portal\') }}</div>'],

    ['title="Helpdesk Internal"', 'title="{{ __(\'navigation.helpdesk\') }}"'],
    ['>Helpdesk Internal</span>', '>{{ __(\'navigation.helpdesk\') }}</span>'],
    ['>Helpdesk Internal</div>', '>{{ __(\'navigation.helpdesk\') }}</div>'],

    ['title="Peminjaman Aset"', 'title="{{ __(\'navigation.asset_loans\') }}"'],
    ['>Peminjaman Aset</span>', '>{{ __(\'navigation.asset_loans\') }}</span>'],
    ['>Peminjaman Aset</div>', '>{{ __(\'navigation.asset_loans\') }}</div>'],

    ['title="Pusat Pengumuman"', 'title="{{ __(\'navigation.announcements\') }}"'],
    ['>Pusat Pengumuman</span>', '>{{ __(\'navigation.announcements\') }}</span>'],
    ['>Pusat Pengumuman</div>', '>{{ __(\'navigation.announcements\') }}</div>'],

    ['title="Sarana Prasarana"', 'title="{{ __(\'navigation.assets\') }}"'],
    ['>Sarana Prasarana</span>', '>{{ __(\'navigation.assets\') }}</span>'],
    ['>Sarana Prasarana</div>', '>{{ __(\'navigation.assets\') }}</div>'],

    ['title="Inventaris Aset"', 'title="{{ __(\'navigation.inventaris_assets\') }}"'],
    ['>Inventaris Aset</span>', '>{{ __(\'navigation.inventaris_assets\') }}</span>'],
    ['>Inventaris Aset</div>', '>{{ __(\'navigation.inventaris_assets\') }}</div>'],

    ['title="Perawatan Aset"', 'title="{{ __(\'navigation.assets_maintenance\') }}"'],
    ['>Perawatan Aset</span>', '>{{ __(\'navigation.assets_maintenance\') }}</span>'],
    ['>Perawatan Aset</div>', '>{{ __(\'navigation.assets_maintenance\') }}</div>'],

    ['title="Penyusutan Aset"', 'title="{{ __(\'navigation.assets_depreciation\') }}"'],
    ['>Penyusutan Aset</span>', '>{{ __(\'navigation.assets_depreciation\') }}</span>'],
    ['>Penyusutan Aset</div>', '>{{ __(\'navigation.assets_depreciation\') }}</div>'],

    ['title="Jurnal Tahfidz"', 'title="{{ __(\'navigation.tahfidz\') }}"'],
    ['>Jurnal Tahfidz</span>', '>{{ __(\'navigation.tahfidz\') }}</span>'],
    ['>Jurnal Tahfidz</div>', '>{{ __(\'navigation.tahfidz\') }}</div>'],

    ['title="Mutaba\'ah Harian"', 'title="{{ __(\'navigation.mutabaah\') }}"'],
    ['>Mutaba\'ah Harian</span>', '>{{ __(\'navigation.mutabaah\') }}</span>'],
    ['>Mutaba\'ah Harian</div>', '>{{ __(\'navigation.mutabaah\') }}</div>'],

    ['title="Pengguna & Akses"', 'title="{{ __(\'navigation.users\') }}"'],
    ['>Pengguna & Akses</span>', '>{{ __(\'navigation.users\') }}</span>'],
    ['>Pengguna & Akses</div>', '>{{ __(\'navigation.users\') }}</div>'],

    ['title="Jejak Digital (Audit)"', 'title="{{ __(\'navigation.audit_logs\') }}"'],
    ['>Jejak Digital (Audit)</span>', '>{{ __(\'navigation.audit_logs\') }}</span>'],
    ['>Jejak Digital (Audit)</div>', '>{{ __(\'navigation.audit_logs\') }}</div>'],

    ['title="Pengaturan Sistem"', 'title="{{ __(\'navigation.system_settings\') }}"'],
    ['>Pengaturan Sistem</span>', '>{{ __(\'navigation.system_settings\') }}</span>'],
    ['>Pengaturan Sistem</div>', '>{{ __(\'navigation.system_settings\') }}</div>'],

    ['title="Tahun Ajaran"', 'title="{{ __(\'navigation.academic_years\') }}"'],
    ['>Tahun Ajaran</span>', '>{{ __(\'navigation.academic_years\') }}</span>'],
    ['>Tahun Ajaran</div>', '>{{ __(\'navigation.academic_years\') }}</div>'],

    ['title="Mata Pelajaran"', 'title="{{ __(\'navigation.subjects\') }}"'],
    ['>Mata Pelajaran</span>', '>{{ __(\'navigation.subjects\') }}</span>'],
    ['>Mata Pelajaran</div>', '>{{ __(\'navigation.subjects\') }}</div>'],

    ['title="Data Kelas"', 'title="{{ __(\'navigation.classrooms\') }}"'],
    ['>Data Kelas</span>', '>{{ __(\'navigation.classrooms\') }}</span>'],
    ['>Data Kelas</div>', '>{{ __(\'navigation.classrooms\') }}</div>'],

    ['title="Data Siswa Aktif"', 'title="{{ __(\'navigation.students\') }}"'],
    ['>Data Siswa Aktif</span>', '>{{ __(\'navigation.students\') }}</span>'],
    ['>Data Siswa Aktif</div>', '>{{ __(\'navigation.students\') }}</div>'],

    ['title="Mutasi Siswa"', 'title="{{ __(\'navigation.student_mutations\') }}"'],
    ['>Mutasi Siswa</span>', '>{{ __(\'navigation.student_mutations\') }}</span>'],
    ['>Mutasi Siswa</div>', '>{{ __(\'navigation.student_mutations\') }}</div>'],

    ['title="Database Alumni"', 'title="{{ __(\'navigation.alumni\') }}"'],
    ['>Database Alumni</span>', '>{{ __(\'navigation.alumni\') }}</span>'],
    ['>Database Alumni</div>', '>{{ __(\'navigation.alumni\') }}</div>'],

    ['title="Plotting Jadwal"', 'title="{{ __(\'navigation.schedule_plotting\') }}"'],
    ['>Plotting Jadwal</span>', '>{{ __(\'navigation.schedule_plotting\') }}</span>'],
    ['>Plotting Jadwal</div>', '>{{ __(\'navigation.schedule_plotting\') }}</div>'],

    ['title="Preset Waktu"', 'title="{{ __(\'navigation.schedule_presets\') }}"'],
    ['>Preset Waktu</span>', '>{{ __(\'navigation.schedule_presets\') }}</span>'],
    ['>Preset Waktu</div>', '>{{ __(\'navigation.schedule_presets\') }}</div>'],

    ['title="Poin & Kedisiplinan"', 'title="{{ __(\'navigation.points\') }}"'],
    ['>Poin & Kedisiplinan</span>', '>{{ __(\'navigation.points\') }}</span>'],
    ['>Poin & Kedisiplinan</div>', '>{{ __(\'navigation.points\') }}</div>'],

    ['title="Master Kategori Poin"', 'title="{{ __(\'navigation.point_categories\') }}"'],
    ['>Master Kategori Poin</span>', '>{{ __(\'navigation.point_categories\') }}</span>'],
    ['>Master Kategori Poin</div>', '>{{ __(\'navigation.point_categories\') }}</div>'],

    ['title="UKS Digital"', 'title="{{ __(\'navigation.uks\') }}"'],
    ['>UKS Digital</span>', '>{{ __(\'navigation.uks\') }}</span>'],
    ['>UKS Digital</div>', '>{{ __(\'navigation.uks\') }}</div>'],

    ['title="Kelola Ekskul"', 'title="{{ __(\'navigation.ekskul\') }}"'],
    ['>Kelola Ekskul</span>', '>{{ __(\'navigation.ekskul\') }}</span>'],
    ['>Kelola Ekskul</div>', '>{{ __(\'navigation.ekskul\') }}</div>'],

    ['title="Keuangan OSIS"', 'title="{{ __(\'navigation.osis\') }}"'],
    ['>Keuangan OSIS</span>', '>{{ __(\'navigation.osis\') }}</span>'],
    ['>Keuangan OSIS</div>', '>{{ __(\'navigation.osis\') }}</div>'],

    ['title="Lembaga MPK"', 'title="{{ __(\'navigation.mpk\') }}"'],
    ['>Lembaga MPK</span>', '>{{ __(\'navigation.mpk\') }}</span>'],
    ['>Lembaga MPK</div>', '>{{ __(\'navigation.mpk\') }}</div>'],

    ['title="Berita & Pengumuman"', 'title="{{ __(\'navigation.news_announcements\') }}"'],
    ['>Berita & Pengumuman</span>', '>{{ __(\'navigation.news_announcements\') }}</span>'],
    ['>Berita & Pengumuman</div>', '>{{ __(\'navigation.news_announcements\') }}</div>'],

    ['title="Administrasi Keuangan"', 'title="{{ __(\'navigation.finance_administration\') }}"'],
    ['>Administrasi Keuangan</span>', '>{{ __(\'navigation.finance_administration\') }}</span>'],
    ['>Administrasi Keuangan</div>', '>{{ __(\'navigation.finance_administration\') }}</div>'],

    ['title="Analisis Keuangan (Kepsek)"', 'title="{{ __(\'navigation.finance_analysis\') }}"'],
    ['>Analisis Keuangan (Kepsek)</span>', '>{{ __(\'navigation.finance_analysis\') }}</span>'],
    ['>Analisis Keuangan (Kepsek)</div>', '>{{ __(\'navigation.finance_analysis\') }}</div>'],

    ['title="Analitik Sekolah"', 'title="{{ __(\'navigation.school_analytics\') }}"'],
    ['>Analitik Sekolah</span>', '>{{ __(\'navigation.school_analytics\') }}</span>'],
    ['>Analitik Sekolah</div>', '>{{ __(\'navigation.school_analytics\') }}</div>'],

    ['title="Surat Digital"', 'title="{{ __(\'navigation.digital_letters\') }}"'],
    ['>Surat Digital</span>', '>{{ __(\'navigation.digital_letters\') }}</span>'],
    ['>Surat Digital</div>', '>{{ __(\'navigation.digital_letters\') }}</div>'],

    ['title="Direktori Pegawai"', 'title="{{ __(\'navigation.employee_directory\') }}"'],
    ['>Direktori Pegawai</span>', '>{{ __(\'navigation.employee_directory\') }}</span>'],
    ['>Direktori Pegawai</div>', '>{{ __(\'navigation.employee_directory\') }}</div>'],

    ['title="Absensi Siswa"', 'title="{{ __(\'navigation.student_attendance\') }}"'],
    ['>Absensi Siswa</span>', '>{{ __(\'navigation.student_attendance\') }}</span>'],
    ['>Absensi Siswa</div>', '>{{ __(\'navigation.student_attendance\') }}</div>'],

    ['title="Rekap Presensi"', 'title="{{ __(\'navigation.attendance_recap\') }}"'],
    ['>Rekap Presensi</span>', '>{{ __(\'navigation.attendance_recap\') }}</span>'],
    ['>Rekap Presensi</div>', '>{{ __(\'navigation.attendance_recap\') }}</div>'],

    ['title="Input Nilai Mapel"', 'title="{{ __(\'navigation.grade_input\') }}"'],
    ['>Input Nilai Mapel</span>', '>{{ __(\'navigation.grade_input\') }}</span>'],
    ['>Input Nilai Mapel</div>', '>{{ __(\'navigation.grade_input\') }}</div>'],

    ['title="Rekap Nilai"', 'title="{{ __(\'navigation.grade_recap\') }}"'],
    ['>Rekap Nilai</span>', '>{{ __(\'navigation.grade_recap\') }}</span>'],
    ['>Rekap Nilai</div>', '>{{ __(\'navigation.grade_recap\') }}</div>'],

    ['title="Ledger & E-Rapor"', 'title="{{ __(\'navigation.ledger_report\') }}"'],
    ['>Ledger & E-Rapor</span>', '>{{ __(\'navigation.ledger_report\') }}</span>'],
    ['>Ledger & E-Rapor</div>', '>{{ __(\'navigation.ledger_report\') }}</div>'],

    ['title="Ekstrakurikuler"', 'title="{{ __(\'navigation.ekskul\') }}"'],
    ['>Ekstrakurikuler</span>', '>{{ __(\'navigation.ekskul\') }}</span>'],
    ['>Ekstrakurikuler</div>', '>{{ __(\'navigation.ekskul\') }}</div>'],

    ['title="Smart Library"', 'title="{{ __(\'navigation.library\') }}"'],
    ['>Smart Library</span>', '>{{ __(\'navigation.library\') }}</span>'],
    ['>Smart Library</div>', '>{{ __(\'navigation.library\') }}</div>'],

    ['title="E-Learning"', 'title="{{ __(\'navigation.e_learning\') }}"'],
    ['>E-Learning</span>', '>{{ __(\'navigation.e_learning\') }}</span>'],
    ['>E-Learning</div>', '>{{ __(\'navigation.e_learning\') }}</div>'],

    ['title="Forum Diskusi"', 'title="{{ __(\'navigation.forum\') }}"'],
    ['>Forum Diskusi</span>', '>{{ __(\'navigation.forum\') }}</span>'],
    ['>Forum Diskusi</div>', '>{{ __(\'navigation.forum\') }}</div>'],

    ['title="Survei Digital"', 'title="{{ __(\'navigation.surveys\') }}"'],
    ['>Survei Digital</span>', '>{{ __(\'navigation.surveys\') }}</span>'],
    ['>Survei Digital</div>', '>{{ __(\'navigation.surveys\') }}</div>'],
];

foreach ($mappings as $mapping) {
    $content = str_replace($mapping[0], $mapping[1], $content);
}

if (file_put_contents($filePath, $content) === false) {
    exit("Error: Could not write file back to $filePath\n");
}

echo "Success: Sidebar file translated completely.\n";
