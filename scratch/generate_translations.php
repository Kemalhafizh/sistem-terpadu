<?php

$basePath = 'c:/project/portofolio/project-super-web/sistem-terpadu';
$langPath = "$basePath/lang";

// Create directories
@mkdir("$langPath/id", 0777, true);
@mkdir("$langPath/en", 0777, true);
@mkdir("$langPath/ar", 0777, true);

// ==========================================
// 1. INDONESIAN (id)
// ==========================================

$idNavigation = <<<'PHP'
<?php
return [
    'dashboard' => 'Dashboard',
    'academic' => 'Akademik',
    'classrooms' => 'Daftar Kelas',
    'subjects' => 'Mata Pelajaran',
    'students' => 'Data Siswa',
    'schedules' => 'Jadwal Belajar',
    'e_report' => 'E-Rapor Digital',
    'finance' => 'Keuangan SPP',
    'billings' => 'Tagihan SPP',
    'reports' => 'Laporan Buku Besar',
    'counseling' => 'Konseling BK',
    'e_learning' => 'E-Learning & Tugas',
    'cbt' => 'Ujian Online CBT',
    'settings' => 'Pengaturan',
    'dev_portal' => 'Developer Portal',
    'logout' => 'Keluar',
    'language' => 'Bahasa',
    'theme' => 'Tema',
];
PHP;
file_put_contents("$langPath/id/navigation.php", $idNavigation);

$idDashboard = <<<'PHP'
<?php
return [
    'welcome' => 'Selamat Datang Kembali',
    'subtitle' => 'Sistem Informasi Sekolah Terpadu & SaaS Platform SISTA',
    'total_students' => 'Total Siswa Aktif',
    'active_staff' => 'Karyawan & Guru',
    'attendance_rate' => 'Rasio Kehadiran',
    'quick_access' => 'Akses Cepat Modul',
    'system_status' => 'Status Sistem',
    'observability' => 'Observabilitas',
    'recent_activities' => 'Aktivitas Terbaru',
    'no_activities' => 'Belum ada aktivitas terbaru hari ini.',
    'select_language' => 'Pilih Bahasa',
    'select_theme' => 'Pilih Tema Visual',
];
PHP;
file_put_contents("$langPath/id/dashboard.php", $idDashboard);

$idAuth = <<<'PHP'
<?php
return [
    'failed' => 'Kredensial tersebut tidak cocok dengan data kami.',
    'password' => 'Kata sandi yang dimasukkan salah.',
    'throttle' => 'Terlalu banyak percobaan masuk. Silakan coba lagi dalam :seconds detik.',
    'login' => 'Masuk Akun',
    'email' => 'Alamat Email',
    'pass_label' => 'Kata Sandi',
    'remember_me' => 'Ingat Saya',
    'submit' => 'Masuk Sekarang',
    'two_factor_title' => 'Verifikasi Dua Faktor (2FA)',
    'two_factor_desc' => 'Silakan masukkan kode OTP yang telah dikirim ke alamat email Anda.',
];
PHP;
file_put_contents("$langPath/id/auth.php", $idAuth);

$idAcademic = <<<'PHP'
<?php
return [
    'student_list' => 'Daftar Siswa Terdaftar',
    'add_student' => 'Tambah Siswa Baru',
    'classroom_list' => 'Daftar Rombongan Belajar',
    'subject_list' => 'Mata Pelajaran Aktif',
    'grade_level' => 'Tingkat Kelas',
    'semester' => 'Semester',
    'teacher' => 'Guru Pengampu',
    'actions' => 'Aksi',
    'active_year' => 'Tahun Pelajaran',
];
PHP;
file_put_contents("$langPath/id/academic.php", $idAcademic);

