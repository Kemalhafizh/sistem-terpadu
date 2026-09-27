<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('admin.chatbot.dashboard') }}" class="text-sm font-bold text-gray-500 hover:text-indigo-600 flex items-center mb-1 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali ke Dasbor Chatbot
                </a>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight dark:text-white flex items-center gap-2">
                    {{ __('Analitik & Performa AI Chatbot') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Metrik performa chatbot, tingkat keakuratan, dan sebaran topik</p>
            </div>
        </div>
    </x-slot>

    <x-vendor.script lib="apexcharts" />

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Intent Sebaran Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                    <h4 class="text-lg font-black text-gray-900 dark:text-white mb-2">Sebaran Topik (Intent)</h4>
                    <p class="text-xs text-gray-400 font-bold mb-6">Distribusi klasifikasi maksud pesan masuk</p>
                    <div id="intentDistributionChart" class="w-full h-80"></div>
                </div>

                <!-- Daily Chats Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                    <h4 class="text-lg font-black text-gray-900 dark:text-white mb-2">Tren Trafik Harian</h4>
                    <p class="text-xs text-gray-400 font-bold mb-6">Jumlah percakapan chatbot 7 hari terakhir</p>
                    <div id="dailyTraficChart" class="w-full h-80"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#cbd5e1' : '#475569';
            const gridColor = isDark ? '#334155' : '#e2e8f0';

            const analyticsData = @json($analytics);
            
            // 1. Intent Distribution Chart (Donut/Pie)
            const intentLabels = Object.keys(analyticsData.intent_stats);
            const intentValues = Object.values(analyticsData.intent_stats);
            const intentOptions = {
                chart: { type: 'donut', height: 320 },
                series: intentValues,
                labels: intentLabels,
                colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                legend: { position: 'bottom', labels: { colors: textColor } },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            };
            new ApexCharts(document.querySelector("#intentDistributionChart"), intentOptions).render();

            // 2. Daily Traffic Chart (Area/Line)
            const dailyLabels = Object.keys(analyticsData.daily_stats);
            const dailyValues = Object.values(analyticsData.daily_stats);
            const dailyOptions = {
                chart: { type: 'area', height: 320, toolbar: { show: false } },
                series: [{ name: 'Total Sesi Chat', data: dailyValues }],
                xaxis: { categories: dailyLabels, labels: { style: { colors: textColor } } },
                yaxis: { labels: { style: { colors: textColor } } },
                colors: ['#6366f1'],
                stroke: { curve: 'smooth', width: 3 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
                grid: { borderColor: gridColor },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            };
            new ApexCharts(document.querySelector("#dailyTraficChart"), dailyOptions).render();
        });
    </script>
</x-app-layout>
