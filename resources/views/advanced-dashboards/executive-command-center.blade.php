<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Pusat Komando Eksekutif (Executive Command Center)') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Pemantauan kinerja institusi, metrik langsung, dan KPI operasional {{ school_name() }}.
                </p>
            </div>
            <button onclick="window.location.reload()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm font-semibold shadow-sm transition-all flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Segarkan Data</span>
            </button>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($kpis as $kpi)
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $kpi['category'] }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                            Target: {{ $kpi['target_value'] }}{{ $kpi['unit'] }}
                        </span>
                    </div>
                    <div>
                        <div class="text-3xl font-black text-slate-900 dark:text-white">
                            {{ $kpi['current_value'] }} <span class="text-base font-medium text-slate-500">{{ $kpi['unit'] }}</span>
                        </div>
                        <h4 class="font-semibold text-xs text-slate-700 dark:text-slate-300 mt-1">{{ $kpi['metric_name'] }}</h4>
                    </div>
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                        <span>Periode Sebelumnya: {{ $kpi['previous_value'] }}{{ $kpi['unit'] }}</span>
                        <span class="text-emerald-600 font-bold">Naik +{{ round($kpi['current_value'] - $kpi['previous_value'], 1) }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- School Pulse Summary -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    Denyut Kinerja Operasional Sekolah
                </h3>
                <div class="h-64 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-700/60 p-4 flex flex-col justify-between">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Tren 30 Hari Terakhir</span>
                        <span class="text-emerald-600 font-bold">Kondisi Institusi: Sangat Baik (A+)</span>
                    </div>
                    <div class="flex items-end justify-between h-40 space-x-2 pt-4">
                        @for($i = 0; $i < 12; $i++)
                            @php $h = rand(50, 95); @endphp
                            <div class="flex-1 bg-gradient-to-t from-emerald-600 to-teal-400 rounded-t-xl" style="height: {{ $h }}%;"></div>
                        @endfor
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-400 pt-2 border-t border-slate-200 dark:border-slate-700">
                        <span>Minggu 1</span>
                        <span>Minggu 2</span>
                        <span>Minggu 3</span>
                        <span>Minggu 4</span>
                    </div>
                </div>
            </div>

            <!-- Quick Pulse Indicators -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">Status Layanan Digital</h3>
                <div class="space-y-3">
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <h5 class="text-xs font-bold text-slate-800 dark:text-white">CBT & Ujian Online</h5>
                            <span class="text-[10px] text-slate-500">Server Status: Normal</span>
                        </div>
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <h5 class="text-xs font-bold text-slate-800 dark:text-white">Payment Gateway SPP</h5>
                            <span class="text-[10px] text-slate-500">Midtrans Webhook: Aktif</span>
                        </div>
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <h5 class="text-xs font-bold text-slate-800 dark:text-white">Presensi GPS & RFID</h5>
                            <span class="text-[10px] text-slate-500">Terminal Gerbang: Online</span>
                        </div>
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
