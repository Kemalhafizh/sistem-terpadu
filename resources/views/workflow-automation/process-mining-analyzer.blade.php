<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Penganalisis Efisiensi Proses (Process Mining & Bottleneck Analyzer)') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Deteksi titik hambatan alur kerja, durasi per tahapan, dan tingkat penyelesaian tugas.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- Overview Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-2">
                <span class="text-xs font-bold text-slate-500 uppercase">Tingkat Penyelesaian Alur Kerja</span>
                <div class="text-3xl font-black text-slate-900 dark:text-white">{{ $analysis['completion_rate'] ?? 100 }}%</div>
                <p class="text-xs text-emerald-600 font-semibold">Total {{ $analysis['total_processes'] ?? 0 }} Proses Terpantau</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-2">
                <span class="text-xs font-bold text-slate-500 uppercase">Status Hambatan (Bottlenecks)</span>
                <div class="text-3xl font-black text-amber-600">1 Titik Terdeteksi</div>
                <p class="text-xs text-slate-500">Tahap Persetujuan Akhir memerlukan eskalasi</p>
            </div>
        </div>

        <!-- Bottleneck Node Breakdown -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="font-bold text-lg text-slate-800 dark:text-white">Durasi Rata-rata per Node Proses</h3>
            <div class="space-y-3 pt-2">
                @foreach($analysis['bottlenecks'] ?? [] as $b)
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border {{ $b['is_bottleneck'] ? 'border-amber-300 dark:border-amber-800' : 'border-slate-100 dark:border-slate-700' }} flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-mono text-slate-400">[{{ $b['node_key'] }}]</span>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white">{{ $b['node_name'] }}</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Durasi Rata-rata: {{ $b['avg_duration_hours'] }} Jam</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $b['is_bottleneck'] ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' }}">
                            {{ $b['is_bottleneck'] ? 'Hambatan (Bottleneck)' : 'Lancar' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
