<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight dark:text-white">
                    {{ __('Analitik & Dashboard Eksekutif Lanjutan') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Data Manajemen Sekolah Terpadu</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('analytics.index', ['clear_cache' => 1]) }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2"></path></svg>
                    Segarkan Data
                </a>
                <a href="{{ route('analytics.export.all') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-emerald-950/10">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Ekspor Semua (Excel)
                </a>
            </div>
        </div>
    </x-slot>

    <!-- load ApexCharts CDN directly to guarantee standalone stability -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Classroom Filter Block -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-950 text-primary-600 dark:text-primary-400 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 dark:text-white">Filter Analitik Akademik</h4>
                        <p class="text-xs text-gray-400">Pilih kelas untuk menyaring distribusi nilai secara dinamis</p>
                    </div>
                </div>
                <form action="{{ route('analytics.index') }}" method="GET" class="w-full md:w-auto flex items-center gap-2">
                    <select name="classroom_id" onchange="this.form.submit()" class="w-full md:w-64 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-bold text-gray-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">-- Seluruh Kelas --</option>
                        @foreach($classrooms as $class)
                            <option value="{{ $class->id }}" {{ $classroomId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <!-- Stats/KPI Grid with Glassmorphic effects & Micro-animations -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- KPI 1: Kehadiran -->
                @php
                    $latestAttendance = $attendanceTrend->last();
                    $presencePct = $latestAttendance ? $latestAttendance['percentage'] : 0.0;
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center transition-all duration-300 hover:translate-y-[-4px] hover:shadow-lg">
                    <div class="w-14 h-14 bg-blue-50 dark:bg-blue-950/40 rounded-2xl flex items-center justify-center text-blue-600 dark:text-blue-400 flex-shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-xs text-gray-400 font-black uppercase tracking-widest">Rata Kehadiran</p>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $presencePct }}%</h3>
                        <p class="text-[10px] font-bold text-gray-400 mt-0.5">Bulan Terakhir</p>
                    </div>
                </div>

                <!-- KPI 2: Keuangan -->
                <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center transition-all duration-300 hover:translate-y-[-4px] hover:shadow-lg">
                    <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 flex-shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-xs text-gray-400 font-black uppercase tracking-widest">Rasio Pelunasan</p>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $finance['global']['paid_percentage'] }}%</h3>
                        <p class="text-[10px] font-bold text-emerald-500 mt-0.5">Tingkat Lunas SPP</p>
                    </div>
                </div>

                <!-- KPI 3: Bimbingan Konseling -->
                <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center transition-all duration-300 hover:translate-y-[-4px] hover:shadow-lg">
                    <div class="w-14 h-14 bg-red-50 dark:bg-red-950/40 rounded-2xl flex items-center justify-center text-red-600 dark:text-red-400 flex-shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-xs text-gray-400 font-black uppercase tracking-widest">Poin Kedisiplinan</p>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $bk['global']['penalty_points'] }}</h3>
                        <p class="text-[10px] font-bold text-red-500 mt-0.5">Total Poin Pelanggaran</p>
                    </div>
                </div>

                <!-- KPI 4: E-Learning -->
                <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center transition-all duration-300 hover:translate-y-[-4px] hover:shadow-lg">
                    <div class="w-14 h-14 bg-purple-50 dark:bg-purple-950/40 rounded-2xl flex items-center justify-center text-purple-600 dark:text-purple-400 flex-shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-xs text-gray-400 font-black uppercase tracking-widest">E-Learning Rata</p>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $elearning['average_score'] }}</h3>
                        <p class="text-[10px] font-bold text-purple-500 mt-0.5">Nilai Tugas & Ujian</p>
                    </div>
                </div>

            </div>

            <!-- Row 1: Charts (Kehadiran & Akademik) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Chart 1: Tren Kehadiran Bulanan (6 Bulan) -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h4 class="text-lg font-black text-gray-900 dark:text-white">Tren Kehadiran Bulanan</h4>
                            <p class="text-xs text-gray-400 font-bold">Rasio Hadir vs Sakit vs Izin vs Alpa (6 Bulan)</p>
                        </div>
                        <a href="{{ route('analytics.export.attendance') }}" class="p-2 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 text-primary-600 dark:text-primary-400 flex items-center justify-center transition-all" title="Ekspor PDF">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </a>
                    </div>
                    <div id="attendanceTrendChart" class="w-full h-80"></div>
                </div>

                <!-- Chart 2: Top 10 Rata-Rata Nilai Mapel -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h4 class="text-lg font-black text-gray-900 dark:text-white">Rata-Rata Nilai Mata Pelajaran</h4>
                            <p class="text-xs text-gray-400 font-bold">Top 10 Pencapaian Rata-Rata Tertinggi</p>
                        </div>
                        <a href="{{ route('analytics.export.grades') }}" class="p-2 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 text-primary-600 dark:text-primary-400 flex items-center justify-center transition-all" title="Ekspor PDF">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </a>
                    </div>
                    <div id="subjectGradesChart" class="w-full h-80"></div>
                </div>

            </div>

            <!-- Row 2: Finance Section -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white">Rasio Tunggakan & Keuangan Sekolah</h4>
                        <p class="text-xs text-gray-400 font-bold">Rasio Pelunasan Global dan Tunggakan Rinci per Kelas</p>
                    </div>
                    <a href="{{ route('analytics.export.finance') }}" class="p-2 rounded-xl bg-gray-50 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 text-primary-600 dark:text-primary-400 flex items-center justify-center transition-all" title="Ekspor PDF">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                    <!-- Global donut chart (1/3) -->
                    <div class="flex flex-col items-center justify-center p-6 border-b xl:border-b-0 xl:border-r border-gray-100 dark:border-gray-700">
                        <h5 class="text-sm font-bold text-gray-700 dark:text-gray-200 mb-4 uppercase tracking-wider text-center">Status Pelunasan Global</h5>
                        <div id="financialDonutChart" class="w-full flex justify-center"></div>
                        <div class="mt-4 text-center space-y-1">
                            <p class="text-xs text-gray-500 font-semibold">Total Tagihan: <span class="font-black text-gray-800 dark:text-white">Rp {{ number_format($finance['global']['total'], 0, ',', '.') }}</span></p>
                            <p class="text-xs text-gray-500 font-semibold">Lunas: <span class="font-black text-emerald-600">Rp {{ number_format($finance['global']['paid'], 0, ',', '.') }}</span></p>
                            <p class="text-xs text-gray-500 font-semibold">Tunggakan: <span class="font-black text-red-500">Rp {{ number_format($finance['global']['unpaid'], 0, ',', '.') }}</span></p>
                        </div>
                    </div>

                    <!-- Classroom bar chart (2/3) -->
                    <div class="xl:col-span-2 p-2">
                        <h5 class="text-sm font-bold text-gray-700 dark:text-gray-200 mb-4 uppercase tracking-wider">Tunggakan vs Pelunasan per Kelas</h5>
                        <div id="financeClassroomsChart" class="w-full h-72"></div>
                    </div>
                </div>
            </div>

            <!-- Row 3: BK Points & Teacher performance -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                
                <!-- Column 1: BK Points (1/3) -->
                <div class="xl:col-span-1 bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col">
                    <h4 class="text-lg font-black text-gray-900 dark:text-white mb-2">Penalti vs Penghargaan BK</h4>
                    <p class="text-xs text-gray-400 font-bold mb-6">Poin Pelanggaran vs Poin Prestasi Siswa</p>
                    <div id="bkRadarChart" class="w-full flex justify-center my-auto"></div>
                    <div class="mt-6 grid grid-cols-2 gap-4 text-center">
                        <div class="p-3 bg-red-50 dark:bg-red-950/20 rounded-2xl">
                            <p class="text-[10px] font-black uppercase text-red-600">Penalti</p>
                            <h4 class="text-lg font-black text-red-700 dark:text-red-400 mt-1">{{ $bk['global']['penalty_count'] }} Kasus</h4>
                        </div>
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-2xl">
                            <p class="text-[10px] font-black uppercase text-emerald-600">Penghargaan</p>
                            <h4 class="text-lg font-black text-emerald-700 dark:text-emerald-400 mt-1">{{ $bk['global']['reward_count'] }} Kasus</h4>
                        </div>
                    </div>
                </div>

                <!-- Column 2 & 3: Teacher performance & SPMB Growth (2/3) -->
                <div class="xl:col-span-2 space-y-8">
                    
                    <!-- Teacher performance -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                        <h4 class="text-lg font-black text-gray-900 dark:text-white mb-2">Kinerja Guru & Jurnal Harian</h4>
                        <p class="text-xs text-gray-400 font-bold mb-6">Top 10 Guru Paling Aktif Mengisi Jurnal Harian</p>
                        <div id="teacherPerformanceChart" class="w-full h-64"></div>
                    </div>

                    <!-- SPMB YoY Growth -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                        <h4 class="text-lg font-black text-gray-900 dark:text-white mb-2">Pertumbuhan Pendaftar SPMB Online</h4>
                        <p class="text-xs text-gray-400 font-bold mb-6">Pertumbuhan Penerimaan Siswa Baru per Tahun Ajaran (YoY)</p>
                        <div id="spmbGrowthChart" class="w-full h-64"></div>
                    </div>

                </div>

            </div>

        </div>
    </div>

    <!-- Interactive ApexCharts JavaScript configuration with harmonious palettes -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ApexCharts configuration options
            const primaryColor = '#3b82f6';
            const successColor = '#10b981';
            const warningColor = '#f59e0b';
            const dangerColor = '#ef4444';
            const purpleColor = '#8b5cf6';
            
            // Theme setting
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#cbd5e1' : '#475569';
            const gridColor = isDark ? '#334155' : '#e2e8f0';

            // 1. Attendance Trend Chart (Multi-line chart)
            const attendanceTrendData = @json($attendanceTrend);
            const attendanceMonths = attendanceTrendData.map(d => d.month);
            const attendanceHadir = attendanceTrendData.map(d => d.H);
            const attendanceSakit = attendanceTrendData.map(d => d.S);
            const attendanceIzin = attendanceTrendData.map(d => d.I);
            const attendanceAlpa = attendanceTrendData.map(d => d.A);

            const attendanceOptions = {
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: { show: false },
                    animations: { enabled: true, easeinout: 'easeinout', speed: 800 }
                },
                series: [
                    { name: 'Hadir', data: attendanceHadir },
                    { name: 'Sakit', data: attendanceSakit },
                    { name: 'Izin', data: attendanceIzin },
                    { name: 'Alpa', data: attendanceAlpa }
                ],
                xaxis: { categories: attendanceMonths, labels: { style: { colors: textColor, fontWeight: 'bold' } } },
                yaxis: { labels: { style: { colors: textColor } } },
                colors: [successColor, warningColor, primaryColor, dangerColor],
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: { opacityFrom: 0.4, opacityTo: 0.05 }
                },
                grid: { borderColor: gridColor },
                legend: { labels: { colors: textColor } },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            };
            new ApexCharts(document.querySelector("#attendanceTrendChart"), attendanceOptions).render();

            // 2. Subject Grades Chart (Horizontal bar chart)
            const gradesData = @json($grades['top_subjects']);
            const subjectNames = gradesData.map(d => d.name);
            const subjectAverages = gradesData.map(d => d.average);

            const subjectGradesOptions = {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '60%',
                        borderRadius: 8,
                        distributed: true
                    }
                },
                series: [{ name: 'Rata-Rata Nilai', data: subjectAverages }],
                xaxis: { categories: subjectNames, labels: { style: { colors: textColor, fontWeight: 'bold' } } },
                yaxis: { labels: { style: { colors: textColor, fontWeight: 'bold' } } },
                colors: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f43f5e', '#06b6d4', '#6366f1', '#a855f7'],
                grid: { borderColor: gridColor },
                legend: { show: false },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            };
            new ApexCharts(document.querySelector("#subjectGradesChart"), subjectGradesOptions).render();

            // 3. Financial Donut Chart
            const financeData = @json($finance['global']);
            const financeDonutOptions = {
                chart: { type: 'donut', height: 260 },
                series: [parseFloat(financeData.paid), parseFloat(financeData.unpaid)],
                labels: ['Sudah Dibayar', 'Tunggakan'],
                colors: [successColor, dangerColor],
                legend: { position: 'bottom', labels: { colors: textColor, fontWeight: 'bold' } },
                dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + "%" },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            };
            new ApexCharts(document.querySelector("#financialDonutChart"), financeDonutOptions).render();

            // 4. Financial Classrooms Chart (Grouped stacked columns)
            const financeClasses = @json($finance['by_classroom']);
            const classNames = financeClasses.map(c => c.classroom);
            const classPaid = financeClasses.map(c => c.paid);
            const classUnpaid = financeClasses.map(c => c.unpaid);

            const financeClassroomsOptions = {
                chart: { type: 'bar', height: 280, stacked: true, toolbar: { show: false } },
                series: [
                    { name: 'Sudah Dibayar (Rp)', data: classPaid },
                    { name: 'Tunggakan (Rp)', data: classUnpaid }
                ],
                xaxis: { categories: classNames, labels: { style: { colors: textColor, fontWeight: 'bold' } } },
                yaxis: { labels: { style: { colors: textColor } } },
                colors: [successColor, dangerColor],
                grid: { borderColor: gridColor },
                legend: { labels: { colors: textColor, fontWeight: 'bold' } },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            };
            new ApexCharts(document.querySelector("#financeClassroomsChart"), financeClassroomsOptions).render();

            // 5. BK Radar Chart
            const bkClasses = @json($bk['by_classroom']);
            const bkClassNames = bkClasses.map(b => b.classroom);
            const bkPenalties = bkClasses.map(b => b.penalty_points);
            const bkRewards = bkClasses.map(b => b.reward_points);

            const bkRadarOptions = {
                chart: { type: 'radar', height: 260, toolbar: { show: false } },
                series: [
                    { name: 'Poin Pelanggaran', data: bkPenalties },
                    { name: 'Poin Penghargaan', data: bkRewards }
                ],
                labels: bkClassNames,
                colors: [dangerColor, successColor],
                xaxis: { labels: { style: { colors: textColor } } },
                legend: { position: 'bottom', labels: { colors: textColor } },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            };
            new ApexCharts(document.querySelector("#bkRadarChart"), bkRadarOptions).render();

            // 6. Teacher Performance Chart
            const teachersData = @json($teachers['performance']);
            const teacherNames = teachersData.map(t => t.name);
            const totalJournals = teachersData.map(t => t.total_journals);
            const reviewedJournals = teachersData.map(t => t.reviewed_journals);

            const teacherPerformanceOptions = {
                chart: { type: 'bar', height: 250, toolbar: { show: false } },
                series: [
                    { name: 'Jurnal Ditulis', data: totalJournals },
                    { name: 'Sudah Direview', data: reviewedJournals }
                ],
                xaxis: { categories: teacherNames, labels: { style: { colors: textColor, fontWeight: 'bold' } } },
                yaxis: { labels: { style: { colors: textColor } } },
                colors: [primaryColor, successColor],
                grid: { borderColor: gridColor },
                legend: { labels: { colors: textColor } },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            };
            new ApexCharts(document.querySelector("#teacherPerformanceChart"), teacherPerformanceOptions).render();

            // 7. SPMB YoY Growth Chart
            const spmbGrowthData = @json($spmb['growth']);
            const spmbYears = spmbGrowthData.map(s => s.year || '2026');
            const spmbTotals = spmbGrowthData.map(s => s.total);

            const spmbGrowthOptions = {
                chart: { type: 'area', height: 250, toolbar: { show: false } },
                series: [{ name: 'Pendaftar Baru', data: spmbTotals }],
                xaxis: { categories: spmbYears, labels: { style: { colors: textColor, fontWeight: 'bold' } } },
                yaxis: { labels: { style: { colors: textColor } } },
                colors: [purpleColor],
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: { opacityFrom: 0.4, opacityTo: 0.05 }
                },
                grid: { borderColor: gridColor },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            };
            new ApexCharts(document.querySelector("#spmbGrowthChart"), spmbGrowthOptions).render();
        });
    </script>
</x-app-layout>
