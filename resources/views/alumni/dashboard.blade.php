<x-app-layout>
    <x-slot name="title">Dashboard Statistik Alumni</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    📊 Dashboard Analytics Alumni
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Statistik penyerapan lulusan SMA Islam Sultan Agung 1 Semarang.
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('alumni.index') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                    📋 Daftar Alumni
                </a>
                <a href="{{ route('alumni.network-map') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                    🗺️ Peta Sebaran
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Grid Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Card 1 -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center text-xl font-bold">
                    🎓
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Alumni</span>
                    <h3 class="font-black text-2xl text-gray-800 dark:text-white mt-0.5">{{ $stats['total_alumni'] }}</h3>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-2xl flex items-center justify-center text-xl font-bold">
                    📚
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kuliah (Studi)</span>
                    <h3 class="font-black text-2xl text-gray-800 dark:text-white mt-0.5">
                        {{ $stats['activity_distribution']['study']['count'] }}
                        <span class="text-xs font-bold text-gray-400 ml-1">({{ $stats['activity_distribution']['study']['percentage'] }}%)</span>
                    </h3>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center text-xl font-bold">
                    💼
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Bekerja/Wirausaha</span>
                    <h3 class="font-black text-2xl text-gray-800 dark:text-white mt-0.5">
                        {{ $stats['activity_distribution']['work']['count'] + $stats['activity_distribution']['entrepreneur']['count'] }}
                        <span class="text-xs font-bold text-gray-400 ml-1">({{ round($stats['activity_distribution']['work']['percentage'] + $stats['activity_distribution']['entrepreneur']['percentage'], 1) }}%)</span>
                    </h3>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-2xl flex items-center justify-center text-xl font-bold">
                    📍
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sebaran Wilayah</span>
                    <h3 class="font-black text-2xl text-gray-800 dark:text-white mt-0.5">{{ $geography->count() }} <span class="text-xs font-bold text-gray-400 ml-1">Kota</span></h3>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Doughnut Chart: Aktivitas -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl lg:col-span-1 flex flex-col justify-between">
                <h3 class="font-bold text-gray-850 dark:text-gray-150 text-sm mb-4">🍩 Distribusi Aktivitas Alumni</h3>
                <div class="relative w-full aspect-square flex items-center justify-center">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>

            <!-- Bar Chart: Top 5 Perusahaan/Instansi -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl lg:col-span-2 flex flex-col justify-between">
                <h3 class="font-bold text-gray-850 dark:text-gray-150 text-sm mb-4">🏢 Top 5 Instansi / Tempat Kerja Alumni</h3>
                <div class="relative w-full aspect-[2/1] min-h-[220px]">
                    <canvas id="institutionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Lists Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Top Majors -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm">🎓 Top Program Studi Pilihan (Kuliah)</h3>
                <div class="space-y-3">
                    @forelse($stats['top_majors'] as $mj)
                        <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl">
                            <span class="font-bold text-xs text-gray-700 dark:text-gray-300">{{ $mj['study_major'] }}</span>
                            <span class="px-2.5 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 font-extrabold text-[10px] rounded-lg">{{ $mj['total'] }} Alumni</span>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic py-4">Belum ada data program studi alumni.</p>
                    @endforelse
                </div>
            </div>

            <!-- Top Jobs -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm">🧑‍💼 Top Profesi / Pekerjaan Alumni</h3>
                <div class="space-y-3">
                    @forelse($stats['top_jobs'] as $jb)
                        <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl">
                            <span class="font-bold text-xs text-gray-700 dark:text-gray-300">{{ $jb['job_title'] }}</span>
                            <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-extrabold text-[10px] rounded-lg">{{ $jb['total'] }} Alumni</span>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic py-4">Belum ada data jenis pekerjaan alumni.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- ChartJS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Activity Distribution Chart
            const ctxActivity = document.getElementById('activityChart').getContext('2d');
            new Chart(ctxActivity, {
                type: 'doughnut',
                data: {
                    labels: ['Kuliah', 'Bekerja', 'Wirausaha', 'Mencari Kerja', 'Lainnya'],
                    datasets: [{
                        data: [
                            {{ $stats['activity_distribution']['study']['count'] }},
                            {{ $stats['activity_distribution']['work']['count'] }},
                            {{ $stats['activity_distribution']['entrepreneur']['count'] }},
                            {{ $stats['activity_distribution']['unemployed']['count'] }},
                            {{ $stats['activity_distribution']['other']['count'] }}
                        ],
                        backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#6b7280'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: { size: 10 },
                                color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#4b5563'
                            }
                        }
                    }
                }
            });

            // 2. Top Institutions Chart
            const ctxInst = document.getElementById('institutionChart').getContext('2d');
            const institutionsData = @json($stats['top_institutions']);
            
            const instLabels = institutionsData.map(item => item.institution_name);
            const instValues = institutionsData.map(item => item.total);

            new Chart(ctxInst, {
                type: 'bar',
                data: {
                    labels: instLabels.length ? instLabels : ['Belum Ada Data'],
                    datasets: [{
                        label: 'Jumlah Alumni',
                        data: instValues.length ? instValues : [0],
                        backgroundColor: '#6366f1',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { display: false },
                            ticks: { precision: 0 }
                        },
                        x: {
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    </script>
</x-app-layout>
