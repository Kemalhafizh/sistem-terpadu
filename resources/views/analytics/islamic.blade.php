<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('analytics.index') }}" class="text-sm font-bold text-gray-500 hover:text-primary-600 flex items-center mb-1 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali ke Dashboard Utama
                </a>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-teal-500 text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m22 4v-4m-3.1-2a9 9 0 11-14.8 0"></path></svg>
                    </div>
                    {{ __('Analitik Keislaman & BPI') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Bimbingan Pribadi Islami & Rekapitulasi Pelanggaran</p>
            </div>
        </div>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Penalty vs Rewards Bar Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                    <h4 class="text-lg font-black text-gray-900 dark:text-white mb-2">Poin BPI per Kelas</h4>
                    <p class="text-xs text-gray-400 font-bold mb-6">Penghargaan Prestasi vs Penalti Pelanggaran</p>
                    <div id="bkBarChart" class="w-full h-80"></div>
                </div>

                <!-- Global Breakdown -->
                <div class="bg-teal-50 dark:bg-teal-900/20 rounded-[2.5rem] p-8 shadow-sm border border-teal-100 dark:border-teal-800/50">
                    <h4 class="text-lg font-black text-teal-900 dark:text-teal-400 mb-6 text-center">Rekapitulasi Global</h4>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 text-center flex flex-col items-center justify-center">
                            <div class="w-12 h-12 bg-red-100 text-red-500 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <h3 class="text-3xl font-black text-red-600 dark:text-red-400">{{ $bk['global']['penalty_points'] }}</h3>
                            <p class="text-xs text-gray-500 font-bold mt-1 uppercase">Total Penalti</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 text-center flex flex-col items-center justify-center">
                            <div class="w-12 h-12 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                            </div>
                            <h3 class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $bk['global']['reward_points'] }}</h3>
                            <p class="text-xs text-gray-500 font-bold mt-1 uppercase">Total Penghargaan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#cbd5e1' : '#475569';
            const gridColor = isDark ? '#334155' : '#e2e8f0';

            const bkClasses = @json($bk['by_classroom']);
            const classNames = bkClasses.map(b => b.classroom);
            const bkPenalties = bkClasses.map(b => b.penalty_points);
            const bkRewards = bkClasses.map(b => b.reward_points);

            const options = {
                chart: { type: 'bar', height: 320, stacked: false, toolbar: { show: false } },
                series: [
                    { name: 'Poin Penalti', data: bkPenalties },
                    { name: 'Poin Penghargaan', data: bkRewards }
                ],
                xaxis: { categories: classNames, labels: { style: { colors: textColor } } },
                yaxis: { labels: { style: { colors: textColor } } },
                colors: ['#ef4444', '#10b981'],
                grid: { borderColor: gridColor },
                legend: { labels: { colors: textColor } },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            };
            new ApexCharts(document.querySelector("#bkBarChart"), options).render();
        });
    </script>
</x-app-layout>
