<x-app-layout>
    <x-slot name="title">Analitik Tracer Study SISTA</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    📈 Analisis & Evaluasi Tracer Study
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Hasil kuesioner pelacakan aktivitas dan kepuasan alumni SMA Islam Sultan Agung 1 Semarang.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('alumni.tracer-study.comparison') }}" class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                    📊 Perbandingan Angkatan
                </a>
                <a href="{{ route('alumni.tracer-study.invitation') }}" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                    🔔 Undangan Tracer
                </a>
                <a href="{{ route('alumni.tracer-study.export.excel') }}" class="inline-flex items-center justify-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                    Down Excel CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Stat Widget Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Responded -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center text-xl font-bold">
                    📝
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Mengisi</span>
                    <h3 class="font-black text-2xl text-gray-800 dark:text-white mt-0.5">{{ $summary['total_responses'] }} <span class="text-xs font-bold text-gray-400 ml-1">alumni</span></h3>
                </div>
            </div>

            <!-- Relevance Avg -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-2xl flex items-center justify-center text-xl font-bold">
                    🎯
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Rata-rata Keselarasan</span>
                    <h3 class="font-black text-2xl text-gray-800 dark:text-white mt-0.5">{{ $summary['avg_relevance'] }} <span class="text-xs font-bold text-gray-400 ml-1">/ 5</span></h3>
                </div>
            </div>

            <!-- Satisfaction Avg -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center text-xl font-bold">
                    ❤️
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Rata-rata Kepuasan</span>
                    <h3 class="font-black text-2xl text-gray-800 dark:text-white mt-0.5">{{ $summary['avg_satisfaction'] }} <span class="text-xs font-bold text-gray-400 ml-1">/ 5</span></h3>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Salary Distribution Chart -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl min-h-[300px] flex flex-col justify-between">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-4">💰 Sebaran Pendapatan Bulanan Alumni</h3>
                <div class="relative w-full aspect-[2/1] min-h-[220px]">
                    <canvas id="salaryChart"></canvas>
                </div>
            </div>

            <!-- Graduation Year Trend Chart -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl min-h-[300px] flex flex-col justify-between">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-4">📈 Tren Tanggapan Tracer per Angkatan</h3>
                <div class="relative w-full aspect-[2/1] min-h-[220px]">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Feedback & Suggestions List Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column: Suggestions -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm">💬 Saran & Aspirasi untuk Kurikulum Sekolah</h3>
                <div class="space-y-3 max-h-[350px] overflow-y-auto pr-2 divide-y divide-gray-100 dark:divide-gray-700/50 animate-fade-in">
                    @php
                        $responsesWithSuggestions = \App\Models\TracerResponse::whereNotNull('suggestions')->where('suggestions', '!=', '')->with('alumni.student')->latest()->take(10)->get();
                    @endphp

                    @forelse($responsesWithSuggestions as $res)
                        <div class="pt-3 first:pt-0">
                            <div class="flex justify-between text-[10px] text-gray-400 font-bold mb-1">
                                <span>👤 {{ $res->alumni->student->name }} (Angkatan {{ $res->alumni->graduation_year }})</span>
                                <span>📅 {{ $res->created_at->format('d M Y') }}</span>
                            </div>
                            <p class="text-xs text-gray-700 dark:text-gray-300 italic leading-relaxed bg-gray-50/50 dark:bg-gray-900/30 p-3 rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                                "{{ $res->suggestions }}"
                            </p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic text-center py-6">Belum ada saran/masukan tertulis dari alumni.</p>
                    @endforelse
                </div>
            </div>

            <!-- Right Column: Recent Responded -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm">👥 Responden Terbaru</h3>
                <div class="space-y-3 max-h-[350px] overflow-y-auto pr-2 divide-y divide-gray-100 dark:divide-gray-700/50">
                    @php
                        $recentResponses = \App\Models\TracerResponse::with('alumni.student')->latest()->take(10)->get();
                    @endphp

                    @forelse($recentResponses as $res)
                        <div class="pt-3 first:pt-0 flex justify-between items-center text-xs">
                            <div>
                                <span class="font-bold text-gray-800 dark:text-white">{{ $res->alumni->student->name }}</span>
                                <span class="text-[10px] text-gray-450 ml-1">(Angkatan {{ $res->alumni->graduation_year }})</span>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $res->job_title ?? $res->study_major ?? 'Lainnya' }}</p>
                            </div>
                            <span class="text-[10px] text-gray-400 font-bold">{{ $res->created_at->format('d M Y') }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic text-center py-6">Belum ada tanggapan masuk.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- ChartJS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Salary Distribution Chart
            const ctxSalary = document.getElementById('salaryChart').getContext('2d');
            const salaryLabels = ['< Rp 3.000.000', 'Rp 3.000.000 - Rp 5.000.000', 'Rp 5.000.000 - Rp 10.000.000', '> Rp 10.000.000'];
            
            const salaryCounts = [
                {{ $summary['salary_distribution']['< 3M']['count'] ?? 0 }},
                {{ $summary['salary_distribution']['3M - 5M']['count'] ?? 0 }},
                {{ $summary['salary_distribution']['5M - 10M']['count'] ?? 0 }},
                {{ $summary['salary_distribution']['> 10M']['count'] ?? 0 }}
            ];

            new Chart(ctxSalary, {
                type: 'bar',
                data: {
                    labels: salaryLabels,
                    datasets: [{
                        label: 'Persentase (%)',
                        data: [
                            {{ $summary['salary_distribution']['< 3M']['percentage'] ?? 0 }},
                            {{ $summary['salary_distribution']['3M - 5M']['percentage'] ?? 0 }},
                            {{ $summary['salary_distribution']['5M - 10M']['percentage'] ?? 0 }},
                            {{ $summary['salary_distribution']['> 10M']['percentage'] ?? 0 }}
                        ],
                        backgroundColor: '#3b82f6',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { display: false }
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

            // 2. Trend Chart
            const ctxTrend = document.getElementById('trendChart').getContext('2d');
            const trendData = @json($summary['graduation_trends']);
            const trendLabels = Object.keys(trendData);
            const trendValues = Object.values(trendData);

            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: trendLabels.length ? trendLabels : ['Belum Ada'],
                    datasets: [{
                        label: 'Jumlah Responden',
                        data: trendValues.length ? trendValues : [0],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.3
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
                    }
                }
            });
        });
    </script>
</x-app-layout>
