<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight tracking-tight">
                {{ __('Dasbor Analitik Alumni & Tracer Study') }}
            </h2>
            <div class="mt-4 sm:mt-0 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                Unit Humas & Hubungan Industri
            </div>
        </div>
    </x-slot>

    <!-- Chart.js -->
    <x-vendor.script lib="chartjs" />

    <div class="py-8 space-y-10 w-full">
        <!-- Stats Summary Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Alumni -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center space-x-5">
                <div class="w-12 h-12 bg-blue-50 dark:bg-blue-950/20 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Total Database Alumni</p>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-gray-100 mt-1">{{ $summary['total_alumni'] }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Siswa berstatus alumni</p>
                </div>
            </div>

            <!-- Total Respon -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center space-x-5">
                <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Tanggapan Kuesioner</p>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-gray-100 mt-1">{{ $summary['total_responses'] }}</h3>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1 font-bold">
                        {{ $summary['total_alumni'] > 0 ? round(($summary['total_responses'] / $summary['total_alumni']) * 100, 1) : 0 }}% Tingkat Respon
                    </p>
                </div>
            </div>

            <!-- Rata-rata Relevansi -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center space-x-5">
                <div class="w-12 h-12 bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Relevansi Kurikulum</p>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-gray-100 mt-1">{{ $summary['avg_relevance'] }} <span class="text-sm font-bold text-gray-400">/ 5</span></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Keselarasan materi sekolah</p>
                </div>
            </div>

            <!-- Rata-rata Kepuasan -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center space-x-5">
                <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Kepuasan Alumni</p>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-gray-100 mt-1">{{ $summary['avg_satisfaction'] }} <span class="text-sm font-bold text-gray-400">/ 5</span></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Penilaian layanan sekolah</p>
                </div>
            </div>
        </div>

        <!-- Charts Grid Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- 1. Pie Chart: Sebaran Aktivitas Alumni (2 cols in layout for balance or 1 col) -->
            <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col h-full">
                <div class="mb-6">
                    <h4 class="text-lg font-black text-gray-900 dark:text-gray-100">Sebaran Aktivitas Lulusan</h4>
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">Status karir dan studi pasca sekolah</p>
                </div>
                <div class="flex-grow flex items-center justify-center relative min-h-[300px]">
                    @if($summary['total_alumni'] > 0)
                        <canvas id="activityChart" class="w-full max-h-[300px]"></canvas>
                    @else
                        <div class="text-center text-gray-400 text-sm font-bold">Belum ada data aktivitas lulusan</div>
                    @endif
                </div>
            </div>

            <!-- 2. Bar Chart: Rentang Pendapatan Alumni (Gaji) -->
            <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col h-full">
                <div class="mb-6">
                    <h4 class="text-lg font-black text-gray-900 dark:text-gray-100">Sebaran Pendapatan Alumni</h4>
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">Gaji bulanan alumni bekerja & wirausaha</p>
                </div>
                <div class="flex-grow flex items-center justify-center relative min-h-[300px]">
                    @if($summary['total_responses'] > 0)
                        <canvas id="salaryChart" class="w-full max-h-[300px]"></canvas>
                    @else
                        <div class="text-center text-gray-400 text-sm font-bold">Belum ada data pendapatan alumni</div>
                    @endif
                </div>
            </div>

            <!-- 3. Line Chart: Tren Respon Kelulusan Tahunan -->
            <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col h-full">
                <div class="mb-6">
                    <h4 class="text-lg font-black text-gray-900 dark:text-gray-100">Tren Tracer Study Tahunan</h4>
                    <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">Jumlah lulusan yang berpartisipasi per angkatan</p>
                </div>
                <div class="flex-grow flex items-center justify-center relative min-h-[300px]">
                    @if(count($summary['graduation_trends']) > 0)
                        <canvas id="graduationChart" class="w-full max-h-[300px]"></canvas>
                    @else
                        <div class="text-center text-gray-400 text-sm font-bold">Belum ada data tren tahunan</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Latest Feedback Suggestions (Table style) -->
        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h4 class="text-xl font-black text-gray-900 dark:text-gray-100">Masukan & Saran Alumni Terbaru</h4>
                    <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">Umpan balik langsung untuk perbaikan kurikulum</p>
                </div>
                <div class="w-10 h-10 bg-primary-50 dark:bg-primary-950/20 text-primary-600 dark:text-primary-400 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 text-xs font-black uppercase tracking-widest text-gray-400">
                            <th class="pb-3 w-1/4">Nama Alumni</th>
                            <th class="pb-3 w-1/6 text-center">Angkatan</th>
                            <th class="pb-3 w-1/6 text-center">Relevansi / Kepuasan</th>
                            <th class="pb-3">Saran / Masukan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50 text-sm">
                        @php
                            $latestResponses = \App\Models\TracerResponse::with('alumni.student.user')->latest()->take(5)->get();
                        @endphp
                        @forelse($latestResponses as $resp)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-all">
                                <td class="py-4">
                                    <div class="font-bold text-gray-900 dark:text-gray-100">{{ $resp->alumni->student->user->name }}</div>
                                    <div class="text-xs text-gray-400 mt-1 capitalize">{{ $resp->alumni->current_activity }} ({{ $resp->alumni->institution_name ?? '-' }})</div>
                                </td>
                                <td class="py-4 text-center font-semibold text-gray-500 dark:text-gray-400">
                                    {{ $resp->alumni->graduation_year }}
                                </td>
                                <td class="py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2 text-xs font-bold">
                                        <span class="px-2 py-1 bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 rounded">R: {{ $resp->relevance_score }}</span>
                                        <span class="px-2 py-1 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 rounded">K: {{ $resp->satisfaction_score }}</span>
                                    </div>
                                </td>
                                <td class="py-4 text-gray-600 dark:text-gray-400 italic">
                                    "{{ $resp->suggestions ?? 'Tidak ada masukan khusus.' }}"
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-400 dark:text-gray-500 italic">Belum ada saran/masukan tracer study yang masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart rendering logic using Chart.js -->
    @if($summary['total_alumni'] > 0)
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Dark mode adaptations
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#9ca3af' : '#4b5563';
            const gridColor = isDark ? '#374151' : '#f3f4f6';

            // 1. Sebaran Aktivitas Alumni Pie Chart
            const activityCtx = document.getElementById('activityChart').getContext('2d');
            const activityData = @json($summary['activity_distribution']);
            new Chart(activityCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Kuliah', 'Bekerja', 'Wirausaha', 'Mencari Kerja', 'Lainnya'],
                    datasets: [{
                        data: [
                            activityData.study.count,
                            activityData.work.count,
                            activityData.entrepreneur.count,
                            activityData.unemployed.count,
                            activityData.other.count
                        ],
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                        borderWidth: isDark ? 2 : 0,
                        borderColor: '#1f2937'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                font: { family: 'Inter', weight: 'bold', size: 11 }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });

            // 2. Rentang Pendapatan Alumni Bar Chart
            const salaryCtx = document.getElementById('salaryChart').getContext('2d');
            const salaryData = @json($summary['salary_distribution']);
            new Chart(salaryCtx, {
                type: 'bar',
                data: {
                    labels: ['< 3 Juta', '3 - 5 Juta', '5 - 10 Juta', '> 10 Juta'],
                    datasets: [{
                        label: 'Persentase Alumni (%)',
                        data: [
                            salaryData['< 3M'].percentage,
                            salaryData['3M - 5M'].percentage,
                            salaryData['5M - 10M'].percentage,
                            salaryData['> 10M'].percentage
                        ],
                        backgroundColor: '#10b981',
                        borderRadius: 12
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            grid: { color: gridColor },
                            ticks: {
                                color: textColor,
                                callback: function(value) { return value + '%'; },
                                font: { family: 'Inter', weight: 'bold' }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: textColor,
                                font: { family: 'Inter', weight: 'bold' }
                            }
                        }
                    }
                }
            });

            // 3. Tren Tracer Kelulusan Tahunan
            const graduationCtx = document.getElementById('graduationChart').getContext('2d');
            const trends = @json($summary['graduation_trends']);
            const trendYears = Object.keys(trends);
            const trendCounts = Object.values(trends);
            new Chart(graduationCtx, {
                type: 'line',
                data: {
                    labels: trendYears,
                    datasets: [{
                        label: 'Jumlah Pengisi Survei',
                        data: trendCounts,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#3b82f6',
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            grid: { color: gridColor },
                            ticks: {
                                color: textColor,
                                stepSize: 1,
                                font: { family: 'Inter', weight: 'bold' }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: textColor,
                                font: { family: 'Inter', weight: 'bold' }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endif
</x-app-layout>
