<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('analytics.index') }}" class="text-sm font-bold text-gray-500 hover:text-primary-600 flex items-center mb-1 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali ke Dashboard Utama
                </a>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    {{ __('Analitik Akademik & Nilai') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Drill-Down Distribusi Nilai & Peringkat Kelas</p>
            </div>
            <form action="{{ route('analytics.academic') }}" method="GET" class="w-64">
                <select name="classroom_id" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-bold text-gray-700 dark:text-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">-- Seluruh Kelas --</option>
                    @foreach($classrooms as $class)
                        <option value="{{ $class->id }}" {{ $classroomId == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Distribution Column Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                    <h4 class="text-lg font-black text-gray-900 dark:text-white mb-2">Distribusi Nilai Siswa</h4>
                    <p class="text-xs text-gray-400 font-bold mb-6">Jumlah siswa pada rentang nilai (0-100)</p>
                    <div id="distributionChart" class="w-full h-80"></div>
                </div>

                <!-- Class Averages Bar Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                    <h4 class="text-lg font-black text-gray-900 dark:text-white mb-2">Rata-Rata Nilai per Kelas</h4>
                    <p class="text-xs text-gray-400 font-bold mb-6">Komparasi nilai rata-rata antar rombongan belajar</p>
                    <div id="classAverageChart" class="w-full h-80"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#cbd5e1' : '#475569';
            const gridColor = isDark ? '#334155' : '#e2e8f0';

            const gradesData = @json($grades);
            
            // 1. Distribution Chart
            const distLabels = Object.keys(gradesData.distribution);
            const distValues = Object.values(gradesData.distribution);
            const distOptions = {
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                series: [{ name: 'Jumlah Siswa', data: distValues }],
                xaxis: { categories: distLabels, labels: { style: { colors: textColor, fontWeight: 'bold' } } },
                yaxis: { labels: { style: { colors: textColor } } },
                colors: ['#10b981'],
                grid: { borderColor: gridColor },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            };
            new ApexCharts(document.querySelector("#distributionChart"), distOptions).render();

            // 2. Class Average Chart
            @if(empty($classroomId))
                const classAvgs = gradesData.class_averages || [];
                const classNames = classAvgs.map(c => c.name);
                const classScores = classAvgs.map(c => c.average);
                const classAvgOptions = {
                    chart: { type: 'bar', height: 320, toolbar: { show: false } },
                    series: [{ name: 'Rata-rata Kelas', data: classScores }],
                    xaxis: { categories: classNames, labels: { style: { colors: textColor, fontWeight: 'bold' } } },
                    yaxis: { labels: { style: { colors: textColor } }, max: 100 },
                    colors: ['#3b82f6'],
                    grid: { borderColor: gridColor },
                    tooltip: { theme: isDark ? 'dark' : 'light' }
                };
                new ApexCharts(document.querySelector("#classAverageChart"), classAvgOptions).render();
            @endif
        });
    </script>
</x-app-layout>
