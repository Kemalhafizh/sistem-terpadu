<?php

$filePath = 'c:\project\portofolio\project-super-web\sistem-terpadu\resources\views\dashboard.blade.php';
if (!file_exists($filePath)) {
    die("Error: File not found at $filePath\n");
}
$content = file_get_contents($filePath);

$mappings = [
    // Header title
    '{{ __(\'Dashboard Utama\') }}' => '{{ __(\'dashboard.dashboard_title\') }}',

    // Smart alerts
    '🧠 Smart Alerts Engine Terdeteksi' => '{{ __(\'dashboard.smart_alerts_detected\') }}',
    'Sistem kecerdasan analitik mendeteksi <strong class="text-white">{{ $dashActiveCount }} anomali/alert aktif</strong> (termasuk {{ $dashCriticalCount }} tindakan kritis) yang membutuhkan peninjauan dan solusi segera di lapangan.' => '{!! __(\'dashboard.smart_alerts_desc\', [\'count\' => \'<strong class="text-white">\'.$dashActiveCount.\'</strong>\', \'critical\' => \'<strong class="text-white">\'.$dashCriticalCount.\'</strong>\']) !!}',
    '<span>Buka Dasbor Prediktif</span>' => '<span>{{ __(\'dashboard.open_predictive_dashboard\') }}</span>',

    // Urgent / From
    'Mendesak' => '{{ __(\'dashboard.urgent\') }}',
    'Dari: {{ $announcement?->creator?->name }}' => '{{ __(\'dashboard.from\') }}: {{ $announcement?->creator?->name }}',

    // School overview
    'Ikhtisar Sekolah & Dasbor Cerdas' => '{{ __(\'dashboard.school_overview_dashboard\') }}',
    '🔧 Kustomisasi Dasbor' => '{{ __(\'dashboard.customize_dashboard\') }}',

    // Superadmin quick access
    'Akses Cepat Superadmin' => '{{ __(\'dashboard.superadmin_quick_access\') }}',
    '<p class="text-sm font-bold">Modul Tahfidz & Keislaman</p>' => '<p class="text-sm font-bold">{{ __(\'dashboard.islamic_tahfidz_module\') }}</p>',
    '<p class="text-sm font-bold">Modul BK & Kedisiplinan</p>' => '<p class="text-sm font-bold">{{ __(\'dashboard.bk_discipline_module\') }}</p>',
    '<p class="text-sm font-bold">Modul Keuangan (SPP)</p>' => '<p class="text-sm font-bold">{{ __(\'dashboard.finance_spp_module\') }}</p>',
    '<p class="text-sm font-bold">Manajemen User & Guru</p>' => '<p class="text-sm font-bold">{{ __(\'dashboard.user_teacher_management\') }}</p>',

    // Financial trend headers
    'Tren Pemasukan Keuangan' => '{{ __(\'dashboard.financial_income_trend\') }}',
    '7 Hari Terakhir' => '{{ __(\'dashboard.last_7_days\') }}',

    // Live Feed
    'Live Feed' => '{{ __(\'dashboard.live_feed\') }}',
    'Pembayaran Terbaru' => '{{ __(\'dashboard.recent_payments\') }}',
    '{{ $recentPayment->billing?->student?->user?->name ?? \'Siswa\' }}' => '{{ $recentPayment->billing?->student?->user?->name ?? __(\'navigation.students\') }}',
    '{{ $recentPayment->billing?->category?->name ?? \'Iuran\' }}' => '{{ $recentPayment->billing?->category?->name ?? __(\'dashboard.billing_details\') }}',
    'Belum ada transaksi' => '{{ __(\'dashboard.no_transactions\') }}',

    // Customize drawer
    '<h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Kustomisasi Dasbor</h3>' => '<h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">{{ __(\'dashboard.drawer_title\') }}</h3>',
    '<p class="text-xs font-semibold text-gray-400 mt-0.5">Atur tata letak & visibilitas widget</p>' => '<p class="text-xs font-semibold text-gray-400 mt-0.5">{{ __(\'dashboard.drawer_subtitle\') }}</p>',
    '<p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Seret untuk Mengurutkan & Toggle Aktif</p>' => '<p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __(\'dashboard.drag_to_sort\') }}</p>',
    'Reset' => '{{ __(\'dashboard.reset\') }}',
    'isSaving ? \'Menyimpan...\' : \'Simpan Layout\'' => 'isSaving ? \'{{ __(\'dashboard.saving\') }}\' : \'{{ __(\'dashboard.save_layout\') }}\'',

    // Waka Kurikulum
    'Wadah Akademik & Kurikulum' => '{{ __(\'dashboard.academic_curriculum_hub\') }}',

    // Student points
    'Poin Kedisiplinan Saya' => '{{ __(\'dashboard.my_discipline_points\') }}',
    'Status: {{ $total_points > 50 ? \'Perlu Pembinaan\' : \'Baik\' }}' => '{{ __(\'dashboard.payment_status\') }}: {{ $total_points > 50 ? __(\'dashboard.status_need_guidance\') : __(\'dashboard.status_good\') }}',
    'Lihat Detail Riwayat →' => '{{ __(\'dashboard.development_details\') }} →',

    // Student Finance
    'Tunggakan Administrasi' => '{{ __(\'dashboard.spp_arrears\') }}',
    '{{ $unpaid_billings_count }} Tagihan Belum Lunas' => '{{ $unpaid_billings_count }} {{ __(\'dashboard.payment_status\') }}',
    'Detail Iuran' => '{{ __(\'dashboard.billing_details\') }}',
    'Bayar Sekarang' => '{{ __(\'dashboard.pay_now\') }}',

    // Student Academic
    'Akademik & E-Rapor' => '{{ __(\'dashboard.academic_ereport\') }}',
    'Pantau Nilai & Download Rapor' => '{{ __(\'dashboard.monitor_grades_download_report\') }}',
    'LIHAT NILAI SAYA' => '{{ __(\'dashboard.view_my_grades\') }}',

    // Student Recent Logs
    'Catatan Terakhir' => '{{ __(\'dashboard.recent_notes\') }}',
    '• Oleh {{ $log?->creator?->name }}' => '• {{ __(\'dashboard.from\') }} {{ $log?->creator?->name }}',
    'Belum ada catatan perilaku.' => '{{ __(\'dashboard.no_behavior_notes\') }}',

    // Parent Monitoring
    'Monitoring Anak' => '{{ __(\'dashboard.child_monitoring\') }}',
    '{{ count($children) }} Anak Terdaftar' => '{{ __(\'dashboard.children_registered\', [\'count\' => count($children)]) }}',
    'Poin Kedisiplinan' => '{{ __(\'dashboard.my_discipline_points\') }}',
    'Tunggakan Iuran' => '{{ __(\'dashboard.spp_arrears\') }}',
    'Belum ada catatan.' => '{{ __(\'dashboard.no_activities\') }}',
    '🌟 Buka Portal Premium' => '🌟 {{ __(\'dashboard.open_predictive_dashboard\') }}',
    '📝 Rapor Nilai' => '📝 {{ __(\'dashboard.view_report_card\') }}',
    '📅 Jadwal Kelas' => '📅 {{ __(\'navigation.schedules\') }}',
    '⏰ Presensi Log' => '⏰ {{ __(\'dashboard.view_attendance\') }}',
    '💰 Tagihan SPP' => '💰 {{ __(\'navigation.billings\') }}',

    // Teacher Portal
    'Selamat Bertugas, Pahlawan Pendidikan!' => '{{ __(\'dashboard.teacher_welcome\') }}',
    'Dashboard ini disesuaikan dengan mata pelajaran dan tugas tambahan Anda.' => '{{ __(\'dashboard.teacher_welcome_desc\') }}',
    'Modul Keislaman' => '{{ __(\'dashboard.islamic_tahfidz_module\') }}',
    'Kelola Tahfidz & Mutaba\'ah Yaumiyah siswa.' => '{{ __(\'dashboard.manage_tahfidz_desc\') }}',
    'Kedisiplinan & BK' => '{{ __(\'dashboard.bk_discipline_module\') }}',
    'Catat pelanggaran dan riwayat konseling siswa.' => '{{ __(\'dashboard.manage_discipline_desc\') }}',
    'Perwalian Kelas' => '{{ __(\'dashboard.classroom_advisory\') }}',
    'Anda adalah wali kelas dari:' => '{{ __(\'dashboard.classroom_advisory_desc\') }}',
    '{{ $kelas->students_count }} Siswa' => '{{ __(\'dashboard.student_count\', [\'count\' => $kelas->students_count]) }}',
    'Jadwal & Absensi' => '{{ __(\'dashboard.schedule_attendance\') }}',
    'Lihat jadwal mengajar dan isi presensi harian.' => '{{ __(\'dashboard.schedule_attendance_desc\') }}',

    // Fallback other roles
    'Modul Sedang Disiapkan' => '{{ __(\'dashboard.module_preparing\') }}',
    'Kami sedang merakit wadah digital khusus untuk peran <strong>{{ auth()->user()->role }}</strong>. Dashboard personal Anda akan segera hadir.' => '{!! __(\'dashboard.module_preparing_desc\', [\'role\' => auth()->user()->role]) !!}',

    // Scripts
    'name: \'Pemasukan (Rp)\'' => 'name: \'{{ __(\'dashboard.financial_income_trend\') }} (Rp)\'',
    'Belum ada data pemasukan 7 hari terakhir.' => '{{ __(\'dashboard.no_income_data\') }}',
    'name: \'Pendaftar Baru\'' => 'name: \'{{ __(\'dashboard.new_applicants\') }}\'',
    'return \'Pendaftar:\'' => 'return \'{{ __(\'dashboard.registrants\') }}:\'',
];

foreach ($mappings as $target => $replacement) {
    if (strpos($content, $target) === false) {
        echo "Warning: Target string not found: '$target'\n";
    }
    $content = str_replace($target, $replacement, $content);
}

if (file_put_contents($filePath, $content) === false) {
    die("Error: Could not write file back to $filePath\n");
}

echo "Success: Dashboard file translated completely.\n";
