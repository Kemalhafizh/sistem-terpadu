<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </span>
                    Komparasi Hasil Baseline vs Test Terbaru
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Perbandingan delta throughput Req/Sec dan perubahan latensi dari baseline sebelumnya.</p>
            </div>
            <a href="{{ route('load_test.dashboard') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Dashboard Load Test
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Analisis Tren Performa Throughput</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Status Tren: <span class="uppercase font-mono font-bold text-emerald-600">{{ $comparison['performance_trend'] }}</span></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs font-mono">
                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl space-y-2">
                        <span class="text-gray-400 uppercase block font-bold">Hasil Pengujian Saat Ini</span>
                        <p class="text-xl font-bold text-indigo-600">{{ $comparison['current']->requests_per_second }} Req/s</p>
                        <p class="text-gray-500">Skenario: {{ $comparison['current']->scenario_name }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl space-y-2">
                        <span class="text-gray-400 uppercase block font-bold">Selisih Delta Throughput</span>
                        <p class="text-xl font-bold {{ $comparison['rps_difference'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $comparison['rps_difference'] > 0 ? '+' : '' }}{{ $comparison['rps_difference'] }} Req/s
                        </p>
                        <p class="text-gray-500">Dibandingkan dengan Baseline Pengujian Sebelumnya</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