$idFinance = <<<'PHP'
<?php
return [
    'title' => 'Manajemen Keuangan SPP & Uang Pangkal',
    'spp_billings' => 'Daftar Tagihan Siswa',
    'generate_billings' => 'Buat Tagihan Bulanan Massal',
    'total_paid' => 'Total Terbayar',
    'remaining_balance' => 'Sisa Tagihan',
    'payment_status' => 'Status Bayar',
    'invoice_number' => 'Nomor Invoice',
    'pay_now' => 'Bayar Online (Midtrans)',
];
PHP;
file_put_contents("$langPath/id/finance.php", $idFinance);


// ==========================================
// 2. ENGLISH (en)
// ==========================================

$enNavigation = <<<'PHP'
<?php
return [
    'dashboard' => 'Dashboard',
    'academic' => 'Academic',
    'classrooms' => 'Classrooms List',
    'subjects' => 'Subjects Management',
    'students' => 'Students Data',
    'schedules' => 'Study Schedules',
    'e_report' => 'Digital E-Report Card',
    'finance' => 'SPP Financials',
    'billings' => 'SPP Billings',
    'reports' => 'Ledger Reports',
    'counseling' => 'BK Counseling',
    'e_learning' => 'E-Learning & Tasks',
    'cbt' => 'CBT Online Exam',
    'settings' => 'Settings',
    'dev_portal' => 'Developer Portal',
    'logout' => 'Log Out',
    'language' => 'Language',
    'theme' => 'Theme',
];
PHP;
file_put_contents("$langPath/en/navigation.php", $enNavigation);

$enDashboard = <<<'PHP'
<?php
return [
    'welcome' => 'Welcome Back',
    'subtitle' => 'SISTA Integrated School Information & SaaS Platform',
    'total_students' => 'Total Active Students',
    'active_staff' => 'Staff & Teachers',
    'attendance_rate' => 'Attendance Rate',
    'quick_access' => 'Quick Module Access',
    'system_status' => 'System Status',
    'observability' => 'Observability',
    'recent_activities' => 'Recent Activities',
    'no_activities' => 'No recent activities found today.',
    'select_language' => 'Select Language',
    'select_theme' => 'Select Visual Theme',
];
PHP;
file_put_contents("$langPath/en/dashboard.php", $enDashboard);

$enAuth = <<<'PHP'
<?php
return [
    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'login' => 'Sign In',
    'email' => 'Email Address',
    'pass_label' => 'Password',
    'remember_me' => 'Remember Me',
    'submit' => 'Sign In Now',
    'two_factor_title' => 'Two-Factor Authentication (2FA)',
    'two_factor_desc' => 'Please enter the OTP code sent to your email address.',
];
PHP;
file_put_contents("$langPath/en/auth.php", $enAuth);

$enAcademic = <<<'PHP'
<?php
return [
    'student_list' => 'Registered Students List',
    'add_student' => 'Add New Student',
    'classroom_list' => 'Study Groups List (Classrooms)',
    'subject_list' => 'Active Subjects',
    'grade_level' => 'Grade Level',
    'semester' => 'Semester',
    'teacher' => 'Teacher',
    'actions' => 'Actions',
    'active_year' => 'Academic Year',
];
PHP;
file_put_contents("$langPath/en/academic.php", $enAcademic);

$enFinance = <<<'PHP'
<?php
return [
    'title' => 'SPP & Entrance Fee Financial Management',
    'spp_billings' => 'Student Billings List',
    'generate_billings' => 'Generate Monthly Bulk Billings',
    'total_paid' => 'Total Paid Amount',
    'remaining_balance' => 'Remaining Balance',
    'payment_status' => 'Payment Status',
    'invoice_number' => 'Invoice Number',
    'pay_now' => 'Pay Online (Midtrans)',
];
PHP;
file_put_contents("$langPath/en/finance.php", $enFinance);


// ==========================================
// 3. ARABIC (ar)
// ==========================================

