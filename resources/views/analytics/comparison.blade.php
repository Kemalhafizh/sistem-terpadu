<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('analytics.index') }}" class="text-sm font-bold text-gray-500 hover:text-primary-600 flex items-center mb-1 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali ke Dashboard Utama
                </a>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-indigo-500 text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    {{ __('Analitik Komparasi (Year-over-Year)') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Perbandingan Pendaftar SPMB & Pertumbuhan Historis</p>
            </div>
        </div>
    </x-slot>

    <x-vendor.script lib="apexcharts" />

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-2">Pertumbuhan SPMB Tahunan</h4>
                <p class="text-xs text-gray-400 font-bold mb-6">Tren Pendaftaran Calon Siswa Baru Berdasarkan Tahun Akademik</p>
                <div id="spmbComparisonChart" class="w-full h-[400px]"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#cbd5e1' : '#475569';
            const gridColor = isDark ? '#334155' : '#e2e8f0';

            const spmbData = @json($spmb['growth']);
            const years = spmbData.map(s => s.year || 'N/A');
            const totals = spmbData.map(s => s.total);

            const options = {
                chart: { type: 'area', height: 400, toolbar: { show: true } },
                series: [{ name: 'Total Pendaftar', data: totals }],
                xaxis: { categories: years, labels: { style: { colors: textColor } } },
                yaxis: { labels: { style: { colors: textColor } } },
                colors: ['#6366f1'],
                stroke: { curve: 'smooth', width: 3 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.5, opacityTo: 0.1 } },
                grid: { borderColor: gridColor },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            };
            new ApexCharts(document.querySelector("#spmbComparisonChart"), options).render();
        });
    </script>
</x-app-layout>