$arNavigation = <<<'PHP'
<?php
return [
    'dashboard' => 'لوحة التحكم',
    'academic' => 'الأكاديمي',
    'classrooms' => 'قائمة الفصول الدراسية',
    'subjects' => 'إدارة المواد الدراسية',
    'students' => 'بيانات الطلاب',
    'schedules' => 'جداول الدراسة',
    'e_report' => 'بطاقة التقرير الرقمي',
    'finance' => 'الشؤون المالية SPP',
    'billings' => 'فواتير SPP',
    'reports' => 'تقارير دفتر الأستاذ',
    'counseling' => 'الاستشارات المدرسية BK',
    'e_learning' => 'التعلم الإلكتروني والمهام',
    'cbt' => 'امتحان CBT عبر الإنترنت',
    'settings' => 'الإعدادات',
    'dev_portal' => 'بوابة المطورين',
    'logout' => 'تسجيل الخروج',
    'language' => 'اللغة',
    'theme' => 'المظهر',
];
PHP;
file_put_contents("$langPath/ar/navigation.php", $arNavigation);

$arDashboard = <<<'PHP'
<?php
return [
    'welcome' => 'مرحباً بك مجدداً',
    'subtitle' => 'سيستا نظام معلومات المدارس المتكاملة ومنصة SaaS',
    'total_students' => 'إجمالي الطلاب النشطين',
    'active_staff' => 'الموظفين والمعلمين',
    'attendance_rate' => 'نسبة الحضور',
    'quick_access' => 'الوصول السريع للمودول',
    'system_status' => 'حالة النظام',
    'observability' => 'المراقبة والتحليل',
    'recent_activities' => 'الأنشطة الأخيرة',
    'no_activities' => 'لم يتم العثور على أنشطة حديثة اليوم.',
    'select_language' => 'اختر اللغة',
    'select_theme' => 'اختر مظهر الرؤية',
];
PHP;
file_put_contents("$langPath/ar/dashboard.php", $arDashboard);

$arAuth = <<<'PHP'
<?php
return [
    'failed' => 'كردينشال الحساب غير مطابقة لسجلاتنا.',
    'password' => 'كلمة المرور المدخلة غير صحيحة.',
    'throttle' => 'محاولات دخول كثيرة جداً. يرجى المحاولة بعد :seconds ثوانٍ.',
    'login' => 'تسجيل الدخول',
    'email' => 'البريد الإلكتروني',
    'pass_label' => 'كلمة المرور',
    'remember_me' => 'تذكرني',
    'submit' => 'سجل الدخول الآن',
    'two_factor_title' => 'المصادقة الثنائية (2FA)',
    'two_factor_desc' => 'يرجى إدخال رمز التحقق (OTP) المرسل إلى بريدك الإلكتروني.',
];
PHP;
file_put_contents("$langPath/ar/auth.php", $arAuth);

$arAcademic = <<<'PHP'
<?php
return [
    'student_list' => 'قائمة الطلاب المسجلين',
    'add_student' => 'إضافة طالب جديد',
    'classroom_list' => 'قائمة المجموعات الدراسية (الفصول)',
    'subject_list' => 'المواد الدراسية النشطة',
    'grade_level' => 'المستوى الدراسي',
    'semester' => 'الفصل الدراسي',
    'teacher' => 'المعلم المسؤول',
    'actions' => 'العمليات',
    'active_year' => 'السنة الأكاديمية',
];
PHP;
file_put_contents("$langPath/ar/academic.php", $arAcademic);

$arFinance = <<<'PHP'
<?php
return [
    'title' => 'الإدارة المالية لرسوم SPP ورسوم الدخول',
    'spp_billings' => 'قائمة فواتير الطلاب',
    'generate_billings' => 'إنشاء فواتير جماعية شهرية',
    'total_paid' => 'إجمالي المبلغ المدفوع',
    'remaining_balance' => 'الرصيد المتبقي',
    'payment_status' => 'حالة الدفع',
    'invoice_number' => 'رقم الفاتورة',
    'pay_now' => 'الدفع عبر الإنترنت (Midtrans)',
];
PHP;
file_put_contents("$langPath/ar/finance.php", $arFinance);

echo "Translations generation completed successfully!\n";
PHP;

file_put_contents("$basePath/scratch/generate_translations.php", $codeContent);
